<?php

namespace App\Http\Controllers\Landing;

use App\Models\User;
use App\Models\Member;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\TopupHistory;
use Illuminate\Http\Request;
use App\Constants\OrderStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Owner; // Pastikan Anda mengimpor model Owner
use Illuminate\Validation\ValidationException; // Import for password validation errors

class MemberDashboardController extends Controller
{
   public function dashboard()
{
    $user = Auth::user();

    $member = $user->member;

    if (!$member) {
        return redirect()->route('dashboard')->with('error', 'Anda belum terdaftar sebagai member.');
    }

    // 1. Ambil Total Saldo Member (dari semua langganan yang diverifikasi)
    $totalBalance = DB::table('subscription')
        ->where('member_id', $member->id)
        ->where('is_verified', 1)
        ->sum('amount');

    // 2. Ambil Jumlah Brand/Owner yang Dilanggan (yang sudah diverifikasi)
    $subscribedBrandsCount = $member->owners()->wherePivot('is_verified', 1)->count();

    $pendingSubscriptionsCount = $member->owners()->wherePivot('is_verified', 0)->count();

    // 4. Ambil Transaksi Terbaru
    $recentTransactions = Transaction::where('member_id', $member->id)
        ->with(['owner', 'outlet'])
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

    // 5. Profile completion status
    $profileCompletion = [
        'email_verified' => !is_null($user->email_verified_at),
        'phone_number_added' => !is_null($member->phone_number),
        'address_complete' => !is_null($member->address) && !is_null($member->latlong),
    ];

    // Calculate profile progress percentage
    $completedSteps = 0;
    $totalSteps = 3; // Email, Phone, Address/Latlong

    if ($profileCompletion['email_verified']) $completedSteps++;
    if ($profileCompletion['phone_number_added']) $completedSteps++;
    if ($profileCompletion['address_complete']) $completedSteps++;

    $profileProgressPercentage = ($totalSteps > 0) ? round(($completedSteps / $totalSteps) * 100) : 0;

    return view('landing.member.dashboard', compact(
        'user',
        'member',
        'totalBalance',
        'subscribedBrandsCount',
        'pendingSubscriptionsCount',
        'recentTransactions',
        'profileCompletion',
        'profileProgressPercentage'
    ));
}

    public function brands()
    {
        $user = Auth::user();
        $member = $user->member;

        if (!$member) {
            return redirect()->route('dashboard')->with('error', 'Anda belum terdaftar sebagai member.');
        }

        // Ambil langganan yang sudah diverifikasi
        $verifiedSubscriptions = $member->owners()
            ->wherePivot('is_verified', 1)
            ->get();

        // Ambil langganan yang masih pending
        $pendingSubscriptions = $member->owners()
            ->wherePivot('is_verified', 0)
            ->get();

        return view('landing.member.brands', compact(
            'user',
            'member',
            'verifiedSubscriptions',
            'pendingSubscriptions'
        ));
    }

    /**
     * Menggenerate kode unik untuk langganan member ke sebuah owner.
     * Metode ini akan menggunakan HTTP POST dan meredirect kembali.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generateSubscriptionCode(Request $request)
    {
        // 1. Pastikan pengguna terautentikasi
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Anda harus login untuk melakukan tindakan ini.');
        }

        // 2. Ambil data member dari pengguna yang terautentikasi
        $member = $user->member;
        if (!$member) {
            return redirect()->route('dashboard')->with('error', 'Anda belum terdaftar sebagai member.');
        }

        // 3. Validasi input owner_id
        $request->validate([
            'owner_id' => 'required|exists:owners,id',
        ]);

        $ownerId = $request->input('owner_id');
        $memberId = $member->id;

        // --- Perubahan di sini: Generate kode unik 6 karakter (HANYA HURUF BESAR ALPHABET) ---
        $characters = '0123456789';
        $generatedCode = '';
        $length = 6;
        for ($i = 0; $i < $length; $i++) {
            $generatedCode .= $characters[rand(0, strlen($characters) - 1)];
        }
        // ---------------------------------------------------------------------------------

        // Waktu saat ini untuk disimpan sebagai 'code_generated_at'
        $currentTime = now();

        // 5. Cari atau buat entri langganan di tabel pivot 'subscription'
        // Jika kombinasi member_id dan owner_id sudah ada, akan diupdate.
        // Jika belum ada, akan dibuat entri baru.
        // PENTING: Pastikan nama kolom 'payment_code' dan 'code_generated_at'
        // sesuai dengan nama kolom aktual di tabel 'subscription' database Anda.
        DB::table('subscription')->updateOrInsert(
            ['member_id' => $memberId, 'owner_id' => $ownerId],
            [
                'code' => $generatedCode, // Kolom untuk menyimpan kode pembayaran
                'pin_generated_time' => $currentTime, // Kolom untuk menyimpan waktu generate kode
                'is_used' => false,
                'updated_at' => $currentTime,
            ]
        );

        // 6. Ambil nama brand yang terkait dengan owner_id
        // Ini penting untuk menampilkan nama brand di modal
        $owner = Owner::find($ownerId);
        if (!$owner) {
            return redirect()->back()->with('error', 'Brand tidak ditemukan.');
        }

        // --- Perubahan di sini: Arahkan kembali pengguna dengan data sukses untuk modal ---
        return redirect()->back()->with([
            'success_code_generated' => [
                'code' => $generatedCode, // Kirim kode yang baru digenerate
                'brand_name' => $owner->brand_name, // Kirim nama brand
                'code_generated_at' => $currentTime->toDateTimeString(), // Kirim waktu generate sebagai string yang kompatibel dengan JavaScript
            ],
            'message' => 'Kode pembayaran untuk ' . $owner->brand_name . ' berhasil diaktifkan.' // Pesan sukses umum
        ]);
        // ---------------------------------------------------------------------------------
    }


    public function transactions()
    {
        $user = Auth::user();
        $member = $user->member;

        if (!$member) {
            return redirect()->route('dashboard')->with('error', 'Anda belum terdaftar sebagai member.');
        }

        $transactions = $member->transactions()
            ->with('payments')
            ->get();

        return view('landing.member.transactions', compact('user', 'member', 'transactions'));
    }


    public function orders()
    {
        $user = Auth::user();
        $member = $user->member;

        if (!$member) {
            return redirect()->route('dashboard')->with('error', 'Anda belum terdaftar sebagai member.');
        }

        $finalStatuses = OrderStatus::finalStatuses();

        // Mengambil semua transaksi member, lalu memfilter dengan whereHas
        $orders = $user->member->transactions()
            ->with('dropOffTransaction', 'selfServiceTransaction')
            ->where(function ($query) use ($finalStatuses) {
                // Query untuk transaksi Drop-off yang statusnya belum final
                $query->whereHas('dropOffTransaction', function ($subQuery) use ($finalStatuses) {
                    $subQuery->whereNotIn('progress', $finalStatuses);
                })
                // Query untuk transaksi Self-service yang device_status-nya 1 (aktif)
                ->orWhereHas('selfServiceTransaction', function ($subQuery) {
                    $subQuery->where('device_status', 1);
                });
            })
             ->whereHas('payments', function ($query) {
        $query->where('status', 'success');
    })
            ->latest()
            ->get();

        return view('landing.member.orders', compact('user', 'member', 'orders'));
    }



    public function profile()
    {
        $user = Auth::user();
        $member = $user->member;

        if (!$member) {
            return redirect()->route('dashboard')->with('error', 'Anda belum terdaftar sebagai member.');
        }

        return view('landing.member.profile', compact('user', 'member'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $member = $user->member;

        // 1. Validate the incoming data
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        try {
            // 2. Update user data
            $user->name = $request->input('name');
            $user->save();

            // 3. Update member data
            if ($member) {
                $member->phone_number = $request->input('phone_number');
                $member->address = $request->input('address');

                // Combine latitude and longitude into a JSON column 'latlong'
                $member->latlong = [
                    'latitude' => (float)$request->input('latitude'),
                    'longitude' => (float)$request->input('longitude'),
                ];
                $member->save();
            } else {
                Log::warning('Member profile not found for user ID: ' . $user->id);
            }

            // 4. Return with a success flash message for full page reload
            return redirect()->back()->with('success', 'Profil berhasil diperbarui!');
        } catch (\Exception $e) {
            // 5. Return with an error flash message if an exception occurs
            Log::error('Error updating profile: ' . $e->getMessage(), ['user_id' => $user->id, 'request_data' => $request->all()]);
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui profil. Silakan coba lagi.');
        }
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user(); // Mendapatkan user yang sedang login

        // 1. Validasi data yang masuk
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed', // 'confirmed' akan memvalidasi field_confirmation
        ]);

        // 2. Verifikasi kata sandi saat ini
        if (!Hash::check($request->input('current_password'), $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Kata sandi saat ini salah.'],
            ]);
        }

        try {
            // 3. Update kata sandi baru
            $user->password = Hash::make($request->input('new_password'));
            $user->save();

            // 4. Redirect kembali dengan pesan sukses
            return redirect()->back()->with('success', 'Kata sandi berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('Error updating password: ' . $e->getMessage(), ['user_id' => $user->id]);
            return redirect()->back()->with('error', 'Gagal memperbarui kata sandi: ' . $e->getMessage());
        }
    }

    public function topupHistories(Request $request)
    {
        $user = Auth::user();
        $member = $user->member;

        if (!$member) {
            return redirect()->route('dashboard')->with('error', 'Anda belum terdaftar sebagai member.');
        }

        // Ambil riwayat topup untuk member yang sedang login
        // Eager load relasi owner dan outlet jika diperlukan untuk menampilkan nama
        $topupHistories = TopupHistory::where('member_id', $member->id)
            ->with(['owner', 'outlet']) // Asumsi ada relasi di model TopupHistory
            ->orderBy('time', 'desc') // Urutkan dari yang terbaru
            ->paginate(10); // Tambahkan paginasi, misalnya 10 item per halaman

        return view('landing.member.topup_histories', compact('user', 'member', 'topupHistories'));
    }
}
