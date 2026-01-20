<?php

namespace App\Http\Controllers\Partner;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Device;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Constants\OrderStatus;
use Illuminate\Validation\Rule;
use App\Events\NotificationEvent;
use App\Models\DeviceTransaction;
use App\Models\DropOffTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class DeviceController extends Controller
{
    public function serviceOrder()
    {
        $feature = getData();
        if (!$feature->can('partner.service-order.list')) {
            abort(403, 'Anda tidak memiliki izin.');
        }

        $finalStatuses = OrderStatus::finalStatuses();

       $dropOffTransactions = DropOffTransaction::query()
            ->whereHas('transaction', function ($query) {
                $query->where('channel_type', 'drop_off');
            })
            ->whereNotIn('progress', $finalStatuses)
            ->with('transaction.payments') // Eager load relasi transaction dan payment
            ->whereHas('transaction.payments', function ($query) {
                $query->where('status', 'success');
            })
            ->latest()
            ->get();

        return view('partner.service_order.list', compact('dropOffTransactions'));
    }

    public function serviceOrderDetail($id)
    {
        $outlets = getData()->outlets->pluck('id')->toArray();

        $transaction = Transaction::with(['manualDetails', 'deviceTransactions'])
            ->where('type', 'manual')
            ->whereIn('outlet_id', $outlets)
            ->findOrFail($id);

        return view('partner.service_order.detail', compact('transaction'));
    }

    public function activateDeviceService(Request $request, DeviceTransaction $deviceTransaction)
    {
        // $feature = getData();
        // if (!$feature->can('partner.service-orders.activate-device')) {
        //     abort(403, 'Anda tidak memiliki izin.');
        // }
        $now = Carbon::now();

        if (is_null($deviceTransaction->activated_at) || ($deviceTransaction->activated_at && $deviceTransaction->activated_at->diffInHours($now) < 24)) {
            $updateData = [
                'status' => true,
                'bypass_activation' => Carbon::now()
            ];

            if (is_null($deviceTransaction->activated_at)) {
                $updateData['activated_at'] = $now;
            }

            DB::beginTransaction();
            try {

                $deviceTransaction->update($updateData);

                DB::commit();
                return redirect()->back()->with('success', 'Layanan perangkat ' . $deviceTransaction->device_code . ' berhasil diaktifkan!');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to activate device service: ' . $e->getMessage(), [
                    'device_transaction_id' => $deviceTransaction->id,
                    'exception' => $e
                ]);
                return redirect()->back()->with('error', 'Gagal mengaktifkan layanan perangkat: ' . $e->getMessage());
            }
        } else {
            return redirect()->back()->with('error', 'Layanan perangkat ' . $deviceTransaction->device_code . ' sudah selesai dan tidak dapat diaktifkan kembali karena sudah melewati batas 24 jam.');
        }
    }

       public function updateServiceProgress(Request $request, DropOffTransaction $dropOffTransaction)
    {
        $feature = getData();

        $request->validate([
            'progress' => ['required', Rule::in(array_keys(\App\Constants\OrderStatus::STATUSES))],
        ]);

        $oldProgress = $dropOffTransaction->progress; // Simpan progress lama
        $newProgress = $request->progress;

        $dropOffTransaction->progress = $newProgress;
        $dropOffTransaction->save();

        if ($dropOffTransaction->progress === 'completed') {
            $transaction = $dropOffTransaction->transaction;
            $transaction->status = 'success';
            $transaction->save();
        }

        // --- Logika Notifikasi untuk Member ---
        $transaction = $dropOffTransaction->transaction;
        if ($transaction && $transaction->member_id) {
            $memberUser = User::whereHas('member', function ($q) use ($transaction) {
                $q->where('id', $transaction->member_id);
            })->first();

            if ($memberUser) {
                $title = '🚀 Status Layanan Anda Berubah!';
                $message = 'Status layanan "' . $dropOffTransaction->service->name . '" Anda (ID Transaksi: ' . $transaction->order_id . ') telah diperbarui menjadi ' . \App\Constants\OrderStatus::STATUSES[$newProgress]['label'] . '.';

                event(new NotificationEvent(
                    recipients: $memberUser,
                    title: $title,
                    message: $message,
                    url: route('home.member.transactions')
                ));
            }
        }
        // --- Akhir Logika Notifikasi ---

        $message = 'Status progress berhasil diperbarui ke ' . \App\Constants\OrderStatus::STATUSES[$newProgress]['label'];

        return back()->with('success', $message);
    }

}
