<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Device;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use App\Events\NotificationEvent;
use App\Models\DeviceTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Models\QrisTransactionDetail;
use App\Models\SelfServiceTransaction;

class QrisController extends Controller
{
    public function qr_request(Request $request)
    {
        $type = $request->input('type');
        $deviceCode = $request->input('device_code');
        $apiToken = $request->input('api_token');

        $device = Device::where('code', $deviceCode)->first();
        if ($device->outlet->device_token !== $apiToken) {
            return response()->json([
                "status" => "error",
                "message" => "Token tidak valid atau tidak diizinkan"
            ], 401);
        }

        if (!$device) {
            return response()->json(["status" => "error", "message" => "Device tidak ditemukan"], 404);
        }

        $originalPrice = 0;
        for ($i = 1; $i <= 4; $i++) {
            $optionKey = "option_$i";
            $optionData = $device->$optionKey;

            if (is_string($optionData)) {
                $optionData = json_decode($optionData, true);
            }

            if ($optionData && isset($optionData['type']) && $optionData['type'] === $type) {
                $originalPrice = $optionData['price'];
                break;
            }
        }

        if ($originalPrice <= 0) {
            return response()->json(["status" => "error", "message" => "Tipe layanan '$type' tidak ditemukan."], 400);
        }

        // 3. Cari Outlet & Owner
        $outlet = $device->outlet;
        $owner = $outlet ? $outlet->owner : null;
        if (!$owner) {
            return response()->json(["status" => "error", "message" => "Data Owner/Outlet tidak lengkap"], 404);
        }

        // 4. Perhitungan Pajak/Fee (User bayar harga NET setelah dipotong fee)
        $feePercentage = $owner->service_fee_percentage ?? 0.1;
        $feeAmount = $originalPrice * $feePercentage;
        $finalAmountToPay = $originalPrice - $feeAmount; // Nilai yang harus dibayar user ke Xendit

        $currentTime = Carbon::now();

        DB::beginTransaction();
    try {
        $orderId = 'TRX-' . $outlet->code . '-' . time() . '-' . strtoupper(uniqid());

        $transaction = Transaction::create([
            'order_id'               => $orderId,
            'owner_id'               => $owner->id,
            'type'                   => 'payment',
            'gross_amount'           => $originalPrice,
            'service_fee_amount'     => $feeAmount,
            'service_fee_percentage' => $feePercentage,
            'amount'                 => $finalAmountToPay,
            'timezone'               => $outlet->timezone ?? 'Asia/Jakarta',
            'status'                 => 'pending',
            'date'                   => $currentTime->toDateString(),
            'time'                   => $currentTime->toTimeString(),
        ]);
       $selfservice = SelfServiceTransaction::create([
            'transaction_id' => $transaction->id,
            'owner_id'      => $owner->id,
            'outlet_id'      => $outlet->id,
            'device_code'    => $deviceCode,
            'service_type'   => $type,
            'device_status'  => 1,
        ]);
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $midtransUrl = env('MIDTRANS_URL_API');

        $requestBody = [
            "payment_type" => "qris",
            "transaction_details" => [
                "order_id"     => $orderId,
                "gross_amount" => (int)$originalPrice, // Gunakan nominal akhir
            ],
            "item_details" => [
                [
                    "id"       => $type,
                    "price"    => (int)$originalPrice,
                    "quantity" => 1,
                    "name"     => "Layanan " . ucfirst($type) . " - " . $owner->brand_name,
                ]
            ],
            "customer_details" => [
                "owner_name" => $owner->user->name,
                "brand_name" => $owner->brand_name,
                "outlet_name"      => $device->outlet,
                "email"      => $owner->user->email,
            ],
            "qris" => [
                "acquirer" => "gopay"
            ]
        ];

        $response = Http::withBasicAuth($serverKey, '')
            ->withHeaders([
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post($midtransUrl, $requestBody);

        if (!$response->successful()) {
            throw new \Exception("Midtrans Error: " . $response->body());
        }

        $midtransResult = $response->json();

        $qrString = '';
        foreach ($midtransResult['actions'] as $action) {
            if ($action['name'] == 'generate-qr-code') {
                $qrString = $action['url'];
                break;
            }
        }

        $qrisDetail = QrisTransactionDetail::create([
            'payment_url'          => $midtransResult['transaction_id'], // ID Transaksi Midtrans
            'qr_code_image'        => '',
            'transactionable_id'   => $transaction->id,
            'transactionable_type' => Transaction::class,
        ]);

        $amountFormatted = 'Rp ' . number_format($originalPrice, 0, ',', '.');
        $filePath = $this->generateQRCode($midtransResult['actions'][0]['url'], $orderId, $outlet->outlet_name, $amountFormatted);

        $qrisDetail->update([
            'qr_code_image' => basename($filePath)
        ]);

        DB::commit();

        return response()->json([
            "status" => "success",
            "message" => [
                "order_id"       => $orderId,
                "device_name"    => $device->name,
                "device_code"    => $deviceCode,
                "transaction_id" => $transaction->id,
                "payment_status" => "pending",
                "qr_image"       => url('storage/qrcodes/' . basename($filePath)),
                "expires_at"     => $midtransResult['expiry_time'] ?? null,
                "original_price" => $originalPrice,
                "final_amount"   => $finalAmountToPay,
                "fee_deducted"   => $feeAmount
            ],
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Midtrans QR Request Failed: " . $e->getMessage());
        return response()->json([
            "status"  => "error",
            "message" => "Gagal memproses QRIS Midtrans: " . $e->getMessage()
        ], 500);
    }
    }

    function generateQRCode($qrUrl, $orderId, $textAbove = 'Merchant Name', $textBelow = 'Rp 0')
    {
        $targetWidth = 320;
        $targetHeight = 480;

        $backgroundImageUrl = asset('assets/img/qristempl.jpg');
        $bgFilePath = public_path(parse_url($backgroundImageUrl, PHP_URL_PATH));
        $bgOriginal = imagecreatefromjpeg($bgFilePath);

        $bgGdImage = imagecreatetruecolor($targetWidth, $targetHeight);
        $white = imagecolorallocate($bgGdImage, 255, 255, 255);
        imagefill($bgGdImage, 0, 0, $white);

        $headerY = 20;
        $originalWidth = imagesx($bgOriginal);
        $originalHeight = imagesy($bgOriginal);

        imagecopyresampled($bgGdImage, $bgOriginal, 0, $headerY, 0, 0, $targetWidth, (int)($originalHeight * ($targetWidth / $originalWidth)), $originalWidth, $originalHeight);

        $qrImageContent = file_get_contents($qrUrl);
        $qrGdImageOriginal = imagecreatefromstring($qrImageContent);
        $qrSize = 300;
        $qrGdImage = imagecreatetruecolor($qrSize, $qrSize);
        imagecopyresampled($qrGdImage, $qrGdImageOriginal, 0, 0, 0, 0, $qrSize, $qrSize, imagesx($qrGdImageOriginal), imagesy($qrGdImageOriginal));

        $merchantY = $headerY + 80;
        $qrY = $merchantY + 30;
        $priceY = 430;

        $qrX = ($targetWidth - $qrSize) / 2;
        imagecopy($bgGdImage, $qrGdImage, (int)$qrX, (int)$qrY, 0, 0, $qrSize, $qrSize);

        $textColor = imagecolorallocate($bgGdImage, 0, 0, 0);
        $fontSize = 5;

        $textAboveWidth = imagefontwidth($fontSize) * strlen($textAbove);
        $textAboveX = ($targetWidth - $textAboveWidth) / 2;
        imagestring($bgGdImage, $fontSize, (int)$textAboveX, (int)$merchantY, $textAbove, $textColor);

        $textBelowWidth = imagefontwidth($fontSize) * strlen($textBelow);
        $textBelowX = ($targetWidth - $textBelowWidth) / 2;

        imagestring($bgGdImage, $fontSize, (int)$textBelowX, (int)$priceY, $textBelow, $textColor);
        imagestring($bgGdImage, $fontSize, (int)$textBelowX + 1, (int)$priceY, $textBelow, $textColor);
        imagestring($bgGdImage, $fontSize, (int)$textBelowX, (int)$priceY + 1, $textBelow, $textColor);

        $folder = storage_path('app/public/qrcodes/');
        if (!file_exists($folder)) { mkdir($folder, 0777, true); }
        $filePath = $folder . $orderId . '.jpg';
        imagejpeg($bgGdImage, $filePath, 100);

        imagedestroy($qrGdImageOriginal);
        imagedestroy($qrGdImage);
        imagedestroy($bgOriginal);
        imagedestroy($bgGdImage);

        return $filePath;
    }

    public function checkPaymentStatus(Request $request)
    {
        $apiToken = $request->query('api_token');
        $orderId  = $request->query('order_id');

        if (!$orderId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Order ID is required'
            ], 400);
        }

        try {
            $transaction = Transaction::where('order_id', $orderId)
                ->where('created_at', '>=', now()->subHour())
                ->with('deviceTransactions')
                ->first();

            // ❌ TRANSACTION TIDAK DITEMUKAN
            if (!$transaction) {
                return response()->json([
                    'status'  => 'error',
                    'message' => [
                        'order_id'    => $orderId,
                        'description' => 'Order not found.'
                    ]
                ]);
            }

            $transactionStatus = $transaction->status;
            $qrCodeImage       = $transaction->qr_code_image;

            // ⛔ BELUM DIBAYAR → JANGAN SENTUH DEVICE
            if ($transactionStatus !== 'success') {
                return response()->json([
                    'status'  => 'success',
                    'message' => [
                        'order_id'        => $orderId,
                        'payment_status'  => $transactionStatus,
                        'qr_code_deleted' => false,
                        'description'     => 'Pembayaran tidak berhasil.'
                    ]
                ]);
            }

            // ✅ BARU MASUK KE DEVICE TRANSACTION
            $deviceTransaction = $transaction->deviceTransactions->first();

            if (!$deviceTransaction) {
                throw new \Exception('Device transaction not found for successful payment.');
            }

            $device = Device::where('code', $deviceTransaction->device_code)->first();

            if (!$device || !$device->outlet) {
                throw new \Exception('Device or outlet not found.');
            }

            // 🔐 VALIDASI TOKEN SETELAH PAYMENT SUCCESS
            if ($device->outlet->device_token !== $apiToken) {
                return response()->json([
                    "status"  => "error",
                    "message" => "Token tidak valid atau tidak diizinkan"
                ], 401);
            }

            // 🔄 UPDATE DEVICE TRANSACTION
            $deviceStatus = $deviceTransaction->status;

            $deviceTransaction->update([
                'status'            => false,
                'bypass_activation' => now()
            ]);

            Log::info("DeviceTransaction ID {$deviceTransaction->id} updated after successful payment.");

            if ($qrCodeImage) {
                Log::info("QR Code for order ID {$orderId} would be deleted here.");
            }

            return response()->json([
                'status'  => 'success',
                'message' => [
                    'type'             => $deviceTransaction->service_type,
                    'order_id'         => $orderId,
                    'payment_status'   => $transactionStatus,
                    'device_status'    => $deviceStatus,
                    'qr_code_deleted'  => (bool) $qrCodeImage,
                    'description'      => 'Pembayaran Berhasil.'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking payment status: ' . $e->getMessage(), [
                'order_id'  => $orderId,
                'exception' => $e
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }


    public function checkPaymentStatus2(Request $request)
    {
        $service_type = $request->query('service_type');
        $code_device = $request->query('device_code');
        $apiToken = $request->query('api_token');

        if (!$service_type) {
            return response()->json([
                'status'  => 'error',
                'message' => 'service_type is required'
            ], 400);
        }
        if (!$code_device) {
            return response()->json([
                'status'  => 'error',
                'message' => 'device_code is required'
            ], 400);
        }

        $device = Device::where('code', $code_device)->first();

        if (!$device) {
            return response()->json([
                'status'  => 'error',
                'message' => 'device code is not registered'
            ], 400);
        }
        if ($device->outlet->device_token !== $apiToken) {
                return response()->json([
                    "status" => "error",
                    "message" => "Token tidak valid atau tidak diizinkan"
                ], 401);
            }


        try {
            $transaction = Transaction::where('status', 'success') // Langsung cari transaksi yang status-nya 'success'
                ->whereHas('deviceTransactions', function ($query) use ($service_type, $code_device) {
                    $query->where('service_type', $service_type)
                        ->where('device_code', $code_device)
                        ->where('status', true)
                        ;
                })
                ->where('created_at', '>=', now()->subHour())
                ->orderBy('created_at', 'asc')
                ->with('deviceTransactions')
                ->first();

            $orderId = $transaction ? $transaction->order_id : null;

            if ($transaction) {
                $transactionStatus = $transaction->status;
                $qrCodeImage = $transaction->qr_code_image;

                $deviceTransaction = $transaction->deviceTransactions->first();
                $deviceStatus = null;

                if ($deviceTransaction) {
                    $deviceStatus = $deviceTransaction->status;
                    if ($qrCodeImage) {
                        Log::info("QR Code for order ID {$orderId} would be deleted here (checkPaymentStatus2).");
                    }
                    // Update device transaction status to false and record activation time
                    $deviceTransaction->update(['status' => false, 'activated_at' => now()]);
                    Log::info("DeviceTransaction ID {$deviceTransaction->id} for Transaction ID {$transaction->id} updated to status: false (checkPaymentStatus2).");
                }

                return response()->json([
                    'status'  => 'success',
                    'message' => [
                        'order_id'        => $orderId,
                        'payment_status'  => $transactionStatus,
                        'device_status'   => $deviceStatus,
                        'amount'          => $transaction->amount,
                        'description'     => 'Pembayaran Berhasil.',
                        'qr_code_deleted' => (bool)$qrCodeImage,
                    ]
                ]);
            } else {
                return response()->json([
                    'status'  => 'error', // Ubah status menjadi 'error' karena tidak ada transaksi yang ditemukan
                    'message' => [
                        'order_id'    => null,
                        'description' => 'Order not found for the given criteria or payment is not yet successful.'
                    ]
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error checking payment status 2: ' . $e->getMessage(), [
                'service_type' => $service_type ?? 'N/A',
                'device_code' => $code_device ?? 'N/A',
                'exception' => $e
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }

    private function deleteQRCode($orderId)
    {
        $filePath = storage_path('app/public/qrcodes/' . $orderId . '.jpg');

        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function updateTransactionStatus(Request $request)
    {
        $notification = $request->json()->all();
        Log::info('Midtrans Callback Received:', $notification);

        // 1. Identifikasi Data Utama dari Midtrans
        $orderId = $notification['order_id'] ?? null;
        $midtransStatus = $notification['transaction_status'] ?? null;
        $grossAmount = $notification['gross_amount'] ?? 0;

        if (!$orderId || !$midtransStatus) {
            Log::warning('Invalid Midtrans notification data:', $notification);
            return response()->json(['status' => 'error', 'message' => 'Invalid data.'], 400);
        }

        $statusMap = [
            'settlement' => 'success',
            'capture'    => 'success',
            'pending'    => 'pending',
            'deny'       => 'failed',
            'expire'     => 'expired',
            'cancel'     => 'failed'
        ];

        $internalStatus = $statusMap[$midtransStatus] ?? 'pending';

        DB::beginTransaction();
        try {
            // 3. Cari Transaksi berdasarkan order_id
            $transaction = Transaction::where('order_id', $orderId)->first();

            if (!$transaction) {
                throw new \Exception("Transaction with order_id '$orderId' not found.");
            }

            // Cegah proses ulang jika status sudah sukses
            if ($transaction->status === 'success') {
                return response()->json(['status' => 'success', 'message' => 'Already processed.']);
            }

            // 4. Update Status Transaksi Utama
            $transaction->update(['status' => $internalStatus]);

            // 5. Jika SUCCESS, Proses Logika Bisnis (Update Saldo & Create Payment)
            if ($internalStatus === 'success') {

                // A. Update Saldo Owner
                // Gunakan $transaction->amount (net amount sesuai skema tabel kamu)
                $owner = $transaction->owner;
                if ($owner) {
                    $owner->increment('balance', $transaction->amount);
                    Log::info("Balance Updated for Owner {$owner->id}. Added: {$transaction->amount}");
                }
                $selfServiceTransaction = $transaction->selfServiceTransaction;

                // dd($selfServiceTransaction);
                Payment::updateOrCreate(
                    ['transaction_id' => $transaction->id],
                    [
                        'outlet_id'              => $selfServiceTransaction->outlet_id, // Pastikan di model Transaction ada outlet_id
                        'owner_id'               => $transaction->owner_id,
                        'amount'                 => $transaction->amount, // Net amount
                        'payment_time'           => $notification['settlement_time'] ?? now(),
                        'timezone'               => $transaction->timezone,
                        'service_fee_amount'     => $transaction->service_fee_amount,
                        'service_fee_percentage' => $transaction->service_fee_percentage,
                        'notes'                  => 'Midtrans payment: ' . ($notification['payment_type'] ?? 'unknown'),
                    ]
                );

                // if (isset($notification['metadata']['device_code'])) {
                    DeviceTransaction::updateOrCreate(
                        ['transaction_id' => $transaction->id],
                        [
                            'owner_id'       => $selfServiceTransaction->owner_id,
                            'outlet_id'       => $selfServiceTransaction->outlet_id,
                            'device_code'       => $selfServiceTransaction->device_code,
                            'service_type'      => $selfServiceTransaction->service_type,
                            'bypass_activation' => now(),
                            'status'            => true,
                        ]
                    );
                    Log::info("Device activated for Order ID: {$orderId}");
                // }

                if (method_exists($this, 'sendSuccessNotifications')) {
                    // $this->sendSuccessNotifications($transaction, $transaction->amount);
                }
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Status updated to ' . $internalStatus]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Midtrans Callback Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
/**
 * Helper function untuk merapikan notifikasi
 */
    private function sendSuccessNotifications($transaction, $amountPaid)
    {
        $owner = $transaction->owner;
        // $cashiers = $transaction->outlet->cashiers->map(fn($c) => $c->user);
        // $recipients = $cashiers->push($owner->user);
        $formattedAmount = number_format($amountPaid, 0, ',', '.');

        event(new NotificationEvent(
            recipients: $recipients,
            title: '💸 Pembayaran Berhasil',
            message: "Transaksi ID {$transaction->order_id} sebesar Rp{$formattedAmount} telah masuk ke saldo.",
            url: route('partner.qris.history'),
        ));

        $admins = User::where('role', 'admin')->get();
        if ($admins->isNotEmpty()) {
            event(new NotificationEvent(
                recipients: $admins,
                title: '✅ Transaksi Berhasil',
                message: "Transaksi ID **{$transaction->order_id}** telah selesai.",
                url: route('admin.qris.history', ['transaction' => $transaction->id]),
            ));
        }
    }
}
