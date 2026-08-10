<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Owner;
use App\Models\Device;
use App\Models\Member;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\ServiceType;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\DeviceTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        // 1. Identifikasi User & Data Akses via helper getData()
        $isAdmin = auth('admin_config')->check();
        $access = getData(); // Mengambil data outlet & owner yang diizinkan
        $accessibleOutletIds = $access->outlets->pluck('id')->toArray();

        // 2. Logika Filter Owner dari Request
        $ownerIds = $request->input('owner_ids');

        if (!$isAdmin) {
            // Jika Partner: Paksa filter hanya ke ID owner miliknya sendiri
            $ownerIds = [auth()->user()->owner->id];
        } else {
            // Jika Admin: Bersihkan array input
            $ownerIds = is_array($ownerIds) ? array_filter($ownerIds) : [];
        }
        // 3. Filter Tanggal
        $daterange = $request->input('daterange');
        if ($daterange) {
            $dateRange = str_contains($daterange, ' to ') ? explode(' to ', $daterange) : explode(' - ', $daterange);
            $startDate = \Carbon\Carbon::createFromFormat('Y/m/d', trim($dateRange[0]))->startOfDay();
            $endDate = \Carbon\Carbon::createFromFormat('Y/m/d', trim($dateRange[1]))->endOfDay();
        } else {
            $startDate = \Carbon\Carbon::now()->subDays(30)->startOfDay();
            $endDate = \Carbon\Carbon::now()->endOfDay();
            $daterange = $startDate->format('Y/m/d') . ' - ' . $endDate->format('Y/m/d');
        }

        // --- 4. BASE QUERIES (Saring berdasarkan Akses Outlet & Filter Owner) ---
        // Semua query harus masuk ke dalam scope $accessibleOutletIds dari getData()
        $baseTransaction = DB::table('device_transactions')
            ->whereIn('outlet_id', $accessibleOutletIds) // Filter Keamanan Utama
            ->when(!empty($ownerIds), function($query) use ($ownerIds) {
                return $query->whereIn('owner_id', $ownerIds);
            })
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Summary Data
        $totalOutlets = DB::table('outlets')
            ->whereIn('id', $accessibleOutletIds)
            ->when(!empty($ownerIds), fn($q) => $q->whereIn('owner_id', $ownerIds))
            ->count();

        $totalDevices = DB::table('devices')
            ->whereIn('outlet_id', $accessibleOutletIds)
            ->count();
        // dd($ownerIds, $accessibleOutletIds);

        $totalOwnerBalance = DB::table('owners')
            ->when($isAdmin, function($q) use ($ownerIds) {
                // Jika admin, filter berdasarkan pilihan, jika kosong biarkan (ambil semua yang terdaftar)
                return $q->when(!empty($ownerIds), fn($sq) => $sq->whereIn('id', $ownerIds));
            }, function($q) {
                // Jika partner, paksa ke owner_id dia sendiri
                return $q->where('id', auth()->user()->owner->id);
            })
            ->sum('balance');

        $totalDepositAmount = DB::table('owners')
            ->when($isAdmin, function($q) use ($ownerIds) {
                return $q->when(!empty($ownerIds), fn($sq) => $sq->whereIn('id', $ownerIds));
            }, function($q) {
                return $q->where('id', auth()->user()->owner->id);
            })
            ->sum('deposit_amount');

        // --- 5. TREN JUMLAH TRANSAKSI (BAR) ---
        $dailyCount = (clone $baseTransaction)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // --- 6. TREN UANG HARIAN (MULTI LINE) ---
        $dailyMoney = DB::table('device_transactions as dt')
            ->join('payments as p', 'dt.transaction_id', '=', 'p.transaction_id')
            ->select(
                DB::raw('DATE(dt.created_at) as date'),
                'dt.service_type',
                DB::raw('SUM(p.amount) as total_money')
            )
            ->whereIn('dt.outlet_id', $accessibleOutletIds) // Filter Keamanan
            ->when(!empty($ownerIds), fn($q) => $q->whereIn('dt.owner_id', $ownerIds))
            ->whereBetween('dt.created_at', [$startDate, $endDate])
            ->groupBy('date', 'dt.service_type')
            ->get();

        // Mapping Chart Data (Multi-Line)
        $dates = $dailyCount->pluck('date')->toArray();
        $serviceTypes = ['washer', 'dryer_a', 'dryer_b', 'turnstile', 'dispenser_a', 'dispenser_b', 'dispenser_c', 'dispenser_d'];
        $multiLineSeries = [];
        foreach ($serviceTypes as $type) {
            $dataPoint = [];
            foreach ($dates as $date) {
                $found = $dailyMoney->where('date', $date)->where('service_type', $type)->first();
                $dataPoint[] = $found ? (int)$found->total_money : 0;
            }
            $multiLineSeries[] = ['name' => ucfirst($type), 'data' => $dataPoint];
        }

        // --- 7. TREN PER JAM & TOP DEVICE ---
       $hourlyData = (clone $baseTransaction)
    ->selectRaw('EXTRACT(HOUR FROM created_at) as hour, COUNT(*) as total')
    ->groupBy('hour')
    ->orderBy('hour')
    ->get();


        $hourlyLabels = []; $hourlyValues = [];
        for ($i = 0; $i < 24; $i++) {
            $hourlyLabels[] = sprintf('%02d:00', $i);
            $found = $hourlyData->where('hour', $i)->first();
            $hourlyValues[] = $found ? (int)$found->total : 0;
        }

        $deviceTrend = (clone $baseTransaction)
            ->select('device_code', DB::raw('COUNT(*) as total'))
            ->groupBy('device_code')
            ->orderBy('total', 'desc')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalOutlets', 'totalDevices', 'totalOwnerBalance', 'totalDepositAmount',
            'daterange', 'dates', 'dailyCount', 'multiLineSeries',
            'hourlyLabels', 'hourlyValues', 'deviceTrend'
        ));
    }
    private function trendDeviceDonut(?Carbon $startDate, ?Carbon $endDate): array
    {
        // Untuk admin, kita ambil semua outlet
        $outletIds = Outlet::pluck('id')->toArray();

        if (empty($outletIds)) {
            Log::warning('trendDeviceDonut: No outlets found in the system. Returning empty data.');
            return [
                'trendDeviceDonutLabels' => [],
                'trendDeviceDonutData' => []
            ];
        }

        // --- Perubahan utama: Ambil 10 perangkat teratas berdasarkan total transaksi ---
        $topDeviceCodesQuery = DeviceTransaction::select(
            'device_transactions.device_code',
            DB::raw('COUNT(*) as total_transactions_count')
        )
            ->join('transactions', 'device_transactions.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'success')
            ->whereIn('transactions.outlet_id', $outletIds);

        if ($startDate && $endDate) {
            $topDeviceCodesQuery->whereBetween('transactions.created_at', [$startDate, $endDate]);
        }

        $topDeviceCodes = $topDeviceCodesQuery
            ->groupBy('device_transactions.device_code')
            ->orderByDesc('total_transactions_count') // Urutkan dari yang tertinggi
            ->limit(10) // Ambil hanya 10 teratas
            ->pluck('device_code')
            ->toArray();

        // Jika tidak ada 10 perangkat teratas, atau jika topDeviceCodes kosong, return data kosong.
        if (empty($topDeviceCodes)) {
            return [
                'trendDeviceDonutLabels' => [],
                'trendDeviceDonutData' => []
            ];
        }

        // Build the query to count total transactions per device for the TOP 10 devices
        $deviceSummaryQuery = DeviceTransaction::select(
            'device_transactions.device_code',
            DB::raw('COUNT(*) as total_transactions')
        )
            ->join('transactions', 'device_transactions.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'success')
            ->whereIn('transactions.outlet_id', $outletIds)
            ->whereIn('device_transactions.device_code', $topDeviceCodes); // <-- Filter hanya untuk 10 perangkat teratas

        if ($startDate && $endDate) {
            $deviceSummaryQuery->whereBetween('transactions.created_at', [$startDate, $endDate]);
        }

        $deviceSummary = $deviceSummaryQuery
            ->groupBy('device_transactions.device_code')
            ->orderBy('device_transactions.device_code') // Bisa diurutkan berdasarkan code untuk konsistensi
            ->get();

        // Prepare labels and data for the donut chart
        $trendDeviceDonutLabels = $deviceSummary->pluck('device_code')->toArray();
        $trendDeviceDonutData = $deviceSummary->pluck('total_transactions')->map(fn($value) => (int) $value)->toArray();

        return [
            'trendDeviceDonutLabels' => $trendDeviceDonutLabels,
            'trendDeviceDonutData' => $trendDeviceDonutData
        ];
    }


    private function dataMultiDeviceChart(?Carbon $startDate, ?Carbon $endDate): array
    {
        // Untuk admin, kita ambil semua outlet
        $outletIds = Outlet::pluck('id')->toArray();

        if (empty($outletIds)) {
            Log::warning('dataMultiDeviceChart: No outlets found in the system. Returning empty data.');
            return [
                'multiDeviceChartCategories' => [],
                'multiDeviceChartSeriesData' => []
            ];
        }

        // --- Perubahan utama: Pertama, identifikasi 10 perangkat teratas ---
        $topDeviceCodesQuery = DeviceTransaction::select(
            'device_transactions.device_code',
            DB::raw('COUNT(*) as total_transactions_count')
        )
            ->join('transactions', 'device_transactions.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'success')
            ->whereIn('transactions.outlet_id', $outletIds);

        if ($startDate && $endDate) {
            $topDeviceCodesQuery->whereBetween('transactions.created_at', [$startDate, $endDate]);
        }

        $topDeviceCodes = $topDeviceCodesQuery
            ->groupBy('device_transactions.device_code')
            ->orderByDesc('total_transactions_count') // Urutkan dari yang tertinggi
            ->limit(10) // Ambil hanya 10 teratas
            ->pluck('device_code')
            ->toArray();

        // Jika tidak ada 10 perangkat teratas, atau jika topDeviceCodes kosong, return data kosong.
        if (empty($topDeviceCodes)) {
            return [
                'multiDeviceChartCategories' => [],
                'multiDeviceChartSeriesData' => []
            ];
        }

        // Build the query for device transactions over time for the TOP 10 devices
        $deviceTransactionsQuery = DeviceTransaction::select(
            DB::raw('DATE(transactions.created_at) as date'),
            'device_transactions.device_code',
            DB::raw('COUNT(*) as total_transactions')
        )
            ->join('transactions', 'device_transactions.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'success')
            ->whereIn('transactions.outlet_id', $outletIds)
            ->whereIn('device_transactions.device_code', $topDeviceCodes); // <-- Filter hanya untuk 10 perangkat teratas

        if ($startDate && $endDate) {
            $deviceTransactionsQuery->whereBetween('transactions.created_at', [$startDate, $endDate]);
        }

        $deviceTransactions = $deviceTransactionsQuery
            ->groupBy(DB::raw('DATE(transactions.created_at)'), 'device_transactions.device_code')
            ->orderBy(DB::raw('DATE(transactions.created_at)'))
            ->get();

        // Extract unique dates and device codes (now limited to top 10)
        $allDates = $deviceTransactions->pluck('date')->unique()->sort()->values();
        // $allDeviceCodes = $deviceTransactions->pluck('device_code')->unique()->sort()->values();
        // Gunakan $topDeviceCodes yang sudah difilter untuk memastikan konsistensi urutan
        $allDeviceCodes = collect($topDeviceCodes)->sort()->values();


        // Prepare chart categories (dates)
        $multiDeviceChartCategories = $allDates->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))->toArray();

        // Prepare series data for each device
        $multiDeviceChartSeriesData = [];
        foreach ($allDeviceCodes as $deviceCode) {
            $data = $allDates->map(function ($date) use ($deviceTransactions, $deviceCode) {
                $item = $deviceTransactions->first(fn($dt) => $dt->date == $date && $dt->device_code == $deviceCode);
                return (int) optional($item)->total_transactions ?? 0;
            })->toArray();

            $multiDeviceChartSeriesData[] = [
                'name' => $deviceCode,
                'data' => $data
            ];
        }

        return [
            'multiDeviceChartCategories' => $multiDeviceChartCategories,
            'multiDeviceChartSeriesData' => $multiDeviceChartSeriesData
        ];
    }
}
