<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Device;
use App\Models\ServiceType;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\SelfServiceTransaction;

class MemberQrController extends Controller
{
    public function qr_request(Request $request)
    {
        $deviceCode = $request->input('device_code');

        // 1. Validasi input
        if (!$deviceCode) {
            return response()->json([
                "status" => "error",
                "message" => "Parameter 'device_code' tidak boleh kosong."
            ], 400);
        }

        $device = Device::where('code', $deviceCode)->first();
        if (!$device || !$device->outlet) {
            return response()->json([
                "status" => "error",
                "message" => "Outlet terkait device tidak ditemukan"
            ], 404);
        }

        DB::beginTransaction();
        try {

            DB::commit();
            $qrCodeSize = '300x300';
            $qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=$qrCodeSize&data=" . urlencode($deviceCode);

            // 6. Kirim respons sukses
            return response()->json([
                "status"  => "success",
                "message"    => [
                    "device_code" => $device->code,
                    "outlet" => $device->outlet->outlet_name,
                    "brand_name" => $device->outlet->owner->brand_name,
                    "qr_image" => url('storage/qrcodes/' . $qrImageUrl),
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal dalam proses QR request: " . $e->getMessage());
            return response()->json([
                "status"  => "error",
                "message" => "Gagal memproses permintaan QR: " . $e->getMessage()
            ], 500);
        }
    }
}
