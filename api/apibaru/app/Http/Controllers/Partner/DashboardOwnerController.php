<?php

namespace App\Http\Controllers\Partner;

use Carbon\Carbon;
use App\Models\Owner;
use App\Models\Device;
use App\Models\Member;
use App\Models\Outlet;
use App\Models\ServiceType;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\DeviceTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardOwnerController extends Controller
{
    public function dashboard(Request $request)
    {
        // --- 1. Ambil Semua Outlet (untuk Admin) ---
        // Karena ini tampilan admin, kita ambil semua outlet yang ada.
        $outletIds = getData()->outlets->pluck('id')->toArray();

        // Jika tidak ada outlet yang ditemukan sama sekali, kembalikan data kosong
        if (empty($outletIds)) {
            Log::warning('Dashboard: No outlets found in the system. Returning empty data.');
            return view('admin.dashboard')->with([
                'categories' => [],
                'seriesData' => [],
                'donutLabels' => [],
                'donutData' => [],
                'serviceLabels' => [],
                'serviceData' => [],
                'totalTransactions' => 0,
                'totalDropOffTransactions' => 0, // Diperbarui
                'totalSelfServiceTransactions' => 0, // Diperbarui
                'serviceTypesDbNames' => [],
                'totalServiceCounts' => [],
                'dailyTransactionCategories' => [],
                'dailyTransactionSeriesData' => [],
                'hourlyLabels' => [],
                'hourlyData' => [],
                'dataMultiDeviceChart' => ['categories' => [], 'series' => []], // Default kosong
                'trendDeviceDonut' => ['labels' => [], 'data' => []], // Default kosong
                'totalMembers' => 0,
                'totalDevices' => 0,
                'daterangeFilter' => '',
                'totalOutlets' => 0,
                'totalOwners' => 0,
                'message' => 'Tidak ada outlet yang ditemukan di sistem.'
            ]);
        }

        // --- 2. Filter Tanggal ---
        $startDate = null;
        $endDate = null;
        $daterangeFilter = '';

        if ($request->filled('daterange')) {
            $dateRange = explode(' - ', $request->input('daterange'));

            if (count($dateRange) === 2 && !empty($dateRange[0]) && !empty($dateRange[1])) {
                try {
                    $startDate = Carbon::createFromFormat('Y/m/d', $dateRange[0])->startOfDay();
                    $endDate = Carbon::createFromFormat('Y/m/d', $dateRange[1])->endOfDay();
                    $daterangeFilter = $request->input('daterange');
                } catch (\Exception $e) {
                    Log::error('Dashboard Date Range Filter Error: Failed to parse date string.', [
                        'input_daterange' => $request->input('daterange'),
                        'exception_message' => $e->getMessage(),
                    ]);
                    // Fallback to default range if parsing fails
                    $startDate = Carbon::now()->subDays(30)->startOfDay();
                    $endDate = Carbon::now()->endOfDay();
                    $daterangeFilter = $startDate->format('Y/m/d') . ' - ' . $endDate->format('Y/m/d');
                }
            } else {
                Log::warning('Dashboard Date Range Filter Warning: Daterange input malformed.', [
                    'input_daterange' => $request->input('daterange'),
                ]);
                // Fallback to default range if malformed
                $startDate = Carbon::now()->subDays(30)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $daterangeFilter = $startDate->format('Y/m/d') . ' - ' . $endDate->format('Y/m/d');
            }
        } else {
            $startDate = Carbon::now()->subDays(30)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
            $daterangeFilter = $startDate->format('Y/m/d') . ' - ' . $endDate->format('Y/m/d');
        }

        $transactionsForChartsQuery = Transaction::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('channel_type'),
            DB::raw('SUM(amount) as total_amount')
        )
            ->where('status', 'success')
            ->whereIn('outlet_id', $outletIds);
        if ($startDate && $endDate) {
            $transactionsForChartsQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $transactionsForCharts = $transactionsForChartsQuery
            ->groupBy(DB::raw('DATE(created_at)'), 'channel_type')
            ->orderBy(DB::raw('DATE(created_at)'))
            ->get();

        $dates = $transactionsForCharts->pluck('date')->unique()->sort()->values();
        $categories = $dates->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))->toArray();

        $channelTypes = ['drop_off', 'self_service'];

        $seriesData = [];
        foreach ($channelTypes as $type) {
            $seriesData[$type] = $dates->map(
                fn($date) =>
                (int) optional(
                    $transactionsForCharts->first(fn($t) => $t->date == $date && $t->channel_type == $type)
                )->total_amount
            )->toArray();
        }

        // --- 4. Data for Chart 2: Donut Chart (Transaction Channel Type Summary) ---
        $transactionSummaryQuery = Transaction::select(
            DB::raw('channel_type'),
            DB::raw('COUNT(*) as total')
        )
            ->where('status', 'success')
            ->whereIn('outlet_id', $outletIds);

        if ($startDate && $endDate) {
            $transactionSummaryQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        $transactionSummary = $transactionSummaryQuery->groupBy('channel_type')->get();

        $donutLabels = [];
        $donutData = [];
        $expectedChannels = ['drop_off', 'self_service'];

        foreach ($expectedChannels as $channel) {
            $count = $transactionSummary->firstWhere('channel_type', $channel)->total ?? 0;
            $donutLabels[] = ucwords(str_replace('_', ' ', $channel));
            $donutData[] = $count;
        }

        // --- 5. Total Transaksi Keseluruhan ---
        $totalTransactionsQuery = Transaction::query()
            ->where('status', 'success')
            ->whereIn('outlet_id', $outletIds);
        if ($startDate && $endDate) {
            $totalTransactionsQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        $totalTransactions = $totalTransactionsQuery->count();

        // --- 6. Total Transaksi Berdasarkan Channel Type ---
        // Ini adalah pengganti totalMemberTransactions, totalManualTransactions, totalQrisTransactions
        $totalDropOffTransactions = $transactionSummary->firstWhere('channel_type', 'drop_off')->total ?? 0;
        $totalSelfServiceTransactions = $transactionSummary->firstWhere('channel_type', 'self_service')->total ?? 0;

        // --- 7. Data for Chart 3: Donut Chart (Service Comparison: Washer vs Dryer) ---
        // Mengambil service_type dari model ServiceType, lalu dikonversi ke snake_case
        $serviceTypesDbNames = ServiceType::pluck('name')->map(fn($name) => Str::snake($name))->toArray();

        $serviceComparisonQuery = DeviceTransaction::select('device_transactions.service_type', DB::raw('COUNT(*) as total'))
            ->join('transactions', 'device_transactions.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'success')
            ->whereIn('transactions.outlet_id', $outletIds)
            ->whereIn('device_transactions.service_type', $serviceTypesDbNames); // Filter berdasarkan service_type yang ada

        if ($startDate && $endDate) {
            $serviceComparisonQuery->whereBetween('transactions.created_at', [$startDate, $endDate]);
        }

        $serviceComparison = $serviceComparisonQuery->groupBy('device_transactions.service_type')->get();

        $serviceLabels = $serviceComparison->pluck('service_type')->toArray();
        $serviceData = $serviceComparison->pluck('total')->map(fn($value) => (int) $value)->toArray();

        $totalServiceCounts = [];
        foreach ($serviceTypesDbNames as $type) {
            $totalServiceCounts[$type] = $serviceComparison->firstWhere('service_type', $type)->total ?? 0;
        }

        // --- 8. NEW DATA FOR REVISION: Daily Total Transactions (Bar Chart) ---
        $dailyTotalTransactionsQuery = Transaction::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total_transactions')
        )
            ->where('status', 'success')
            ->whereIn('outlet_id', $outletIds);

        if ($startDate && $endDate) {
            $dailyTotalTransactionsQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $dailyTotalTransactions = $dailyTotalTransactionsQuery->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'))
            ->get();

        $dailyTransactionCategories = $dailyTotalTransactions->pluck('date')->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))->toArray();
        $dailyTransactionSeriesData = $dailyTotalTransactions->pluck('total_transactions')->toArray();

        // --- 9. NEW DATA FOR REVISION: Hourly Transaction Trend (Bar Chart, bukan Donut) ---
        $hourlyTransactionTrendQuery = Transaction::select(
            DB::raw('HOUR(created_at) as hour'),
            DB::raw('COUNT(*) as total_transactions')
        )
            ->where('status', 'success')
            ->whereIn('outlet_id', $outletIds);

        if ($startDate && $endDate) {
            $hourlyTransactionTrendQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $hourlyTransactionTrend = $hourlyTransactionTrendQuery->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour')
            ->get();

        $hourlyLabels = [];
        foreach ($hourlyTransactionTrend as $item) {
            // Format jam agar selalu 2 digit, misal '09:00'
            $hourlyLabels[] = sprintf('%02d:00', $item->hour);
        }
        $hourlyData = $hourlyTransactionTrend->pluck('total_transactions')->map(fn($value) => (int) $value)->toArray();

        // --- 10. Multi-Device Chart & Trend Device Donut ---
        // Implementasi ini harus Anda sesuaikan dengan cara Anda ingin mendapatkan data perangkat.
        // Ini adalah placeholder.
        $dataMultiDeviceChart = $this->dataMultiDeviceChart($startDate, $endDate, $outletIds);
        $trendDeviceDonut = $this->trendDeviceDonut($startDate, $endDate, $outletIds);

        // --- 11. Statistik Umum (Total Members, Devices, Outlets, Owners) ---

        // --- 12. Kirim Data ke View ---
        return view('admin.dashboard', compact(
            'daterangeFilter',
            'categories',
            'seriesData',
            'donutLabels',
            'donutData',
            'serviceLabels',
            'serviceData',
            'totalTransactions',
            'totalDropOffTransactions', // Variabel baru
            'totalSelfServiceTransactions', // Variabel baru
            'serviceTypesDbNames',
            'totalServiceCounts',
            'dailyTransactionCategories',
            'dailyTransactionSeriesData',
            'hourlyLabels',
            'hourlyData',
            'dataMultiDeviceChart',
            'trendDeviceDonut',
        ));
    }

    private function trendDeviceDonut(?Carbon $startDate, ?Carbon $endDate): array
    {
        // Untuk admin, kita ambil semua outlet
        $outletIds = getData()->outlets->pluck('id')->toArray();

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
            // ->limit(10) // Ambil hanya 10 teratas
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

    /**
     * Get data for the Multi-Device Chart (Daily Transaction Trend for Top 10 Devices).
     *
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return array
     */
    private function dataMultiDeviceChart(?Carbon $startDate, ?Carbon $endDate): array
    {
        // Untuk admin, kita ambil semua outlet
        $outletIds = getData()->outlets->pluck('id')->toArray();

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
            // ->limit(10) // Ambil hanya 10 teratas
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



    public function device_list()
    {
        $feature = getData();
        if (!$feature->can('partner.device_list')) {
            abort(403, 'Anda tidak memiliki izin.');
        }
        $devices = getData()->devices->get();
        $outlets = getData()->outlets->get();

        return view('partner.devices.device_list', compact('devices', 'outlets'));
    }


    public function updateDeviceServicePrices(Request $request, Device $device)
    {
        $feature = getData();
        if (!$feature->can('partner.device.service_types.update')) {
            abort(403, 'Anda tidak memiliki izin.');
        }
        // $validated = $request->validate([
        //     'prices' => 'required|array',
        //     'prices.*' => 'required|min:0',
        // ]);

        DB::beginTransaction();
        try {
            foreach ($request->prices as $serviceTypeId => $price) {
                DB::table('device_service_type')->updateOrInsert(
                    [
                        'device_id' => $device->id,
                        'service_type_id' => $serviceTypeId,
                    ],
                    [
                        'price' => $price,
                    ]
                );
            }

            DB::commit();
            return redirect()->back()->with('success', 'Harga layanan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack(); // batalkan semua kalau ada error

            // Lempar balik atau handle sesuai kebutuhan
            return back()->withErrors(['error' => 'Gagal menyimpan harga: ' . $e->getMessage()]);
        }
    }

    public function updateDeviceDetails(Request $request, Device $device)
    {
        $feature = getData();
        if (!$feature->can('partner.device.update')) {
            abort(403, 'Anda tidak memiliki izin.');
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'outlet_id' => 'required|exists:outlets,id',
        ]);

        // try {
        $device->update([
            'name' => $request->input('name'),
            'outlet_id' => $request->input('outlet_id'),
        ]);

        return redirect()->back()->with('success', 'Device details updated successfully!');
        // } catch (\Exception $e) {
        //     Log::error("Error updating device details for device ID {$device->id}: " . $e->getMessage());
        //     return redirect()->back()->with('error', 'Failed to update device details. Please try again.');
        // }
    }

    public function storeDevice(Request $request)
    {
        $feature = getData();
        if (!$feature->can('partner.device.store')) {
            abort(403, 'Anda tidak memiliki izin.');
        }


        try {
            DB::transaction(function () use ($request) {
                $data = [
                    'name'      => $request->name,
                    'outlet_id' => $request->outlet_id,
                    'device_status' => 'off', // Default status for new devices
                ];

                $device = Device::create($data);
                $device->code = Device::generateUniqueCode($device->id);
                $device->save();
            });

            return redirect()->route('partner.device.list')
                ->with('success', 'Device berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('Error storing new device for partner: ' . $e->getMessage());
            return redirect()->route('partner.device.list')
                ->with('error', 'Terjadi kesalahan saat menambahkan device: ' . $e->getMessage());
        }
    }

    public function destroyDevice(Device $device)
    {
        $feature = getData();
        if (!$feature->can('partner.device.destroy')) {
            abort(403, 'Anda tidak memiliki izin.');
        }

        try {
            DB::transaction(function () use ($device) {
                $device->delete();
            });

            return response()->json(['status' => 'success', 'message' => 'Device berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Error deleting device for partner: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus device: ' . $e->getMessage()], 500);
        }
    }

       public function device_edit(Device $device)
    {
        $outlets = Outlet::with('owner')->get();
        // Mengambil menu settings (sudah otomatis array jika di-cast di Model)
        $menuSettings = $device->menu_settings;
$serviceTypes = ServiceType::all();
    // dd($device);

        return view('partner.devices.device_edit', compact('device','serviceTypes', 'outlets', 'menuSettings'));
    }
public function device_update(Request $request, Device $device)
{
    $request->validate([
        'name'            => 'required|string|max:255',

        'outlet_id'       => 'required|exists:outlets,id',
        'service_type_id' => 'required|exists:service_types,id',
        'menu'            => 'required|array|min:4|max:4',
    ]);

    try {
        $options = [];
        for ($i = 0; $i < 4; $i++) {
            $item = $request->menu[$i] ?? null;

            // Slot dianggap aktif jika input 'type' dikirim oleh JavaScript
            $isActive = is_array($item) && !empty($item['type']);

            $options[$i] = [
                'name'        => $isActive ? ($item['name'] ?? 'Menu ' . ($i + 1)) : '',
                'price'       => $isActive ? (float)($item['price'] ?? 0) : 0,
                'duration'    => $isActive ? (int)($item['duration'] ?? 0) : 0,
                'description' => $isActive ? ($item['description'] ?? '') : '',
                'type'        => $isActive ? $item['type'] : 'disabled',
                'active'      => $isActive,
            ];
        }

        $device->update([
            'name'            => $request->name,
            'code'            => $request->code,
            'outlet_id'       => $request->outlet_id,
            'service_type_id' => $request->service_type_id,
            'option_1'        => $options[0],
            'option_2'        => $options[1],
            'option_3'        => $options[2],
            'option_4'        => $options[3],
        ]);

        return redirect()->route('partner.device.list')
            ->with('success', 'Device ' . $device->code . ' berhasil diperbarui');
    } catch (\Exception $e) {
        return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}
}
