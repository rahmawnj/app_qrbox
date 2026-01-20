<?php

namespace App\Http\Controllers\Partner;

use Carbon\Carbon;
use App\Models\Addon;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Events\NotificationEvent;
use App\Models\DeviceTransaction;
use App\Models\DropOffTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class CashierPaymentController extends Controller
{
     public function create(Request $request)
    {
        $feature = getData();
        if (!$feature->can('partner.cashier.payment.create')) {
            abort(403, 'Anda tidak memiliki izin.');
        }

        $outlets = getData()
            ->outlets
            ->where('status', 1)
            ->get();


        $outlets->load([
            'services.serviceTypes',
            'addons',
            'devices'
        ]);

        $outletServicesData = [];
        $outletAddonsData = [];

        foreach ($outlets as $outlet) {
            $outletServicesData[$outlet->id] = [];
            foreach ($outlet->services as $service) {
                $serviceOptions = [];
                foreach ($service->serviceTypes as $serviceType) {
                    $serviceOptions[] = [
                        'id' => $serviceType->id,
                        'name' => $serviceType->name,
                    ];
                }

                $outletServicesData[$outlet->id][$service->id] = [
                    'id' => $service->id,
                    'name' => $service->name,
                    'unit' => $service->unit, // Tambahkan unit ke dalam data
                    'member_price' => (float) $service->member_price,
                    'non_member_price' => (float) $service->non_member_price,
                    'service_options' => $serviceOptions,
                ];
            }

            $outletAddonsData[$outlet->id] = [];
            foreach ($outlet->addons as $addon) {
                $outletAddonsData[$outlet->id][$addon->id] = [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'category' => $addon->category,
                    'description' => $addon->description,
                    'price' => (float) $addon->price,
                ];
            }
        }
        // dd($outletsFullData);
        $members = getBrand()->members()->wherePivot('is_verified', 1)->get();
        return view('partner.cashier_payment.payment', compact('outlets', 'outletServicesData', 'outletAddonsData', 'members'));
    }

       public function store(Request $request)
    {
        $feature = getData();
        if (!$feature->can('partner.cashier.payment.create')) {
            abort(403, 'Anda tidak memiliki izin.');
        }

        $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'service_id' => 'required|exists:services,id',
            'amount' => 'required|numeric|min:0',
            'cashier_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'addon_ids' => 'nullable|string',
            'estimated_completion_at' => 'required',
            'customer_type' => 'required|in:member,non_member',
            'device_id' => 'nullable|exists:devices,id',
            'member_id' => 'required_if:customer_type,member|exists:members,id',
            'member_payment_code' => 'required_if:customer_type,member|string|size:6',
            'customer_name' => 'required_if:customer_type,non_member|string|max:255',
            'customer_phone_number' => 'required_if:customer_type,non_member|nullable|string|max:20',
            'payment_type' => 'required_if:customer_type,non_member|in:cash,non_cash',
        ]);

        $dataFetcher = getData();
        $outlet = $dataFetcher->outlets->find($request->outlet_id);

        if (!$outlet) {
            return redirect()->back()->with('error', 'Outlet tidak ditemukan atau Anda tidak memiliki akses ke outlet ini.');
        }

        $service = Service::with('serviceOptions.serviceType')->findOrFail($request->service_id);

        $device = null;
        if (count($service->serviceOptions) > 0) {
            $device = $outlet->devices()->find($request->device_id);
            if (!$device) {
                return redirect()->back()->with('error', 'Perangkat tidak ditemukan atau bukan milik outlet ini.');
            }
        }


        $addonsToSave = [];
        if ($request->filled('addon_ids')) {
            $selectedAddonIds = explode(',', $request->addon_ids);
            $selectedAddonIds = array_map('intval', array_filter($selectedAddonIds, 'is_numeric'));

            if (!empty($selectedAddonIds)) {
                $addons = Addon::whereIn('id', $selectedAddonIds)->get();
                foreach ($addons as $addon) {
                    $addonsToSave[] = [
                        'id' => $addon->id,
                        'name' => $addon->name,
                        'price' => $addon->price,
                        'category' => $addon->category,
                    ];
                }
            }
        }

        $customerType = $request->input('customer_type');
        $channelType = 'drop_off';
        $memberId = null;
        $customerName = null;
        $customerPhoneNumber = null;
        $paymentMethodForPaymentTable = null;
        $paymentTypeForPaymentTable = null;
        $subscription = null;
        $isSuccess = true;
        $transactionStatus = 'pending';

        if ($customerType === 'member') {
            $memberId = $request->input('member_id');
            $memberPaymentCode = $request->input('member_payment_code');

            $subscription = $outlet->owner->members()
                ->where('member_id', $memberId)
                ->wherePivot('code', $memberPaymentCode)
                ->first()->pivot ?? null;

            if (!$subscription) {
                return redirect()->back()->with('error', 'Kode bayar member tidak valid atau tidak ditemukan untuk member ini.');
            }

            if ($subscription->is_used) {
                return redirect()->back()->with('error', 'Kode bayar member sudah digunakan.');
            }

            if ($subscription->pin_generated_time && Carbon::parse($subscription->pin_generated_time)->addHours(24)->isPast()) {
                return redirect()->back()->with('error', 'Kode bayar member sudah kadaluarsa. Mohon generate kode baru.');
            }

            if ($subscription->amount < $request->amount) {
                $isSuccess = false;
                $transactionStatus = 'failed';
            }

            $member = Member::with('user')->find($memberId);
            if ($member) {
                $customerName = $member->user->name ?? null;
                $customerPhoneNumber = $member->phone_number ?? null;
            }

            $paymentMethodForPaymentTable = 'member';
            $paymentTypeForPaymentTable = null;

        } else { // non_member
            $customerName = $request->input('customer_name');
            $customerPhoneNumber = $request->input('customer_phone_number');
            $paymentMethodForPaymentTable = 'non_member';
            $paymentTypeForPaymentTable = $request->input('payment_type');
            $transactionStatus = 'pending'; // Status awal untuk non-member, akan diupdate ke 'success'
        }


        $time = Carbon::now();
        $order_id = generateOrderId($outlet->code, $channelType, $time, 'multi');

        DB::beginTransaction();
        try {
            // Logika untuk menentukan harga layanan
            $service_price = ($paymentMethodForPaymentTable === 'member') ? $service->member_price : $service->non_member_price;

            // Buat entri Transaction
            $transaction = Transaction::create([
                'owner_id' => $outlet->owner->id,
                'outlet_id' => $outlet->id,
                'member_id' => $memberId,
                'order_id' => $order_id,
                'amount' => $request->amount,
                'timezone' => $outlet->timezone,
                'time' => $time,
                'date' => $time->toDateString(),
                'channel_type' => $channelType,
                'status' => $transactionStatus,
            ]);

             DropOffTransaction::create([
                    'transaction_id' => $transaction->id,
                    'service_id' => $service->id,
                    'addons' => json_encode($addonsToSave),
                    'notes' => $request->notes,
                    'service_price' => $service_price,
                    'cashier_name' => $request->cashier_name,
                    'estimated_completion_at' => $request->estimated_completion_at ? Carbon::parse($request->estimated_completion_at)->toDateString() : null,
                    'customer_name' => $customerName,
                    'customer_phone_number' => $customerPhoneNumber,
                    'payment_type' => $paymentTypeForPaymentTable,
                    'unit' => $service->unit,
                    'quantity' => $request->unit,
                    'device_code' => $device->code ?? null
                ]);


            // Hanya proses pembayaran dan update jika transaksi berhasil
            if ($isSuccess) {
                // Update status transaksi menjadi 'success' jika non_member
                if ($customerType === 'non_member') {
                    $transaction->update(['status' => 'success']);
                }

                // Potong saldo member jika tipe customer adalah member
                if ($customerType === 'member' && $subscription) {
                    $subscription->amount -= $request->amount;
                    $subscription->is_used = true;
                    $subscription->save();
                    $transaction->update(['status' => 'success']);
                }

                // Buat entri Payment
                $payment = Payment::create([
                    'transaction_id' => $transaction->id,
                    'owner_id' => $outlet->owner->id,
                    'outlet_id' => $outlet->id,
                    'payment_method' => $paymentMethodForPaymentTable,
                    'amount' => $request->amount,
                    'payment_time' => $time->format('Y-m-d H:i:s'),
                ]);


                if ($device) {
                    foreach ($service->serviceOptions as $serviceOption) {
                        DeviceTransaction::create([
                            'transaction_id' => $transaction->id,
                            'device_code' => $device->code,
                            'service_type' => $serviceOption->serviceType->name,
                            'status' => true,
                            'bypass_activation' => now()
                        ]);
                    }
                }

                $ownerUser = $outlet->owner->user;
                $cashierUsers = $outlet->cashiers->map(fn($cashier) => $cashier->user);
                $ownerAndCashiers = $cashierUsers->push($ownerUser);
                event(new NotificationEvent(
                    recipients: $ownerAndCashiers,
                    title: '💸 Transaksi Baru di Outlet ' . $outlet->name,
                    message: 'Transaksi sebesar Rp. ' . number_format($request->amount, 0, ',', '.') . ' telah berhasil dicatat oleh kasir ' . $request->cashier_name . ' di outlet Anda.',
                    url: route('partner.transactions.index')
                ));

                if ($customerType === 'member') {
                    $memberUser = Member::find($memberId)->user;
                    event(new NotificationEvent(
                        recipients: $memberUser,
                        title: '🎉 Pembayaran Berhasil',
                        message: 'Pembayaran sebesar Rp. ' . number_format($request->amount, 0, ',', '.') . ' untuk layanan ' . $service->name . ' di outlet ' . $outlet->name . ' telah berhasil dilakukan.',
                        url: route('home.member.transactions')
                    ));
                }

                DB::commit();

                return redirect()->back()->with([
                    'success' => 'Transaksi berhasil dilakukan sebesar Rp. ' . number_format($request->amount, 0, ',', '.') . '.',
                    'new_transaction' => $transaction
                ]);

            } else { // Transaksi gagal (Saldo member tidak cukup)
                // Notifikasi khusus untuk member jika saldo tidak cukup
                $memberUser = Member::find($memberId)->user;
                event(new NotificationEvent(
                    recipients: $memberUser,
                    title: 'Saldo Tidak Cukup!',
                    message: 'Pembayaran gagal. Saldo Anda tidak mencukupi untuk transaksi sebesar Rp. ' . number_format($request->amount, 0, ',', '.') . '. Mohon lakukan top-up terlebih dahulu.',
                    url: route('home.member.dashboard')
                ));

                DB::commit();

                return redirect()->back()->with('error', 'Saldo member tidak mencukupi untuk transaksi ini.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cashier payment failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_data' => $request->all(),
                'exception_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Pembayaran kasir gagal disimpan: ' . $e->getMessage());
        }
    }

}
