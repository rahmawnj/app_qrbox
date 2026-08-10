<?php

namespace App\Http\Controllers\Member;

use Carbon\Carbon;
use App\Models\Device;
use App\Models\Member;
use App\Models\Payment;
use App\Models\ServiceType;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Events\NotificationEvent;
use App\Models\DeviceTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\SelfServiceTransaction;

class MemberTransactionController extends Controller
{

    public function member_scan()
    {
        $service_types = ServiceType::all();
        return view('landing.member.scan_camera', compact('service_types'));
    }

   public function processQrScan(Request $request)
    {
        $validated = $request->validate([
            'device_code' => 'required|string',
            'service_name' => 'required|string',
        ]);

        $deviceCode = $validated['device_code'];
        $serviceName = $validated['service_name'];
        $user = Auth::user();

        $member = $user->member;
        if (!$member) {
            return redirect()->back()->with('error', 'Akun pengguna tidak terhubung dengan data member.');
        }

        DB::beginTransaction();
        try {
            $device = Device::where('code', $deviceCode)->first();
            if (!$device || !$device->outlet) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Perangkat tidak ditemukan atau tidak terhubung ke outlet.');
            }
            $outlet = $device->outlet;

            $serviceType = ServiceType::whereRaw('LOWER(name) = ?', [strtolower($serviceName)])->first();
            if (!$serviceType) {
                DB::rollBack();
                Log::warning("ServiceType not found for name: " . $serviceName);
                return redirect()->back()->with('error', 'Tipe layanan tidak valid atau tidak ditemukan.');
            }

            $deviceServiceType = DB::table('device_service_type')
                ->where('device_id', $device->id)
                ->where('service_type_id', $serviceType->id)
                ->first();

            if (!$deviceServiceType) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Harga untuk layanan ini pada perangkat tidak ditemukan.');
            }
            $amount = $deviceServiceType->price;

            $subscription = $member->owners()->wherePivot('owner_id', $outlet->owner_id)->wherePivot('is_verified', true)->first();
            if (!$subscription) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Anda belum berlangganan atau akun Anda belum diverifikasi untuk layanan ini.');
            }
            $memberBalance = $subscription->pivot->amount;

            // 5. Buat Transaksi Baru
            $currentTime = Carbon::now();
            $orderId = 'TRX-' . $outlet->code . '-' . $currentTime->timestamp . '-' . uniqid();

            // Tentukan status transaksi berdasarkan saldo
            $transactionStatus = 'success';
            $successMessage = 'Transaksi berhasil! Saldo Anda telah terpotong dan Anda dapat mulai menggunakan perangkat.';
            $errorMessage = 'Saldo Anda tidak mencukupi untuk transaksi ini. Transaksi dibatalkan.';

            if ($memberBalance < $amount) {
                $transactionStatus = 'failed';
                Log::warning("Saldo tidak mencukupi untuk transaksi self-service: {$orderId}. Saldo member: {$memberBalance}, dibutuhkan: {$amount}.");
            }

            $transaction = Transaction::create([
                'order_id'       => $orderId,
                'owner_id'       => $outlet->owner_id,
                'outlet_id'      => $outlet->id,
                'member_id'      => $member->id,
                'amount'         => $amount,
                'channel_type'   => 'self_service',
                'timezone'       => $outlet->timezone,
                'status'         => $transactionStatus,
                'date'           => $currentTime->toDateString(),
                'time'           => $currentTime->toTimeString(),
            ]);

            // Selalu buat entri SelfServiceTransaction, terlepas dari status
            $normalizedServiceType = strtolower(str_replace(' ', '_', $serviceName));
            SelfServiceTransaction::create([
                'transaction_id'    => $transaction->id,
                'device_code'       => $deviceCode,
                'service_type'      => $normalizedServiceType,
                'last_attempt_at'   => $currentTime->toDateTimeString(),
            ]);

            // --- LOGIKA NOTIFIKASI DAN PEMROSESAN UTAMA (HANYA JIKA SUKSES) ---
            if ($transactionStatus === 'success') {
                // Notifikasi berhasil untuk member
                event(new NotificationEvent(
                    recipients: $user,
                    title: '🎉 Pembayaran Berhasil',
                    message: 'Pembayaran sebesar Rp. ' . number_format($amount, 0, ',', '.') . ' untuk layanan ' . $serviceType->name . ' di outlet ' . $outlet->name . ' telah berhasil dilakukan.',
                    url: route('home.member.transactions')
                ));

                // Buat entri pembayaran
                Payment::create([
                    'transaction_id'    => $transaction->id,
                    'owner_id'          => $outlet->owner_id,
                    'outlet_id'         => $outlet->id,
                    'payment_method'    => 'member',
                    'amount'            => $amount,
                    'payment_time'      => now(),
                    'notes'             => 'Pembayaran otomatis dari saldo member untuk layanan self-service.',
                ]);

                // Potong saldo member
                $subscription->pivot->amount -= $amount;
                $subscription->pivot->save();

                // Buat entri DeviceTransaction dengan bypass_activation
                DeviceTransaction::create([
                    'transaction_id'    => $transaction->id,
                    'device_code'       => $deviceCode,
                    'service_type'      => $normalizedServiceType,
                    'bypass_activation' => now(),
                    'status'            => true,
                ]);

                // Notifikasi untuk owner dan kasir
                $ownerUser = $outlet->owner->user;
                $cashierUsers = $outlet->cashiers->map(fn($cashier) => $cashier->user);
                $ownerAndCashiers = $cashierUsers->push($ownerUser);

                event(new NotificationEvent(
                    recipients: $ownerAndCashiers,
                    title: '💸 Transaksi Self-Service Baru di Outlet ' . $outlet->name,
                    message: 'Transaksi self-service sebesar Rp. ' . number_format($amount, 0, ',', '.') . ' telah berhasil dicatat di outlet Anda oleh member.',
                    url: route('partner.transactions.index')
                ));
            } else {
                // Notifikasi gagal untuk member jika saldo tidak mencukupi
                event(new NotificationEvent(
                    recipients: $user,
                    title: '⚠️ Pembayaran Gagal',
                    message: 'Transaksi sebesar Rp. ' . number_format($amount, 0, ',', '.') . ' untuk layanan ' . $serviceType->name . ' di outlet ' . $outlet->name . ' gagal karena saldo Anda tidak mencukupi.',
                    url: route('home.member.transactions')
                ));
            }

            DB::commit();

            if ($transactionStatus === 'success') {
                Log::info("Transaksi self-service berhasil untuk order_id: {$transaction->order_id} oleh member: {$member->id}.");
                return redirect()->route('home.member.orders')->with('success', $successMessage);
            } else {
                Log::warning("Transaksi self-service gagal karena saldo tidak mencukupi untuk order_id: {$transaction->order_id}.");
                return redirect()->back()->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal memproses transaksi self-service: " . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses transaksi. Silakan coba lagi.');
        }
    }

}
