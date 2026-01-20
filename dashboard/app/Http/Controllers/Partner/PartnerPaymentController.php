<?php

namespace App\Http\Controllers\Partner;

use Carbon\Carbon;
use App\Models\Payment;
use App\Models\TopupHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\QrisTransactionDetail;
use Illuminate\Pagination\LengthAwarePaginator;

class PartnerPaymentController extends Controller
{
    public function payments_history(Request $request)
    {
        // Ambil ID outlet yang dapat diakses oleh partner
        $accessibleOutletIds = getData()->outlets->pluck('id')->toArray();

        // Query dasar untuk mengambil riwayat pembayaran dari outlet yang relevan
        $baseQuery = Payment::query()
            ->with(['transaction.outlet', 'transaction.owner.user'])
            ->whereIn('outlet_id', $accessibleOutletIds)
            ->orderBy('created_at', 'desc');

        // Clone query untuk perhitungan ringkasan keseluruhan (overall)
        $overallSummaryQuery = clone $baseQuery;

        // Clone query untuk perhitungan ringkasan setelah filter (filtered)
        $filteredQuery = clone $baseQuery;

        // Terapkan semua filter ke filteredQuery
        if ($request->has('status') && $request->status != '') {
            $filteredQuery->where('status', $request->status);
        }

        if ($request->has('daterange') && $request->daterange != '') {
            list($startDate, $endDate) = explode(' - ', $request->daterange);
            try {
                $startDate = Carbon::createFromFormat('Y-m-d', trim($startDate))->startOfDay();
                $endDate = Carbon::createFromFormat('Y-m-d', trim($endDate))->endOfDay();
                $filteredQuery->whereBetween('created_at', [$startDate, $endDate]);
            } catch (\Exception $e) {
                Log::error('Invalid date format in daterange filter', ['exception' => $e]);
            }
        }

        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $filteredQuery->where(function ($q) use ($searchTerm) {
                $q->where('amount', 'like', "%{$searchTerm}%")
                    ->orWhere('notes', 'like', "%{$searchTerm}%")
                    ->orWhereHas('transaction', function ($q2) use ($searchTerm) {
                        $q2->where('order_id', 'like', "%{$searchTerm}%")
                            ->orWhereHas('outlet', function ($q3) use ($searchTerm) {
                                $q3->where('outlet_name', 'like', "%{$searchTerm}%");
                            })
                            ->orWhereHas('owner', function ($q4) use ($searchTerm) {
                                $q4->where('brand_name', 'like', "%{$searchTerm}%");
                            });
                    });
            });
        }


        // Filter berdasarkan metode pembayaran
        if ($request->has('payment_method_filter') && $request->payment_method_filter != '') {
            $filter = $request->payment_method_filter;
            if ($filter === 'member') {
                $filteredQuery->where('payment_method', 'member');
            } elseif ($filter === 'cash') {
                $filteredQuery->where('payment_method', 'non_member')->where('payment_type', 'cash');
            } elseif ($filter === 'non_cash') {
                $filteredQuery->where('payment_method', 'non_member')->where('payment_type', 'non_cash');
            }
        }

        // Hitung total ringkasan untuk kartu-kartu di atas (tanpa filter tanggal/pencarian, tapi dengan filter outlet)
        $totalPaymentsCountOverall = $overallSummaryQuery->count();
        $totalPaymentsAmountOverall = (clone $overallSummaryQuery)->where('status', 'success')->sum('amount');

        // Hitung ringkasan untuk footer tabel (setelah filter)
        $totalFilteredPaymentsCount = $filteredQuery->count();
        $totalFilteredPaymentsAmount = $filteredQuery->sum('amount');

        // Terapkan pagination dan ambil data
        $payments = $filteredQuery->paginate(200);


        return view('partner.payments.history', compact(
            'payments',
            'totalPaymentsCountOverall',
            'totalPaymentsAmountOverall',
            'totalFilteredPaymentsCount',
            'totalFilteredPaymentsAmount',

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

        return view('partner.payments.qris_history', compact(
            'paginatedTransactions',
            'totalCombinedCountOverall',
            'totalCombinedAmountOverall',
            'totalFilteredCount',
            'totalFilteredAmount'
        ));
    }

}
