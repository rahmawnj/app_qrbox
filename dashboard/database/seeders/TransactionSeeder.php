<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $devices = DB::table('devices')->get();
        if ($devices->isEmpty()) {
            throw new \Exception('Devices kosong.');
        }

        $timezones  = ['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'];
        $channels   = ['web', 'mobile', 'pos'];
        $services   = ['washer','dryer_a','dryer_b','turnstile','dispenser_a','dispenser_b'];
        $txTypes    = ['payment', 'withdrawal'];
        $txStatuses = ['success', 'pending', 'failed'];

        for ($i = 0; $i < 1000; $i++) {

            $device = $devices->random();
            $outlet = DB::table('outlets')->where('id', $device->outlet_id)->first();

            $owner_id  = $outlet->owner_id;
            $outlet_id = $outlet->id;

            $gross = rand(50_000, 500_000);
            $feePercent = rand(5, 15) / 100;
            $feeAmount  = (int) round($gross * $feePercent);
            $net        = $gross - $feeAmount;

            $dateTime = Carbon::now()
                ->subDays(rand(0, 30))
                ->setTime(rand(0, 23), rand(0, 59), rand(0, 59));

            $transactionType   = $txTypes[array_rand($txTypes)];
            $transactionStatus = $txStatuses[array_rand($txStatuses)];

            /** ================= TRANSACTION ================= */
            $transactionId = DB::table('transactions')->insertGetId([
                'owner_id' => $owner_id,
                'order_id' => strtoupper(Str::random(10)),
                'type' => $transactionType,
                'gross_amount' => $gross,
                'amount' => $net,
                'service_fee_amount' => $feeAmount,
                'service_fee_percentage' => $feePercent,
                'timezone' => $timezones[array_rand($timezones)],
                'date' => $dateTime->toDateString(),
                'time' => $dateTime->toTimeString(),
                'status' => $transactionStatus,
                'notes' => $transactionStatus !== 'success'
                    ? strtoupper($transactionType) . ' ' . strtoupper($transactionStatus)
                    : null,
                'created_at' => $dateTime,
                'updated_at' => $dateTime,
            ]);

            /** =================================================
             *  IF NOT SUCCESS → STOP
             * ================================================= */
            if ($transactionStatus !== 'success') {
                continue;
            }

            /** =================================================
             *  PAYMENT SUCCESS
             * ================================================= */
            if ($transactionType === 'payment') {

                DB::table('payments')->insert([
                    'transaction_id' => $transactionId,
                    'outlet_id' => $outlet_id,
                    'owner_id' => $owner_id,
                    // 'payment_method' => rand(0,1) ? 'member' : 'non_member',
                    'amount' => $net,
                    'payment_time' => $dateTime,
                    // 'channel_type' => $channels[array_rand($channels)],
                    'timezone' => $timezones[array_rand($timezones)],
                    'service_fee_amount' => $feeAmount,
                    'service_fee_percentage' => $feePercent,
                    'created_at' => $dateTime,
                    'updated_at' => $dateTime,
                ]);

                DB::table('device_transactions')->insert([
                    'transaction_id' => $transactionId,
                    'outlet_id' => $outlet_id,
                    'owner_id' => $owner_id,
                    'device_code' => $device->code,
                    'service_type' => $services[array_rand($services)],
                    'activated_at' => $dateTime,
                    'status' => true,
                    'created_at' => $dateTime,
                    'updated_at' => $dateTime,
                ]);
            }

            /** =================================================
             *  WITHDRAWAL SUCCESS
             * ================================================= */
            if ($transactionType === 'withdrawal') {

                $requestAmount = rand(30_000, $net);
                $wdFee         = rand(2_000, 5_000);

                DB::table('withdrawals')->insert([
                    'owner_id' => $owner_id,
                    'requested_amount' => $requestAmount,
                    'amount_before_fee' => $requestAmount,
                    'withdrawal_fee' => $wdFee,
                    'amount_after_fee' => $requestAmount - $wdFee,
                    'amount' => $requestAmount,
                    'notes' => 'Auto generated withdrawal',
                    'approved_at' => $dateTime->copy()->addMinutes(rand(5,120)),
                    'bank_name' => collect(['BCA','BRI','BNI','MANDIRI'])->random(),
                    'bank_account_number' => '1' . rand(100000000,999999999),
                    'bank_account_holder_name' => 'OWNER ' . strtoupper(Str::random(4)),
                    'created_at' => $dateTime,
                    'updated_at' => $dateTime,
                ]);
            }
        }
    }
}
