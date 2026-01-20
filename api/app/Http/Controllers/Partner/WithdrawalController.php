<?php

namespace App\Http\Controllers\Partner;

use App\Models\User;
use App\Models\Withdrawal;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Events\NotificationEvent;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class WithdrawalController extends Controller
{
   public function withdrawal_request()
{
    $feature = getData();
    if (!$feature->can('withdrawal.request')) {
        abort(403, 'Anda tidak memiliki izin.');
    }

    $owner = getBrand();

    // Logic Biaya Dinamis
    $withdrawalFee = 10000; // Biaya Layanan Sistem
    // Jika BCA biaya bank 0, jika bukan BCA biaya bank 6500
    $bankFee = (strtoupper($owner->bank_name) == 'BCA') ? 0 : 6500;
    $totalFeePerTransaction = $withdrawalFee + $bankFee;

    $availableBalance = $owner->balance;

    $totalWithdrawnApproved = Withdrawal::where('owner_id', $owner->id)
        // ->where('status', 'approved')
        ->sum('amount');

    $totalWithdrawnPending = Withdrawal::where('owner_id', $owner->id)
        // ->where('status', 'pending')
        ->sum('amount');

    $hasPendingWithdrawal = Withdrawal::where('owner_id', $owner->id)
        // ->where('status', 'pending')
        ->exists();

    $minWithdrawalAmount = config('app.min_withdrawal_amount', 50000); // Sesuaikan minimal penarikan
    $processingTimeInfo = 'Biasanya diproses dalam 1-2 hari kerja.';

    return view('partner.withdrawal.form', compact(
        'availableBalance',
        'hasPendingWithdrawal',
        'minWithdrawalAmount',
        'processingTimeInfo',
        'totalWithdrawnPending',
        'totalWithdrawnApproved',
        'withdrawalFee',
        'bankFee',
        'totalFeePerTransaction'
    ));
}

 public function withdrawal_store(Request $request)
{
    $owner = getBrand();

    if (!$owner || !$owner->id) {
        return back()->withErrors(['error' => 'Data pemilik tidak ditemukan.']);
    }

    // 1. Logic Biaya Dinamis
    $serviceFee = 10000;
    $bankFee = (strtoupper($owner->bank_name) == 'BCA') ? 0 : 6500;
    $totalWithdrawalFee = $serviceFee + $bankFee;

    $minWithdrawalAmount = config('app.min_withdrawal_amount', 50000);

    // Ambil saldo saat ini dari owner
    $availableBalance = $owner->balance;

    // 2. Validasi Data
    $request->validate([
        'amount' => [
            'required',
            'integer',
            'min:' . $minWithdrawalAmount,
        ],
        'notes' => 'nullable|string|max:255',
    ]);

    $requestedAmount = (int) $request->amount;
    $totalAmountToDeduct = $requestedAmount + $totalWithdrawalFee;

    if ($totalAmountToDeduct > $availableBalance) {
        return back()->withErrors(['amount' => 'Saldo tidak cukup. Total potong saldo (termasuk biaya) adalah Rp ' . number_format($totalAmountToDeduct, 0, ',', '.')]);
    }

    $hasPending = Transaction::where('owner_id', $owner->id)
        ->where('type', 'withdrawal')
        ->where('status', 'pending')
        ->exists();

    if ($hasPending) {
        return back()->withErrors(['error' => 'Anda masih memiliki permintaan penarikan yang sedang diproses.']);
    }

    // 4. Eksekusi Simpan ke Tabel Transactions
    DB::beginTransaction();
    try {
        // Menggunakan tabel transactions sesuai schema kamu
        $transaction = Transaction::create([
            'owner_id'               => $owner->id,
            'order_id'               => 'WD-' . time() . '-' . $owner->id,
            'amount'                 => $totalAmountToDeduct,
            'type'                   => 'withdrawal',
            'gross_amount'           => $requestedAmount,
            'service_fee_amount'     => $totalWithdrawalFee,
            'service_fee_percentage' => 0.000,
            'timezone'               => 'Asia/Jakarta',
            'date'                   => now()->format('Y-m-d'),
            'time'                   => now()->format('H:i:s'),
            'status'                 => 'pending',
        ]);

        // Opsional: Jika kamu ingin langsung memotong saldo di kolom 'balance' tabel owners
        // $owner->decrement('balance', $totalAmountToDeduct);

        // 5. Notifikasi Admin
        $admins = User::where('role', 'admin')->get();
        if ($admins->isNotEmpty()) {
            event(new NotificationEvent(
                recipients: $admins,
                title: '⚠️ Penarikan Baru',
                message: "Brand {$owner->brand_name} menarik dana bersih Rp " . number_format($requestedAmount, 0, ',', '.'),
                url: '', // Arahkan ke list transaksi
            ));
        }

        DB::commit();
        return redirect()->route('partner.withdrawal.histories')
            ->with('success', 'Permintaan penarikan berhasil diajukan dan sedang menunggu verifikasi.');

    } catch (\Throwable $e) {
        DB::rollBack();
        report($e);
        return back()->withErrors(['error' => 'Terjadi kesalahan sistem saat memproses transaksi.']);
    }
}

public function histories(Request $request)
{
    // Ambil data owner yang sedang login
    $owner = getBrand();
    $feature = getData();

    if (!$feature->can('withdrawal.histories')) {
        abort(403, 'Anda tidak memiliki izin.');
    }

    // 1. Saldo Tersedia (Langsung dari kolom balance owner karena sudah dipotong saat approve)
    $availableBalance = $owner->balance;

    // 2. Query Riwayat Penarikan (Hanya milik owner ini)
    $query = Withdrawal::where('owner_id', $owner->id);

    // Filter berdasarkan rentang waktu
    if ($request->filled('daterange')) {
        $dates = explode(' - ', $request->daterange);
        if (count($dates) === 2) {
            $query->whereBetween('created_at', [
                trim($dates[0]) . ' 00:00:00',
                trim($dates[1]) . ' 23:59:59'
            ]);
        }
    }

    // Hitung total ringkasan untuk Owner ini
    $totalWithdrawalsCount = (clone $query)->count();
    $totalWithdrawalsAmount = (clone $query)->sum('requested_amount'); // Nominal yang diajukan
    $totalNetTransferred = (clone $query)->sum('amount_after_fee');   // Nominal yang diterima bersih

    // Pagination
    $withdrawalHistories = $query->latest()->paginate(10);

    return view('partner.withdrawal.histories', compact(
        'withdrawalHistories',
        'availableBalance',
        'totalWithdrawalsCount',
        'totalWithdrawalsAmount',
        'totalNetTransferred'
    ));
}
}
