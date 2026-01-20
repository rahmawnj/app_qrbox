<?php

namespace App\Http\Controllers\Partner;

use Carbon\Carbon;
use App\Models\Member;
use App\Models\Outlet;
use App\Models\TopupHistory;
use Illuminate\Http\Request;
use App\Events\NotificationEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TopupController extends Controller
{
    public function showTopupForm()
    {
        $members = getBrand()->members()->wherePivot('is_verified', 1)->get();
$outlets = getData()
    ->outlets
    ->where('status', 1)
    ->get();

        return view('partner.topup', compact('members', 'outlets'));
    }


    public function processTopup(Request $request)
    {
        // Validasi input
        $request->validate([
            'member_id'    => 'required|exists:members,id',
            'out'          => 'required|string|exists:outlets,code',
            'nominal'      => 'required|numeric|min:1', // Ini adalah nominal yang akan masuk ke saldo (tanpa tax)
            'notes'        => 'nullable|string|max:500',
            'payment_method' => 'nullable|in:cashier,qris,bank_transfer,e_wallet,other',
            // 'tax_amount_input' => 'nullable|numeric|min:0', // Jika pajak bisa diinput manual dari form
        ]);

        $member = Member::find($request->member_id);
        if (!$member) {
            return redirect()->back()->withErrors('Member tidak ditemukan.');
        }

        $outlet = Outlet::where('code', $request->out)->first();
        if (!$outlet) {
            return redirect()->back()->withErrors('Outlet tidak ditemukan.');
        }

        $subscription = DB::table('subscription')
            ->where('member_id', $member->id)
            ->where('owner_id', $outlet->owner->id)
            ->first();

        if (!$subscription) {
            return redirect()->back()->withErrors('Langganan member ke brand ini tidak ditemukan.');
        }

        $initialBalance = $subscription->amount;
        $amountToAddToBalance = $request->nominal;

        $taxAmount = 0;

        $totalPaymentMade = $amountToAddToBalance + $taxAmount;

        $finalBalance = $initialBalance + $amountToAddToBalance;

        $cashierName = Auth::user()->name;

        $paymentMethod = $request->input('payment_method', 'cashier');

        $status = 'success';

        DB::beginTransaction();
        try {
            // Update saldo pada tabel subscription (increment 'amount')
            DB::table('subscription')
                ->where('member_id', $member->id)
                ->where('owner_id', $outlet->owner->id)
                ->increment('amount', $amountToAddToBalance); // Menggunakan $amountToAddToBalance

            // Buat topup history record
            TopupHistory::create([
                'member_id'        => $member->id,
                'outlet_id'        => $outlet->id,
                'owner_id'         => $outlet->owner->id,
                'initial_balance'  => $initialBalance,
                'amount'           => $amountToAddToBalance, // Ini 'amount' yang ditambahkan ke saldo
                'tax_amount'       => $taxAmount,
                // 'total_amount_after_tax' => $totalPaymentMade, // Kolom ini dihilangkan dari DB, jadi tidak perlu di sini
                'final_balance'    => $finalBalance,
                'payment_method'   => $paymentMethod,
                'status'           => $status,
                'time'             => now(), // Menggunakan now() untuk waktu saat ini
                'timezone'         => $outlet->timezone,
                'cashier_name'     => $cashierName,
                'notes'            => $request->notes,
            ]);

             event(new NotificationEvent(
                recipients: $member->user,
                title: '💰 Topup Berhasil',
                message: 'Topup sebesar Rp. ' . number_format($amountToAddToBalance, 0, ',', '.') . ' telah berhasil ditambahkan ke saldo Anda.',
                url: route('home.member.topup.histories')
            ));

            // Notifikasi untuk Owner
            event(new NotificationEvent(
                recipients: $outlet->owner->user,
                title: '💸 Topup Member',
                message: 'Member ' . $member->user->name . ' telah melakukan topup sebesar Rp. ' . number_format($amountToAddToBalance, 0, ',', '.') . ' di outlet ' . $outlet->name . '.',
                url: route('partner.topup.histories')
            ));

            DB::commit();

            return redirect()->back()->withInput(['out' => $request->get('out')])
                ->with('success', $member->user->name . ' telah berhasil Topup sebesar Rp. ' . number_format($amountToAddToBalance, 0, ',', '.') . ' (Total Pembayaran: Rp. ' . number_format($totalPaymentMade, 0, ',', '.') . ')');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Topup failed: ' . $e->getMessage(), [
                'member_id' => $member->id,
                'outlet_id' => $outlet->id,
                'nominal'   => $request->nominal,
                'error'     => $e->getTraceAsString(),
            ]);
            return redirect()->back()->withErrors('Topup gagal: ' . $e->getMessage() . '. Silakan coba lagi.');
        }
    }


    public function topupHistories(Request $request)
    {
        // Start a new query for TopupHistory
        $query = TopupHistory::with(['member.user', 'outlet']); // Eager load outlet for display

        $ownerOutletIds = getData()->outlets->pluck('id')->toArray();
        $query->whereIn('outlet_id', $ownerOutletIds);

        // 1. Date Range Filter
        if ($request->filled('daterange')) {
            $dateRange = explode(' - ', $request->daterange);
            if (count($dateRange) === 2) {
                try {
                    $startDate = Carbon::createFromFormat('Y-m-d', $dateRange[0], 'Asia/Jakarta')->startOfDay();
                    $endDate = Carbon::createFromFormat('Y-m-d', $dateRange[1], 'Asia/Jakarta')->endOfDay();
                    $query->whereBetween('time', [$startDate, $endDate]);
                } catch (\Exception $e) {
                    Log::error('Invalid daterange format: ' . $request->daterange . ' - ' . $e->getMessage());
                }
            }
        }

        // 2. Status Filter
        if ($request->filled('status') && in_array($request->status, ['pending', 'success', 'failed'])) {
            $query->where('status', $request->status);
        }

       if ($request->filled('channel')) {
            if ($request->channel === 'cashier') {
                $query->where('payment_method', 'cashier');
            } elseif ($request->channel === 'qris') {
                $query->where('payment_method', 'qris');
            }
        }

       // 4. Search Filter (Member Name, Phone Number, Notes, Outlet Name)
       if ($request->filled('search')) {
        $searchTerm = '%' . $request->search . '%';
        $query->where(function ($q) use ($searchTerm) {
            // Search by Member Name
            $q->whereHas('member.user', function ($subQ) use ($searchTerm) {
                $subQ->where('name', 'like', $searchTerm);
            })
            // Search by Member Phone Number
            ->orWhereHas('member', function ($subQ) use ($searchTerm) {
                $subQ->where('phone_number', 'like', $searchTerm);
            })
            // Search by Notes
            ->orWhere('notes', 'like', $searchTerm)
            // Search by Outlet Name
            ->orWhereHas('outlet', function ($subQ) use ($searchTerm) {
                $subQ->where('outlet_name', 'like', $searchTerm);
            })
            ->orWhereHas('outlet.owner', function ($subQ) use ($searchTerm) {
                $subQ->where('brand_name', 'like', $searchTerm);
            });
        });
    }

        $topupHistories = $query->orderBy('time', 'desc')->paginate(15);

        $summaryQueryBase = TopupHistory::whereIn('outlet_id', $ownerOutletIds);

        // Apply owner's outlet filter (and specific outlet if applicable) to summaries
        if ($request->filled('out')) {
            $summaryQueryBase->where('outlet_id', $request->out);
        }

        // Apply date range filter to summaries (if present in request)
        if ($request->filled('daterange')) {
            $dateRange = explode(' - ', $request->daterange);
            if (count($dateRange) === 2) {
                try {
                    $startDate = Carbon::createFromFormat('Y-m-d', $dateRange[0], 'Asia/Jakarta')->startOfDay();
                    $endDate = Carbon::createFromFormat('Y-m-d', $dateRange[1], 'Asia/Jakarta')->endOfDay();
                    $summaryQueryBase->whereBetween('time', [$startDate, $endDate]);
                } catch (\Exception $e) { /* ignore for summary if invalid date */
                }
            }
        }

        $totalTopupsCount = (clone $summaryQueryBase)->where('status', 'success')->count();
        $totalTopupsAmount = (clone $summaryQueryBase)->where('status', 'success')->sum('amount');
        $cashierTopupsCount = (clone $summaryQueryBase)->where('status', 'success')->whereNotNull('cashier_name')->count();

        return view('partner.topup_histories', compact(
            'topupHistories',
            'totalTopupsCount',
            'totalTopupsAmount',
            'cashierTopupsCount',
        ));
    }
}
