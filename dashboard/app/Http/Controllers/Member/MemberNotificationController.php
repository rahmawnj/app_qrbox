<?php

namespace App\Http\Controllers\Member;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class MemberNotificationController extends Controller
{
        public function markAsRead(Request $request)
    {
        $user = Auth::user();
        $notification = $request->notification;
        if ($notification == 'all') {
            // Kasus 1: Tandai semua notifikasi sebagai sudah dibaca
            $user->unreadNotifications->markAsRead();

            // Redirect kembali ke halaman sebelumnya
            return redirect()->back()->with('success', 'Semua notifikasi telah ditandai sebagai sudah dibaca.');
        }

        Log::info($request->all());

        // Kasus 2: Tandai satu notifikasi tertentu sebagai sudah dibaca
        $userNotification = $user->notifications()->where('id', $notification)->first();

        if ($userNotification) {
            $userNotification->markAsRead();
            return response()->json(['message' => 'Notification marked as read.']);
        }

        // Jika notifikasi tidak ditemukan, kembalikan respons error
        return response()->json(['message' => 'Notification not found.'], 404);
    }

     public function list()
    {
        // Mendapatkan pengguna yang sedang login
        $user = Auth::user();

        // Mengambil semua notifikasi pengguna
        $notifications = $user->notifications;

        // Mengirimkan notifikasi ke view
        return view('landing.member.notifications', compact('notifications'));
    }
}
