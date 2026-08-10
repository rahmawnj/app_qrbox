<?php

namespace App\Http\Controllers;

use App\Constants\OrderStatus;
use App\Models\Transaction; // Make sure this model exists and is correctly namespaced
use Carbon\Carbon; // Make sure Carbon is imported

class LandingController extends Controller
{
    /**
     * Display the transaction progress.
     *
     * @param string $order_id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function transaction($order_id)
    {
        // 1. Fetch the transaction with necessary relationships
        $transaction = Transaction::where('order_id', $order_id)
            ->with([
                'owner',
                'outlet',
                'dropOffTransaction.service', // Eager load dropOffTransaction dan service-nya
                'selfServiceTransaction',     // Eager load selfServiceTransaction
                'member.user',                // Eager load member dan user terkait
                'payments'                    // Eager load payments
            ])
            ->first();

        if (!$transaction) {
            // Handle case where transaction is not found
            return view('landing.transaction_progress', ['error' => 'Transaksi tidak ditemukan.']);
        }

        // 2. Set custom_status_text based on channel_type
        $progressStatus = null;
        if ( $transaction->dropOffTransaction) {
            $progressStatus = $transaction->dropOffTransaction->progress;
        }
        // Jika tidak ada progress spesifik dari drop_off, atau channel_type lain, default ke 'received'
        $transaction->custom_status_text = OrderStatus::STATUSES[$progressStatus ?? 'received']['label'] ?? 'Status Tidak Diketahui';


        $customerName = 'Pelanggan Umum'; // Default
        $customerPhone = 'N/A'; // Default

        if ( $transaction->dropOffTransaction) {
            $customerName = $transaction->dropOffTransaction->customer_name ?? $customerName;
            $customerPhone = $transaction->dropOffTransaction->customer_phone_number ?? $customerPhone;
        } elseif ($transaction->channel_type == 'self_service' && $transaction->member && $transaction->member->user) {
            $customerName = $transaction->member->user->name ?? $customerName;
            $customerPhone = $transaction->member->phone_number ?? $customerPhone;
        }
        $transaction->customer_display_name = $customerName;
        $transaction->customer_display_phone = $customerPhone;

        // --- 3. Mengambil Estimasi Tanggal Selesai ---
        $estimatedCompletionAt = null;
        if ( $transaction->dropOffTransaction && $transaction->dropOffTransaction->estimated_completion_at) {
            $estimatedCompletionAt = Carbon::parse($transaction->dropOffTransaction->estimated_completion_at);
        }
        // Tambahkan ke objek transaksi
        $transaction->estimated_completion_display_at = $estimatedCompletionAt;


        // --- 4. Menghitung Total Harga Layanan + Addons (untuk tampilan detail) ---
        $totalAddonsPrice = 0;
        $addonsData = [];
        $servicePrice = 0;

        if ( $transaction->dropOffTransaction) {
            $addons = $transaction->dropOffTransaction->addons;
            // Pastikan addons adalah array, jika string maka decode
            if (is_string($addons)) {
                $addons = json_decode($addons, true);
            }
            if (is_array($addons)) {
                foreach ($addons as $addon) {
                    $totalAddonsPrice += (float) ($addon['price'] ?? 0);
                    $addonsData[] = $addon;
                }
            }
            $servicePrice = $transaction->dropOffTransaction->service_price ?? 0;
        }
        // Untuk self_service, total amount sudah ada di transaction->amount
        // Jadi total_amount_before_paid ini lebih relevan untuk drop_off
        $transaction->total_amount_before_paid = $servicePrice + $totalAddonsPrice;
        $transaction->addons_data = $addonsData; // Sertakan addons yang sudah di-parse ke objek transaksi

        return view('landing.transaction_progress', [
            'transaction' => $transaction,
            'progressSteps' => OrderStatus::STATUSES,
        ]);
    }

}
