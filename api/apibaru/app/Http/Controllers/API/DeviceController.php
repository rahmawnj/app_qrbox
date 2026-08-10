<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Device;
use Illuminate\Http\Request;
use App\Events\NotificationEvent;
use App\Models\DeviceTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\BypassRecord; // Pastikan model BypassRecord di-import di sini

class DeviceController extends Controller
{
    public function checkDeviceStatus(Request $request)
    {
        // Validate if the 'device_code' parameter is present in the request.
        if (!$request->has('device_code')) {
            return response()->json([
                'status'        => 'failure',
                'status_device' => 'off',
                'message'       => 'Parameter device_code harus disertakan.',
                'activation_date' => null,
                'source'        => null
            ], 400);
        }

        // Find the device based on the provided 'device_code'.
        $device = Device::where('code', $request->device_code)->first();

        // If the device is not found, return a 404 response.
        if (!$device) {
            return response()->json([
                'status'        => 'failure',
                'status_device' => 'off',
                'message'       => 'Device tidak ditemukan.',
                'activation_date' => null,
                'source'        => null
            ], 404);
        }

        $deviceBypassActivation = null;
        if (
            $device->device_status !== 'off' &&
            $device->bypass_activation &&
            Carbon::parse($device->bypass_activation)->greaterThanOrEqualTo(Carbon::now()->subHours(24))
        ) {
            $deviceBypassActivation = Carbon::parse($device->bypass_activation);
        }


        // Retrieve the earliest 'bypass_activation' timestamp from 'device_transactions'
        // where status is true and bypass_activation is not null.
        $deviceTransactionBypassActivation = DeviceTransaction::where('device_code', $request->device_code)
            ->whereNotNull('bypass_activation')
            ->where('status', true) // Only consider active bypass transactions
            ->where('bypass_activation', '>=', Carbon::now()->subHours(24)) // activated_at is within the last 24 hours
            ->oldest('bypass_activation') // Get the oldest bypass_activation
            ->first();

        // Get the bypass_activation from the found device transaction.
        $transactionBypassActivation = $deviceTransactionBypassActivation?->bypass_activation;

        $earliestValidBypassActivation = null;
        $sourceOfActivation = null;
        $serviceType = null;
        $note = null; // Tambahkan variabel untuk catatan

        // Determine the earliest valid bypass activation and its source.
        if ($deviceBypassActivation && $transactionBypassActivation) {
            if ($deviceBypassActivation->lessThanOrEqualTo($transactionBypassActivation)) {
                $earliestValidBypassActivation = $deviceBypassActivation;
                $sourceOfActivation = 'bypass';
                // When source is 'device', serviceType reflects the device's current status
                $serviceType = $device->device_status;
                $note = $device->bypass_note; // Ambil catatan dari model Device
            } else {
                $earliestValidBypassActivation = $transactionBypassActivation;
                $sourceOfActivation = 'session';
                $serviceType = $deviceTransactionBypassActivation->service_type;
                $note = null; // Tidak ada catatan untuk transaksi
            }
        } elseif ($deviceBypassActivation) {
            $earliestValidBypassActivation = $deviceBypassActivation;
            $sourceOfActivation = 'bypass';
            $serviceType = $device->device_status;
            $note = $device->bypass_note; // Ambil catatan dari model Device
        } elseif ($transactionBypassActivation) {
            $earliestValidBypassActivation = $transactionBypassActivation;
            $sourceOfActivation = 'session';
            $serviceType = $deviceTransactionBypassActivation->service_type;
            $note = null; // Tidak ada catatan untuk transaksi
        }

        if ($earliestValidBypassActivation) {
            DB::table('bypass_records')->insert([
                'device_id'         => $device->id,
                'type'              => $sourceOfActivation,
                'bypass_activation' => $earliestValidBypassActivation,
                'bypass_status'     => $serviceType,
                'note'              => $note, // Tambahkan catatan ke database
                'created_at'        => now(),
                'updated_at'        => now()
            ]);
        }

        if ($sourceOfActivation == 'bypass') {
            $device->device_status = 'off';
            $device->save();
        } elseif ($sourceOfActivation == 'session') {
            $deviceTransactionBypassActivation->status = false;
            $deviceTransactionBypassActivation->save();
        }

        $serviceType = strtolower($serviceType ?? 'off'); // Default to 'off' if serviceType is null

        return response()->json([
            'status'        => 'success',
            'status_device' => $serviceType, // This will be 'washer', 'dryer', or 'off'
            'source'        => $sourceOfActivation,
            'activation_date' => $earliestValidBypassActivation?->format('Y-m-d H:i:s'),
            'message'       => 'Status diterima'
        ]);
    }


public function toggleStatus(Request $request, Device $device)
    {
        try {
            $newStatus = $request->input('device_status', 'off');
            $bypassNote = $request->input('bypass_note', null);
            $oldStatus = $device->device_status;

            $device->device_status = $newStatus;

            // Atur bypass_activation dan bypass_note hanya jika status bukan 'off'
            if ($newStatus !== 'off') {
                $device->bypass_activation = Carbon::now();
                $device->bypass_note = $bypassNote;
            } else {
                // Jika status 'off', reset bypass_activation dan bypass_note
                $device->bypass_activation = null;
                $device->bypass_note = null;
            }

            $device->save();

            // --- Logika Notifikasi untuk Owner ---
            // Kirim notifikasi HANYA jika status berubah menjadi bypass (bukan 'off')
                $owner = $device->outlet->owner->user;
                Log::info($owner);
                if ($owner) {
                    $message = "Perangkat **{$device->code}** di outlet **{$device->outlet->outlet_name}** telah di-bypass ke status '{$newStatus}'.";
                    if ($bypassNote) {
                        $message .= " Catatan: {$bypassNote}";
                    }

                    event(new NotificationEvent(
                        recipients: $owner,
                        title: '⚠️ Perangkat Di-Bypass',
                        message: $message,
                        url: route('partner.bypass.logs') // Sesuaikan rute
                    ));
                    Log::info("Notification sent to owner {$owner->id} for device bypass: {$device->code}");
                }
            // --- Akhir Logika Notifikasi ---

            Log::info('Device status updated', [
                'device_code' => $device->code,
                'new_status' => $device->device_status,
                'bypass_note' => $bypassNote,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Device ' . $device->code . ' berhasil diperbarui menjadi "' . $device->device_status . '"'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update device status', [
                'device_id' => $device->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui status device. ' . $e->getMessage()
            ], 500);
        }
    }


    public function getDeviceMenu($device_code)
    {
        try {
            // Cari device berdasarkan device_code
            $device = Device::where('code', $device_code)->first();

            if (!$device) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Device tidak ditemukan'
                ], 404);
            }

            $menus = [
                $device->option_1 ?? new \stdClass(),
                $device->option_2 ?? new \stdClass(),
                $device->option_3 ?? new \stdClass(),
                $device->option_4 ?? new \stdClass(),
            ];

            return response()->json([
                'status' => 'success',
                'service_type' => $device->serviceType->name,
                'device_name' => $device->name,
                'device_code' => $device->code,
                'menus' => $menus
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
