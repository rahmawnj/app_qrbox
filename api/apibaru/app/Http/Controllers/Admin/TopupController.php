<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\TopupHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class TopupController extends Controller
{
    public function topupHistories(Request $request)
    {
        $query = TopupHistory::with(['member.user', 'outlet']); // Eager load outlet for display

        $ownerOutletIds = getData()->outlets->pluck('id')->toArray();
        $query->whereIn('outlet_id', $ownerOutletIds);



        if ($request->filled('daterange')) {
            $dateRange = explode(' - ', $request->daterange);
            if (count($dateRange) === 2) {
                try {
                    $startDate = Carbon::createFromFormat('Y-m-d', $dateRange[0], 'Asia/Jakarta')->startOfDay(); 
                    $endDate = Carbon::createFromFormat('Y-m-d', $dateRange[1], 'Asia/Jakarta')->endOfDay();   
                    $query->whereBetween('time', [$startDate, $endDate]);
                } catch (\Exception $e) {
                    Log::error('Invalid daterange format: ' . $request->daterange . ' - ' . $e->getMessage());
                }
            }
        }

        // 2. Status Filter
        if ($request->filled('status') && in_array($request->status, ['pending', 'success', 'failed'])) {
            $query->where('status', $request->status);
        }

        // 3. Channel Filter (Kasir or QRIS)
        if ($request->filled('channel')) {
            if ($request->channel === 'cashier') {
                $query->whereNotNull('cashier_name');
            } elseif ($request->channel === 'qris') {
                $query->whereNotNull('qris_transaction_detail_id');
            }
        }

if ($request->filled('search')) {
    $searchTerm = '%' . $request->search . '%';
    $query->where(function ($q) use ($searchTerm) {
        // Search by Member Name
        $q->whereHas('member.user', function ($subQ) use ($searchTerm) {
            $subQ->where('name', 'like', $searchTerm);
        })
            // Or search by Member Phone Number
            ->orWhereHas('member', function ($subQ) use ($searchTerm) {
                $subQ->where('phone_number', 'like', $searchTerm);
            })
            // Or search by Notes
            ->orWhere('notes', 'like', $searchTerm)
            // ✅ Tambahan: Search by Outlet Name
            ->orWhereHas('outlet', function ($subQ) use ($searchTerm) {
                $subQ->where('outlet_name', 'like', $searchTerm);
            });
    });
}


        // Get paginated top-up histories for the table
        $topupHistories = $query->orderBy('time', 'desc')->paginate(15);

        // --- Calculate Summary Data (ALWAYS for 'success' status) ---
        // These calculations should reflect successful top-ups, regardless of table filters.
        $summaryQueryBase = TopupHistory::whereIn('outlet_id', $ownerOutletIds);

        // Apply owner's outlet filter (and specific outlet if applicable) to summaries
        if ($request->filled('out')) {
            $summaryQueryBase->where('outlet_id', $request->out);
        }

        // Apply date range filter to summaries (if present in request)
        if ($request->filled('daterange')) {
            $dateRange = explode(' - ', $request->daterange);
            if (count($dateRange) === 2) {
                try {
                    $startDate = Carbon::createFromFormat('Y-m-d', $dateRange[0], 'Asia/Jakarta')->startOfDay();
                    $endDate = Carbon::createFromFormat('Y-m-d', $dateRange[1], 'Asia/Jakarta')->endOfDay();
                    $summaryQueryBase->whereBetween('time', [$startDate, $endDate]);
                } catch (\Exception $e) { /* ignore for summary if invalid date */
                }
            }
        }

        $totalTopupsCount = (clone $summaryQueryBase)->where('status', 'success')->count();
        $totalTopupsAmount = (clone $summaryQueryBase)->where('status', 'success')->sum('amount');
        $cashierTopupsCount = (clone $summaryQueryBase)->where('status', 'success')->whereNotNull('cashier_name')->count();

        return view('admin.topup_histories', compact(
            'topupHistories',
            'totalTopupsCount',
            'totalTopupsAmount',
            'cashierTopupsCount',
        ));
    }
}
