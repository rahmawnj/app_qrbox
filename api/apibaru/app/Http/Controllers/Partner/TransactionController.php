<?php

namespace App\Http\Controllers\Partner;

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
        $filterableQuery = Transaction::query()
            ->with(['owner.user', 'outlet', 'dropOffTransaction.service', 'payments', 'selfServiceTransaction', 'member.user']);
        $accessibleOutletIds = getData()->outlets->pluck('id')->toArray();
        $filterableQuery->whereIn('outlet_id', $accessibleOutletIds);

        if ($request->has('status') && $request->status != '') {
            $filterableQuery->where('status', $request->status);
        }

        if ($request->has('type') && $request->type != '') {
            $filterableQuery->where('channel_type', $request->type);
        }

        if ($request->has('is_member') && $request->is_member != '') {
            if ($request->is_member === 'yes') {
                $filterableQuery->whereNotNull('member_id');
            } elseif ($request->is_member === 'no') {
                $filterableQuery->whereNull('member_id');
            }
        }

        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $filterableQuery->where(function ($query) use ($searchTerm) {
                $query->where('order_id', 'like', '%' . $searchTerm . '%')
                    ->orWhere('amount', 'like', '%' . $searchTerm . '%')
                    // ✅ Cari berdasarkan nama outlet
                    ->orWhereHas('outlet', function ($q) use ($searchTerm) {
                        $q->where('outlet_name', 'like', '%' . $searchTerm . '%');
                    })
                    // ✅ Cari berdasarkan nama owner (brand_name)
                    ->orWhereHas('outlet.owner', function ($q) use ($searchTerm) {
                        $q->where('brand_name', 'like', '%' . $searchTerm . '%');
                    })

                    ->orWhereHas('member.user', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', '%' . $searchTerm . '%');
                    });
            });
        }


        $shouldPaginate = true;

        if ($request->has('daterange') && $request->daterange != '') {
            list($startDate, $endDate) = explode(' - ', $request->daterange);
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();
            $filterableQuery->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            $today = Carbon::now();
            $startOfToday = $today->copy()->startOfDay();
            $endOfToday = $today->copy()->endOfDay();
            $filterableQuery->whereBetween('created_at', [$startOfToday, $endOfToday]);
            $shouldPaginate = false;
        }

        // Ambil jumlah total transaksi
        $totalTransactionsCount = (clone $filterableQuery)->count();

        if ($shouldPaginate) {
            $transactions = $filterableQuery->orderBy('created_at', 'desc')->paginate(200);
        } else {
            $transactions = $filterableQuery->orderBy('created_at', 'desc')->get();
        }

        return view('partner.transactions.index', compact('transactions', 'totalTransactionsCount'));
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

   $this->applyFilters($request, $baseQuery, function ($q) use ($request) {
    $searchTerm = $request->search;

    $q->orWhereHas('member.user', function ($query) use ($searchTerm) {
        $query->where('name', 'like', '%' . $searchTerm . '%');
    });
});

    $totalTransactionsCount = (clone $baseQuery)->count();

    $totalMembersWithTransactions = (clone $baseQuery)->distinct('member_id')->count('member_id');

    $totalDevicesInvolved = (clone $baseQuery)->withCount('deviceTransactions')->get()->sum('device_transactions_count');

    $totalDevicesActivated = (clone $baseQuery)->whereHas('deviceTransactions', function ($query) {
        $query->where('status', false);
    })->count();

    $transactions = $baseQuery->paginate(200);

    return view('partner.transactions.self-service-member', compact(
        'transactions',
        'totalTransactionsCount',
        'totalMembersWithTransactions',
        'totalDevicesInvolved',
        'totalDevicesActivated'
    ));
}

public function self_service_non_member(Request $request)
{
    // Gunakan model Transaction sebagai basis query
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

    // Terapkan filter outlet yang dapat diakses
    $accessibleOutletIds = getData()->outlets->pluck('id')->toArray();
    $baseQuery->whereIn('outlet_id', $accessibleOutletIds);

    // Terapkan filter tanggal dan pencarian umum
    $this->applyFilters($request, $baseQuery);

    // Clone query untuk menghitung ringkasan
    $clonedForSummary = clone $baseQuery;

    // Hitung total transaksi
    $totalTransactionsCount = $clonedForSummary->count();

    // Hitung total pendapatan (langsung dari kolom amount)
    $totalRevenue = $clonedForSummary->sum('amount');

    // Hitung total perangkat yang terlibat
    $totalDevicesInvolved = $clonedForSummary->withCount('deviceTransactions')
        ->get()
        ->sum('device_transactions_count');

    // Hitung total perangkat yang 'activated' (asumsi status true)
    $totalActivatedDevices = $clonedForSummary->whereHas('deviceTransactions', function ($query) {
        $query->where('status', true);
    })->count();

    // Paginate hasil query
    $transactions = $baseQuery->paginate(200);

    // Tampilkan view
    return view('partner.transactions.self-service-non-member', compact(
        'transactions',
        'totalTransactionsCount',
        'totalRevenue',
        'totalActivatedDevices',
        'totalDevicesInvolved'
    ));
}

public function drop_off_member(Request $request)
{
    $accessibleOutletIds = getData()->outlets->pluck('id')->toArray();

    // Base query utama
    $baseQuery = Transaction::with([
        'owner.user',
        'outlet',
        'dropOffTransaction',
        'member.user',
        'deviceTransactions.device',
        'payments'
    ])
    ->where('channel_type', 'drop_off')
    ->whereIn('outlet_id', $accessibleOutletIds)
    ->whereHas('payments', function ($query) {
        $query->where('status', 'success')
            ->where('payment_method', 'member');
    });

    // Apply filters dari request (misalnya tanggal, outlet, dll)
      $this->applyFilters($request, $baseQuery, function ($q) use ($request) {
    $searchTerm = $request->search;

    $q->orWhereHas('member.user', function ($query) use ($searchTerm) {
        $query->where('name', 'like', '%' . $searchTerm . '%');
    });
});
    // Ambil ID transaksi yang sudah terfilter
    $filteredTransactionIds = (clone $baseQuery)->pluck('id');

    // Total semua transaksi
    $totalTransactionsCount = count($filteredTransactionIds);

    // Total member unik yang melakukan transaksi
    $totalMembersWithTransactions = Transaction::whereIn('id', $filteredTransactionIds)
        ->distinct('member_id')
        ->count('member_id');

    // Total perangkat yang terlibat
    $totalDevicesInvolved = Transaction::whereIn('id', $filteredTransactionIds)
        ->withCount('deviceTransactions')
        ->get()
        ->sum('device_transactions_count');

    // Total transaksi yang punya perangkat status false
    $totalTransactionsWithDeactivatedDevices = Transaction::whereIn('id', $filteredTransactionIds)
        ->whereHas('deviceTransactions', function ($query) {
            $query->where('status', false);
        })->count();

    // Ambil data paginated
    $transactions = $baseQuery->orderBy('created_at', 'desc')->paginate(200);

    return view('partner.transactions.drop-off-member', compact(
        'transactions',
        'totalTransactionsCount',
        'totalMembersWithTransactions',
        'totalDevicesInvolved',
        'totalTransactionsWithDeactivatedDevices'
    ));
}


    public function drop_off_non_member(Request $request)
    {
        $accessibleOutletIds = getData()->outlets->pluck('id')->toArray();

        $baseQuery = Transaction::with([
            'owner.user',
            'outlet',
            'dropOffTransaction',
            'deviceTransactions.device',
            'payments'
        ])
            ->where('channel_type', 'drop_off')
            ->whereIn('outlet_id', $accessibleOutletIds)
            ->whereHas('payments', function ($query) {
                $query->where('status', 'success')
                    ->where('payment_method', 'non_member');
            });

        $this->applyFilters($request, $baseQuery);
        if ($request->filled('payment_method_filter')) {
            $paymentMethod = $request->payment_method_filter;

            $baseQuery->whereHas('dropOffTransaction', function ($query) use ($paymentMethod) {
                $query->where('payment_type', $paymentMethod);
            });
        }

        $filteredQuery = (clone $baseQuery);

        $totalTransactionsCount = (clone $filteredQuery)->count();

        $filteredTransactionIds = (clone $filteredQuery)->pluck('id');
        $joinedQuery = DropOffTransaction::whereIn('transaction_id', $filteredTransactionIds);

        // Hitung total pendapatan cash
        $totalCashRevenue = (clone $joinedQuery)
            ->where('payment_type', 'cash')
            ->sum('service_price');

        $totalNonCashRevenue = (clone $joinedQuery)
            ->where('payment_type', 'non_cash')
            ->sum('service_price');

        $totalDevicesInvolved = (clone $filteredQuery)
            ->withCount('deviceTransactions')
            ->get()
            ->sum('device_transactions_count');

        // Hitung total perangkat aktif
        $totalActiveDevices = (clone $filteredQuery)
            ->whereHas('deviceTransactions', function ($query) {
                $query->where('status', true);
            })
            ->count();

        // Ambil data paginated
        $transactions = $baseQuery->orderBy('created_at', 'desc')->paginate(200);

        return view('partner.transactions.drop-off-non-member', compact(
            'transactions',
            'totalTransactionsCount',
            'totalCashRevenue',
            'totalNonCashRevenue',
            'totalDevicesInvolved',
            'totalActiveDevices'
        ));
    }

    private function applyFilters(Request $request, $query, Closure $extraSearch = null)
    {
        // Filter tanggal
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

        // Search umum
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
                    $extraSearch($q); // Tambahkan kondisi tambahan seperti filter nama member
                }
            });
        }

        return $query;
    }


    // public function qris_transaction(Request $request)
    // {
    //     $query = Transaction::with([
    //         'owner.user',
    //         'outlet',
    //         'qrisTransaction',
    //         'deviceTransactions.device'
    //     ])
    //         ->where('type', 'qris')
    //         ->where('status', 'success') // Filter default untuk status success
    //         ->orderBy('created_at', 'desc');
    //     $accessibleOutletIds = getData()->outlets->pluck('id')->toArray();
    //     $query->whereIn('outlet_id', $accessibleOutletIds);


    //     // Apply filters (daterange and search) to the query
    //     if ($request->filled('daterange')) {
    //         $dates = explode(' - ', $request->daterange);
    //         if (count($dates) === 2) {
    //             $startDate = trim($dates[0]);
    //             $endDate = trim($dates[1]);

    //             $query->whereDate('created_at', '>=', $startDate)
    //                 ->whereDate('created_at', '<=', $endDate);
    //         }
    //     }

    //     if ($request->filled('search')) {
    //         $search = $request->search;
    //         $query->where(function ($q) use ($search) {
    //             $q->where('order_id', 'like', '%' . $search . '%')
    //                 ->orWhere('amount', 'like', '%' . $search . '%')
    //                 ->orWhereHas('owner', function ($q2) use ($search) {
    //                     $q2->where('brand_name', 'like', '%' . $search . '%')
    //                         ->orWhereHas('user', function ($q3) use ($search) {
    //                             $q3->where('name', 'like', '%' . $search . '%');
    //                         });
    //                 })
    //                 ->orWhereHas('outlet', function ($q4) use ($search) {
    //                     $q4->where('outlet_name', 'like', '%' . $search . '%')
    //                         ->orWhere('code', 'like', '%' . $search . '%')
    //                         ->orWhere('address', 'like', '%' . $search . '%');
    //                 });
    //         });
    //     }

    //     // --- Perubahan di sini: Hitung total sebelum pagination ---
    //     $totalFilteredTransactionsAmount = $query->sum('amount');
    //     $totalFilteredTransactionsCount = $query->count();
    //     // --------------------------------------------------------

    
    //     $transactions = $query->paginate(200);

    //     $transactionsData = getData()->transactions->where('type', 'qris');
    //     $totalTransactionsCountOverall = $transactionsData->count();
    //     $totalTransactionsAmountOverall = $transactionsData->where('status', 'success')->sum('amount'); // Total semua tanpa filter daterange/search
    //     $completedTransactionsCount = $transactionsData->where('status', 'success')->count();

    //     $activatedDeviceTransactionsCount = DeviceTransaction::whereNull('activated_at')
    //         ->whereHas('transaction', function ($q) {
    //             $q->where('type', 'qris');
    //         })->whereIn('transaction_id', $transactions->pluck('id'))
    //         ->count();

    //     return view('partner.transactions.qris', compact(
    //         'transactions',
    //         'totalTransactionsCountOverall', // Ini untuk card summary, total keseluruhan
    //         'totalTransactionsAmountOverall', // Ini untuk card summary, total keseluruhan
    //         'completedTransactionsCount',
    //         'activatedDeviceTransactionsCount',
    //         'totalFilteredTransactionsCount', // Total yang difilter (untuk footer)
    //         'totalFilteredTransactionsAmount' // Total jumlah yang difilter (untuk footer)
    //     ));
    // }
}
