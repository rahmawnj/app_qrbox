<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Payment;
use App\Models\TopupHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\QrisTransactionDetail;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminPaymentController extends Controller
{
  public function payments_history(Request $request)
{
    $access = getData();
    $isAdmin = auth('admin_config')->check();

    // 1. Tentukan Accessible Owner IDs (Sama seperti logika Transaksi)
    if ($isAdmin) {
        $accessibleOwnerIds = \App\Models\Owner::pluck('id')->toArray();
    } else {
        $brand = $access->getBrand();
        $accessibleOwnerIds = $brand ? [$brand->id] : [];
    }

    // 2. Query Dasar
    $baseQuery = \App\Models\Payment::with(['transaction', 'outlet', 'owner'])
        ->whereIn('owner_id', $accessibleOwnerIds);

    // 3. Filter Admin (Pilih Owner)
    if ($isAdmin && $request->filled('owner_ids')) {
        $baseQuery->whereIn('owner_id', $request->owner_ids);
    }

    // 4. Filter Search
    if ($request->filled('search')) {
        $searchTerm = $request->search;
        $baseQuery->where(function ($q) use ($searchTerm) {
            $q->where('amount', 'like', "%$searchTerm%")
              ->orWhereHas('transaction', fn($sq) => $sq->where('order_id', 'like', "%$searchTerm%"))
              ->orWhereHas('owner', fn($sq) => $sq->where('brand_name', 'like', "%$searchTerm%"));
        });
    }

    // 5. Filter Tanggal (Default 30 hari terakhir)
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

    // --- HITUNG STATISTIK (Berdasarkan Filter) ---
    $statsQuery = clone $baseQuery;
    $totalPaymentsCount = $statsQuery->count();
    $totalPaymentsAmount = $statsQuery->sum('amount');
    $totalServiceFees = $statsQuery->sum('service_fee_amount');
    $netAmount = $totalPaymentsAmount - $totalServiceFees;

    // --- PAGINASI ---
    $payments = $baseQuery->orderBy('created_at', 'desc')->paginate(100);

    return view('admin.payments.history', compact(
        'payments', 'totalPaymentsCount', 'totalPaymentsAmount',
        'totalServiceFees', 'netAmount', 'daterangeValue'
    ));
}

    public function qris_history(Request $request)
    {
        $accessibleOutletIds = getData()->outlets->pluck('id')->toArray();

        $qrisTransactionsBaseQuery = QrisTransactionDetail::query()
            ->with(['transactionable.outlet']) // relasi outlet dari Payment
            ->whereHasMorph(
                'transactionable',
                [Payment::class],
                function ($query) use ($accessibleOutletIds) {
                    $query->whereIn('outlet_id', $accessibleOutletIds);
                }
            );

        $topupQrisBaseQuery = TopupHistory::query()
            ->with(['member.user', 'outlet', 'owner'])
            ->whereIn('outlet_id', $accessibleOutletIds)
            ->where('payment_method', 'qris');

        // Hitung ringkasan total
        $totalOverallQrisCount = (clone $qrisTransactionsBaseQuery)
            ->whereHas('transactionable', function ($q) {
                $q->where('status', 'success');
            })->count();

        $totalOverallQrisAmount = (clone $qrisTransactionsBaseQuery)
            ->whereHas('transactionable', function ($q) {
                $q->where('status', 'success');
            })->get()
            ->sum(fn($qrisItem) => $qrisItem->transactionable->amount ?? $qrisItem->amount);

        $totalOverallTopupQrisCount = (clone $topupQrisBaseQuery)
            ->where('status', 'success')->count();

        $totalOverallTopupAmount = (clone $topupQrisBaseQuery)
            ->where('status', 'success')->sum('amount');

        $totalCombinedCountOverall = $totalOverallQrisCount + $totalOverallTopupQrisCount;
        $totalCombinedAmountOverall = $totalOverallQrisAmount + $totalOverallTopupAmount;

        // Terapkan filter
        $filteredQrisTransactionsQuery = clone $qrisTransactionsBaseQuery;
        $filteredTopupQrisQuery = clone $topupQrisBaseQuery;

        if ($request->has('daterange') && $request->daterange != '') {
            try {
                [$startDate, $endDate] = explode(' - ', $request->daterange);
                $startDate = Carbon::createFromFormat('Y-m-d', trim($startDate))->startOfDay();
                $endDate = Carbon::createFromFormat('Y-m-d', trim($endDate))->endOfDay();

                $filteredQrisTransactionsQuery->whereBetween('created_at', [$startDate, $endDate]);
                $filteredTopupQrisQuery->whereBetween('created_at', [$startDate, $endDate]);
            } catch (\Exception $e) {
                Log::error('Invalid date format in daterange filter for QRIS history', ['exception' => $e]);
            }
        }

        if ($request->has('status') && $request->status != '') {
            $filteredQrisTransactionsQuery->whereHas('transactionable', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
            $filteredTopupQrisQuery->where('status', $request->status);
        }

        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;

            // Untuk transaksi QRIS dari payments
            $filteredQrisTransactionsQuery->whereHas('transactionable', function ($q) use ($searchTerm) {
                $q->where('amount', 'like', "%{$searchTerm}%")
                  ->orWhereHas('outlet', function ($q2) use ($searchTerm) {
                      $q2->where('outlet_name', 'like', "%{$searchTerm}%");
                  });
            });

            // Untuk topup member
            $filteredTopupQrisQuery->where(function ($q) use ($searchTerm) {
                $q->whereHas('member.user', function ($q2) use ($searchTerm) {
                    $q2->where('name', 'like', "%{$searchTerm}%");
                })->orWhere('amount', 'like', "%{$searchTerm}%")
                  ->orWhereHas('outlet', function ($q3) use ($searchTerm) {
                      $q3->where('outlet_name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // Gabungkan dan paginasi hasil
        $qrisTransactions = $filteredQrisTransactionsQuery->get();
        $topupQris = $filteredTopupQrisQuery->get();

        $allTransactions = collect($qrisTransactions)->map(function ($item) {
            $item->type = 'payment';
            return $item;
        })->merge(collect($topupQris)->map(function ($item) {
            $item->type = 'topup';
            return $item;
        }))->sortByDesc('created_at');

        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $paginatedTransactions = new LengthAwarePaginator(
            $allTransactions->forPage($currentPage, $perPage),
            $allTransactions->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url()]
        );

        $totalFilteredCount = $allTransactions->count();
        $totalFilteredAmount = $allTransactions->sum(function ($item) {
            return $item->transactionable->amount ?? $item->amount;
        });

        return view('admin.payments.qris_history', compact(
            'paginatedTransactions',
            'totalCombinedCountOverall',
            'totalCombinedAmountOverall',
            'totalFilteredCount',
            'totalFilteredAmount'
        ));
    }

}
