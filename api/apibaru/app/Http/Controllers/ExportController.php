<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AdminTransactionsExport;
use App\Exports\PartnerTransactionsExport;

class ExportController extends Controller
{
    public function adminTransactions(Request $request)
    {
        $baseQuery = Transaction::query()->with(['owner.user', 'outlet']);

        if ($request->filled('search')) {
            $search = $request->search;
            $baseQuery->where(function ($q) use ($search) {
                $q->where('order_id', 'like', '%' . $search . '%')
                    ->orWhere('amount', 'like', '%' . $search . '%')
                    ->orWhereHas('owner', function ($q2) use ($search) {
                        $q2->where('brand_name', 'like', '%' . $search . '%')
                            ->orWhereHas('user', function ($q3) use ($search) {
                                $q3->where('name', 'like', '%' . $search . '%');
                            });
                    })
                    ->orWhereHas('outlet', function ($q4) use ($search) {
                        $q4->where('outlet_name', 'like', '%' . $search . '%')
                            ->orWhere('code', 'like', '%' . $search . '%')
                            ->orWhere('address', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($request->filled('status')) {
            $baseQuery->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $baseQuery->where('type', $request->type);
        }

        $startDate = null;
        $endDate = null;

        if ($request->filled('daterange')) {
            $dates = explode(' - ', $request->daterange);
            if (count($dates) == 2) {
                $startDate = Carbon::createFromFormat('Y-m-d', $dates[0])->startOfDay();
                $endDate = Carbon::createFromFormat('Y-m-d', $dates[1])->endOfDay();
                $baseQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
        } else {
            // Default: hari ini sampai 7 hari ke belakang jika daterange kosong
            $endDate = Carbon::now()->endOfDay(); // Hari ini hingga akhir hari
            $startDate = Carbon::now()->subDays(6)->startOfDay();
            $baseQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Clone baseQuery untuk total (tanpa limit)
        $totalAmount = (clone $baseQuery)->sum('amount');

        $totalTransactions = (clone $baseQuery)->count();

        // Ambil transaksi maksimal 1000 untuk ditampilkan
        $transactions = (clone $baseQuery)
            ->orderBy('created_at', 'desc')
            ->limit(1000)
            ->get();

        return Excel::download(
            new AdminTransactionsExport($transactions, $startDate, $endDate, $totalAmount, $totalTransactions),
            'transactions_export_' . Carbon::now()->format('Ymd_His') . '.xlsx'
        );
    }


    public function partnerTransactions(Request $request)
    {
        $transactionsQuery = Transaction::query()->with(['owner.user', 'outlet']);



        $outletIds = getData()->outlets->pluck('id')->toArray();
        $transactionsQuery->whereIn('outlet_id', $outletIds);


        // Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $transactionsQuery->where(function ($q) use ($search) {
                $q->where('order_id', 'like', '%' . $search . '%')
                    ->orWhere('amount', 'like', '%' . $search . '%')
                    ->orWhereHas('outlet', function ($q4) use ($search) {
                        $q4->where('outlet_name', 'like', '%' . $search . '%')
                            ->orWhere('code', 'like', '%' . $search . '%')
                            ->orWhere('address', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($request->filled('status')) {
            $transactionsQuery->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $transactionsQuery->where('type', $request->type);
        }

        $startDate = null;
        $endDate = null;

        if ($request->filled('daterange')) {
            $dates = explode(' - ', $request->daterange);
            if (count($dates) === 2) {
                $startDate = Carbon::createFromFormat('Y-m-d', $dates[0])->startOfDay();
                $endDate = Carbon::createFromFormat('Y-m-d', $dates[1])->endOfDay();
                $transactionsQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
        } else {
            // Default: hari ini sampai 7 hari ke belakang jika daterange kosong
            $endDate = Carbon::now()->endOfDay(); // Hari ini hingga akhir hari
            $startDate = Carbon::now()->subDays(6)->startOfDay(); // 7 hari ke belakang (termasuk hari ini)
            $transactionsQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Gunakan query yang benar (transactionsQuery)
        $totalAmount = (clone $transactionsQuery)->sum('amount');
        $totalTransactions = (clone $transactionsQuery)->count();

        $transactions = (clone $transactionsQuery)
            ->orderBy('created_at', 'desc')
            ->limit(1000)
            ->get();

            $brandName = getBrand()->brand_name;


        return Excel::download(
            new PartnerTransactionsExport($transactions, $startDate, $endDate, $totalAmount, $totalTransactions, $brandName),
            'transactions_export_' . Carbon::now()->format('Ymd_His') . '.xlsx'
        );
    }
}