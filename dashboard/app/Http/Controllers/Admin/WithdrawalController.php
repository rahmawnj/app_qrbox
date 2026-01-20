<?php

namespace App\Http\Controllers\Admin;

use App\Models\Withdrawal;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Events\NotificationEvent;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class WithdrawalController extends Controller
{
  public function withdrawal_request(Transaction $transaction)
{
    if ($transaction->type != 'withdrawal') {
        abort(404, 'Data penarikan tidak ditemukan.');
    }

    // Load relasi owner dan user
    $transaction->load('owner.user');

    $owner = $transaction->owner;
    $currentOwnerBalance = $owner ? $owner->balance : 0;

    return view('admin.withdrawal.confirm', compact('transaction', 'currentOwnerBalance'));
}
public function withdrawal_store(Request $request)
{
    $request->validate([
        'withdrawal_id' => 'required|exists:transactions,id',
        'action'        => 'required|in:approve,reject',
        'rejection_reason' => 'nullable|string|max:255',
    ]);

    DB::beginTransaction();

    try {
        // 1. Lock transaksi agar tidak diproses dua kali
        $transaction = Transaction::lockForUpdate()->findOrFail($request->withdrawal_id);

        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Transaksi ini sudah diproses sebelumnya.');
        }

        $owner = $transaction->owner;

        if ($request->action === 'approve') {
            // --- LOGIKA APPROVE ---

            // a. Cek Saldo (Safety Check)
            if ($owner->balance < $transaction->amount) {
                return back()->with('error', 'Saldo owner tidak mencukupi.');
            }

            // b. Potong Saldo Owner
            $owner->decrement('balance', $transaction->amount);

            // c. Masukkan data ke tabel withdrawals (Record Permanen)
            Withdrawal::create([
                'owner_id'                   => $owner->id,
                'amount'                     => $transaction->amount, // Total potong saldo
                'requested_amount'           => $transaction->gross_amount, // Nominal kotor
                'notes'                      => $transaction->notes, // Catatan dari transaksi
                'approved_at'                => now(),
                'bank_name'                  => $owner->bank_name,
                'bank_account_number'        => $owner->bank_account_number,
                'bank_account_holder_name'   => $owner->bank_account_holder_name,
                'amount_before_fee'          => $owner->balance + $transaction->amount, // Saldo sebelum potong
                'withdrawal_fee'             => $transaction->service_fee_amount,
                'amount_after_fee'           => $transaction->gross_amount, // Nominal yang ditransfer
            ]);

            // d. Update Status Transaksi
            $transaction->status = 'success';
            $messageToOwner = "Penarikan dana Rp " . number_format($transaction->gross_amount, 0, ',', '.') . " telah disetujui.";

        } else {
            // --- LOGIKA REJECT ---
            $transaction->status = 'failed';
            $transaction->notes = $request->rejection_reason ?? 'Penarikan ditolak oleh admin.';
            $messageToOwner = "Penarikan dana ditolak. Alasan: " . ($request->rejection_reason ?? 'Tidak ada.');
        }

        $transaction->save();

        // 2. Notifikasi
        if ($owner->user) {
            event(new NotificationEvent(
                recipients: $owner->user,
                title: $request->action === 'approve' ? '✅ Penarikan Berhasil' : '❌ Penarikan Ditolak',
                message: $messageToOwner,
                url: route('partner.withdrawal.histories'),
            ));
        }

        DB::commit();

        return redirect()->route('admin.withdrawal.list')
            ->with('success', 'Status penarikan berhasil diperbarui.');

    } catch (\Throwable $e) {
        DB::rollBack();
        return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
    }
}
public function histories(Request $request)
{
    // 1. Ambil data owner yang sedang login (Gunakan helper getBrand() kamu)
    $owner = getBrand();
    $daterange = $request->query('daterange', '');

    // 2. Tentukan Query Dasar
    // Jika ada $owner, filter berdasarkan owner tersebut.
    // Jika tidak ada (Admin), ambil semua.
    $query = Withdrawal::with('owner.user');

    if ($owner) {
        $query->where('owner_id', $owner->id);

        // Untuk Owner: Total dana yang belum ditarik diambil dari saldonya sendiri
        $totalUnwithdrawnFunds = $owner->balance;
    } else {
        // Untuk Admin: Total semua dana yang ada di sistem
        $totalUnwithdrawnFunds = \App\Models\Owner::sum('balance');
    }

    // 3. Filter Rentang Waktu
    if ($request->filled('daterange')) {
        $dates = explode(' - ', $request->daterange);
        if (count($dates) === 2) {
            $query->whereBetween('created_at', [
                trim($dates[0]) . ' 00:00:00',
                trim($dates[1]) . ' 23:59:59'
            ]);
        }
    }

    // 4. Filter Pencarian (Hanya aktif untuk Admin)
    if ($request->filled('search') && !$owner) {
        $search = $request->search;
        $query->whereHas('owner', function ($q) use ($search) {
            $q->where('brand_name', 'like', '%' . $search . '%')
              ->orWhereHas('user', function ($userQ) use ($search) {
                  $userQ->where('name', 'like', '%' . $search . '%');
              });
        });
    }

    // 5. Hitung Summary Berdasarkan Filter
    // Clone query agar tidak merusak query utama untuk pagination
    $totalGlobalWithdrawalsCount = (clone $query)->count();
    $totalGlobalWithdrawalsAmount = (clone $query)->sum('amount');

    // 6. Eksekusi Pagination
    $withdrawalHistories = $query->latest()->paginate(10);

    // Kirim data ke view
    return view('admin.withdrawal.histories', compact(
        'withdrawalHistories',
        'totalUnwithdrawnFunds',
        'totalGlobalWithdrawalsCount',
        'totalGlobalWithdrawalsAmount',
        'daterange'
    ))->with([
        'totalAmountInTable' => $totalGlobalWithdrawalsAmount,
        'totalCountInTable'  => $totalGlobalWithdrawalsCount,
        'isOwner'            => $owner ? true : false // Helper untuk view
    ]);
}
    public function listWithdrawals()
    {
        $baseQuery = Transaction::with('owner')
                        ->where('type', 'withdrawal')
                        ->where('status', 'pending');

        $pendingWithdrawalCount = (clone $baseQuery)->count();

        $withdrawals = $baseQuery->latest()->get();
        // dd($withdrawals);

        $totalAmountInTable = $withdrawals->sum('amount');
        $totalCountInTable = $withdrawals->count();

        return view('admin.withdrawal.list', compact(
            'withdrawals',
            'pendingWithdrawalCount',
            'totalAmountInTable',
            'totalCountInTable'
        ));
    }

}
