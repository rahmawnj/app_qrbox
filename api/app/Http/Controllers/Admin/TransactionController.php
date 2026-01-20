<?php

namespace App\Http\Controllers\admin;

use Closure;
use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\DeviceTransaction;
use App\Models\DropOffTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\QrisTransactionDetail;
use App\Models\SelfServiceTransaction;

class TransactionController extends Controller
{
public function index(Request $request)
{
    $access = getData();
    $isAdmin = auth('admin_config')->check();

    // 1. Tentukan Accessible Owner IDs
    if ($isAdmin) {
        $accessibleOwnerIds = \App\Models\Owner::pluck('id')->toArray();
    } else {
        $brand = $access->getBrand();
        $accessibleOwnerIds = $brand ? [$brand->id] : [];
    }

    // 2. Inisialisasi Query Utama
    $baseQuery = \App\Models\Transaction::with(['owner', 'outlet'])
        ->whereIn('owner_id', $accessibleOwnerIds);

    // 3. Filter Multi-select Owner (Admin Only)
    if ($isAdmin && $request->filled('owner_ids')) {
        $baseQuery->whereIn('owner_id', $request->owner_ids);
    }

    // 4. Filter Search
    // if ($request->filled('search')) {
    //     $searchTerm = $request->search;
    //     $baseQuery->where(function ($q) use ($searchTerm) {
    //         $q->where('order_id', 'like', "%$searchTerm%")
    //           ->orWhereHas('owner', fn($sq) => $sq->where('brand_name', 'like', "%$searchTerm%"));
    //     });
    // }

    // 5. Filter Status (DEFAULT: success jika tidak ada input)
    // Jika request has 'status' (bisa jadi kosong/all), tapi jika baru buka (null), set success.
    $statusFilter = $request->get('status');
    if (!$request->has('status')) {
        $statusFilter = 'success'; // Default saat pertama kali buka
    }

    if (!empty($statusFilter)) {
        $baseQuery->where('status', $statusFilter);
    }

    // 6. Filter Type
    if ($request->filled('type')) {
        $baseQuery->where('type', $request->type);
    }

    // 7. Filter Tanggal
    $daterange = $request->input('daterange');
    try {
        if ($daterange) {
            $separator = str_contains($daterange, ' to ') ? ' to ' : ' - ';
            $dateRange = explode($separator, $daterange);
            $startDate = \Carbon\Carbon::parse(trim($dateRange[0]))->startOfDay();
            $endDate = \Carbon\Carbon::parse(trim($dateRange[1]))->endOfDay();
        } else {
            $startDate = \Carbon\Carbon::now()->subDays(30)->startOfDay();
            $endDate = \Carbon\Carbon::now()->endOfDay();
        }
    } catch (\Exception $e) {
        $startDate = \Carbon\Carbon::now()->subDays(30)->startOfDay();
        $endDate = \Carbon\Carbon::now()->endOfDay();
    }

    $baseQuery->whereBetween('created_at', [$startDate, $endDate]);
    $daterangeValue = $startDate->format('Y/m/d') . ' - ' . $endDate->format('Y/m/d');

    // --- HITUNG STATISTIK (Global Berdasarkan Filter) ---
    // Statistik biasanya selalu menghitung yang success saja
    $statsBase = (clone $baseQuery)->where('status', 'success');
    $totalTransactionsCount = $statsBase->count();
    $totalIncome = (clone $statsBase)->where('type', 'payment')->sum('amount');
    $totalWithdrawal = (clone $statsBase)->where('type', 'withdrawal')->sum('amount');
    $netBalance = $totalIncome - $totalWithdrawal;

    // --- PAGINASI ---
    $transactions = $baseQuery->orderBy('created_at', 'desc')->paginate(100);

    // --- HITUNG FOOTER (Per Halaman) ---
    $pageIncome = $transactions->filter(fn($t) => $t->type === 'payment' && $t->status === 'success')->sum('amount');
    $pageWithdrawal = $transactions->filter(fn($t) => $t->type === 'withdrawal' && $t->status === 'success')->sum('amount');

    return view('admin.transactions.index', compact(
        'transactions', 'totalTransactionsCount', 'totalIncome',
        'totalWithdrawal', 'netBalance', 'pageIncome', 'pageWithdrawal',
        'daterangeValue', 'statusFilter'
    ));
}
    public function self_service_member(Request $request)
    {
        $baseQuery = Transaction::with([
            'owner.user',
            'outlet',
            'selfServiceTransaction',
            'member.user',
            'deviceTransactions.device',
            'payments'
        ])
        ->where('channel_type', 'self_service')
        ->whereHas('payments', function ($query) {
            $query->where('status', 'success')
                  ->where('payment_method', 'member');
        })
        ->orderBy('created_at', 'desc');

        $accessibleOutletIds = getData()->outlets->pluck('id')->toArray();
        $baseQuery->whereIn('outlet_id', $accessibleOutletIds);

        // Menerapkan semua filter ke baseQuery
        $this->applyFilters($request, $baseQuery, function ($q) use ($request) {
            $searchTerm = $request->search;
            $q->orWhereHas('member.user', function ($query) use ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%');
            });
        });

        // Kloning query yang sudah difilter untuk semua perhitungan
        $totalTransactionsCount = (clone $baseQuery)->count();
        $totalMembersWithTransactions = (clone $baseQuery)->distinct('member_id')->count('member_id');

        // Perbaikan untuk total perangkat yang terlibat
        $totalDevicesInvolved = (clone $baseQuery)
            ->withCount('deviceTransactions')
            ->get()
            ->sum('device_transactions_count');

        // Perbaikan untuk total perangkat yang diaktifkan
        $totalDevicesActivated = (clone $baseQuery)
            ->whereHas('deviceTransactions', function ($query) {
                $query->where('status', false);
            })
            ->count();

        // Ambil data transaksi dengan paginasi dari query yang sudah difilter
        $transactions = $baseQuery->get();
        // dd($transactions);

        return view('admin.transactions.self-service-member', compact(
            'transactions',
            'totalTransactionsCount',
            'totalMembersWithTransactions',
            'totalDevicesInvolved',
            'totalDevicesActivated'
        ));
    }

    public function self_service_non_member(Request $request)
    {
        $baseQuery = Transaction::with([
            'owner.user',
            'outlet',
            'selfServiceTransaction',
            'deviceTransactions.device',
            'payments'
        ])
        ->where('channel_type', 'self_service')
        ->whereNull('member_id')
        ->where('status', 'success')
        ->whereHas('payments', function ($paymentQuery) {
            $paymentQuery->where('payment_method', 'non_member');
        })
        ->orderBy('created_at', 'desc');

        $accessibleOutletIds = getData()->outlets->pluck('id')->toArray();
        $baseQuery->whereIn('outlet_id', $accessibleOutletIds);

        // Menerapkan semua filter ke baseQuery
        $this->applyFilters($request, $baseQuery);

        // Kloning query yang sudah difilter untuk semua perhitungan
        $totalTransactionsCount = (clone $baseQuery)->count();

        $totalRevenue = (clone $baseQuery)->sum('amount');

        $totalDevicesInvolved = (clone $baseQuery)
            ->withCount('deviceTransactions')
            ->get()
            ->sum('device_transactions_count');

        $activeDevicesCount = (clone $baseQuery)
            ->whereHas('deviceTransactions', function ($query) {
                $query->where('status', false); // asumsikan false berarti aktif
            })
            ->count();

        $transactions = $baseQuery->paginate(200);

        return view('admin.transactions.self-service-non-member', compact(
            'transactions',
            'totalTransactionsCount',
            'totalRevenue',
            'activeDevicesCount',
            'totalDevicesInvolved'
        ));
    }

    public function drop_off_member(Request $request)
    {
        $baseQuery = Transaction::with([
            'owner.user',
            'outlet',
            'dropOffTransaction',
            'member.user',
            'deviceTransactions.device',
            'payments'
        ])
        ->where('channel_type', 'drop_off')
        ->whereHas('payments', function ($query) {
            $query->where('status', 'success')
                  ->where('payment_method', 'member');
        });

        $accessibleOutletIds = getData()->outlets->pluck('id')->toArray();
        $baseQuery->whereIn('outlet_id', $accessibleOutletIds);

        // Menerapkan semua filter ke baseQuery
        $this->applyFilters($request, $baseQuery, function ($q) use ($request) {
            $searchTerm = $request->search;
            $q->orWhereHas('member.user', function ($query) use ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%');
            });
        });

        // Kloning query yang sudah difilter untuk semua perhitungan
        $totalTransactionsCount = (clone $baseQuery)->count();
        $totalMembersWithTransactions = (clone $baseQuery)->distinct('member_id')->count('member_id');

        $totalDevicesInvolved = (clone $baseQuery)
            ->withCount('deviceTransactions')
            ->get()
            ->sum('device_transactions_count');

        $totalTransactionsWithDeactivatedDevices = (clone $baseQuery)
            ->whereHas('deviceTransactions', function ($query) {
                $query->where('status', false);
            })->count();

        // Ambil data paginated
        $transactions = $baseQuery->orderBy('created_at', 'desc')->paginate(200);

        return view('admin.transactions.drop-off-member', compact(
            'transactions',
            'totalTransactionsCount',
            'totalMembersWithTransactions',
            'totalDevicesInvolved',
            'totalTransactionsWithDeactivatedDevices'
        ));
    }

    public function drop_off_non_member(Request $request)
    {
        $baseQuery = Transaction::with([
            'owner.user',
            'outlet',
            'dropOffTransaction',
            'deviceTransactions.device',
            'payments'
        ])
        ->where('channel_type', 'drop_off')
        ->whereHas('payments', function ($query) {
            $query->where('status', 'success')
                  ->where('payment_method', 'non_member');
        });

        $accessibleOutletIds = getData()->outlets->pluck('id')->toArray();
        $baseQuery->whereIn('outlet_id', $accessibleOutletIds);

        // Menerapkan semua filter ke baseQuery
        $this->applyFilters($request, $baseQuery);

        if ($request->filled('payment_method_filter')) {
            $paymentMethod = $request->payment_method_filter;
            $baseQuery->whereHas('dropOffTransaction', function ($query) use ($paymentMethod) {
                $query->where('payment_type', $paymentMethod);
            });
        }

        // Kloning query yang sudah difilter untuk semua perhitungan
        $totalTransactionsCount = (clone $baseQuery)->count();

        $totalCashRevenue = (clone $baseQuery)
            ->whereHas('dropOffTransaction', function ($query) {
                $query->where('payment_type', 'cash');
            })
            ->sum('amount');

        $totalNonCashRevenue = (clone $baseQuery)
            ->whereHas('dropOffTransaction', function ($query) {
                $query->where('payment_type', 'non_cash');
            })
            ->sum('amount');

        $totalDevicesInvolved = (clone $baseQuery)
            ->withCount('deviceTransactions')
            ->get()
            ->sum('device_transactions_count');

        $totalActiveDevices = (clone $baseQuery)
            ->whereHas('deviceTransactions', function ($query) {
                $query->where('status', false);
            })
            ->count();

        // Ambil data paginated
        $transactions = $baseQuery->orderBy('created_at', 'desc')->paginate(200);

        return view('admin.transactions.drop-off-non-member', compact(
            'transactions',
            'totalTransactionsCount',
            'totalCashRevenue',
            'totalNonCashRevenue',
            'totalDevicesInvolved',
            'totalActiveDevices'
        ));
    }

    // Fungsi applyFilters ini sudah benar dan tidak perlu diubah
    private function applyFilters(Request $request, $query, Closure $extraSearch = null)
    {
        $dateColumn = $query->getModel()->getCreatedAtColumn();
        if ($request->filled('daterange')) {
            [$startDate, $endDate] = explode(' - ', $request->daterange);
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();
        } else {
            $startDate = now()->startOfDay();
            $endDate = now()->endOfDay();
        }
        $query->whereBetween($dateColumn, [$startDate, $endDate]);

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm, $extraSearch) {
                $q->where('order_id', 'like', "%{$searchTerm}%");
                if (is_numeric($searchTerm)) {
                    $q->orWhere('amount', $searchTerm);
                }
                $q->orWhereHas('outlet', fn($q2) =>
                    $q2->where('outlet_name', 'like', "%{$searchTerm}%"));
                $q->orWhereHas('owner', fn($q3) =>
                    $q3->where('brand_name', 'like', "%{$searchTerm}%"));
                if ($extraSearch) {
                    $extraSearch($q);
                }
            });
        }

        return $query;
    }
}
