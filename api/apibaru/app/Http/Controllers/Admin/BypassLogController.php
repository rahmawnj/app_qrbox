<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class BypassLogController extends Controller
{
  public function index(Request $request)
{
    // 1. Tangkap input daterange untuk dikirim balik ke view
    $daterangeValue = $request->input('daterange', '');

    $query = DB::table('bypass_records')
        ->join('devices', 'bypass_records.device_id', '=', 'devices.id')
        ->leftJoin('outlets', 'devices.outlet_id', '=', 'outlets.id')
        ->leftJoin('owners', 'outlets.owner_id', '=', 'owners.id')
        ->select(
            'bypass_records.*',
            'devices.name as device_name',
            'devices.code as device_code',
            'outlets.outlet_name as outlet_name',
            'outlets.code as outlet_code',
            'outlets.address as outlet_address',
            'owners.brand_name as brand_name'
        );

    // Filter search
    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where('outlets.outlet_name', 'like', '%' . $search . '%')
                ->orWhere('devices.name', 'like', '%' . $search . '%')
                ->orWhere('devices.code', 'like', '%' . $search . '%')
                ->orWhere('bypass_records.bypass_status', 'like', '%' . $search . '%');
        });
    }

    if ($request->filled('type')) {
        $query->where('bypass_records.type', $request->input('type'));
    }

    // Filter daterange
    if ($request->filled('daterange')) {
        $dateRange = explode(' - ', $request->input('daterange'));
        if (count($dateRange) === 2 && !empty($dateRange[0]) && !empty($dateRange[1])) {
            try {
                // Gunakan format d/m/Y (standar PHP Carbon) bukan DD/MM/YYYY
                $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($dateRange[0]))->startOfDay();
                $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($dateRange[1]))->endOfDay();

                $query->whereBetween('bypass_records.created_at', [$startDate, $endDate]);
            } catch (\Exception $e) {
                Log::error('Date Range Filter Error: ' . $e->getMessage());
            }
        }
    }

    $logs = $query->orderBy('bypass_records.created_at', 'desc')->paginate(15);

    // 2. Pastikan 'daterangeValue' masuk ke compact
    return view('admin.bypass_log', compact('logs', 'daterangeValue'));
}
}
