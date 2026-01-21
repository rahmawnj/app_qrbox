<?php

namespace App\Http\Controllers\Admin;

use App\Models\Owner;
use App\Models\Outlet;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\OutletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OutletController extends Controller
{
    public function index()
    {
        try {
            // Mengambil outlet beserta informasi owner-nya
            $outlets = Outlet::with('owner')->latest()->get();
            return view('admin.outlets.index', compact('outlets'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat data: ' . $e->getMessage());
        }
    }

    public function create()
    {
        try {
            $owners = Owner::all();
            return view('admin.outlets.create', compact('owners'));
        } catch (\Exception $e) {
            return redirect()->route('admin.outlets.index')->with('error', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'outlet_name'             => 'required|string|max:255',
            'owner_id'                => 'required|exists:owners,id',
            'city_name'               => 'required|string|max:100',
            'address'                 => 'required|string|max:500',
            'timezone'                => 'required|in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura',
            'status'                  => 'required|boolean',
            'lat'                     => 'required|numeric',
            'lon'                     => 'required|numeric',
            'image'                   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'service_fee_percentage'  => 'nullable|numeric|between:0,0.999',
            'min_monthly_service_fee' => 'nullable|numeric',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $imagePath = null;
                if ($request->hasFile('image')) {
                    // Menggunakan helper uploadImage atau default store
                    $imagePath = $request->file('image')->store('outlets', 'public');
                }

                $outlet = Outlet::create([
                    'owner_id'                => $request->owner_id,
                    'code'                    => $this->generateUniqueCode(),
                    'outlet_name'             => $request->outlet_name,
                    'city_name'               => $request->city_name,
                    'address'                 => $request->address,
                    'timezone'                => $request->timezone,
                    'status'                  => $request->status,
                    'image'                   => $imagePath,
                    'latlong'                 => [
                        'lat' => $request->lat,
                        'lon' => $request->lon
                    ],
                    'service_fee_percentage'  => $request->service_fee_percentage ?? 0.100,
                    'min_monthly_service_fee' => $request->min_monthly_service_fee ?? 100000.00,
                    'device_deposit_price'    => $request->device_deposit_price ?? 500000.00,
                ]);
                outletService()->syncToken($outlet);


            });

            return redirect()->route('admin.outlets.index')->with('success', 'Outlet berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Error creating outlet: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function show(Outlet $outlet)
    {
        $outlet->load('owner');
        return view('admin.outlets.show', compact('outlet'));
    }

    public function edit(Outlet $outlet)
    {
        $owners = Owner::all();
        return view('admin.outlets.edit', compact('outlet', 'owners'));
    }

    public function update(Request $request, Outlet $outlet)
    {
        $request->validate([
            'outlet_name' => 'required|string|max:255',
            'owner_id'    => 'required|exists:owners,id',
            'city_name'   => 'required|string|max:100',
            'address'     => 'required|string|max:500',
            'timezone'    => 'required|in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura',
            'status'      => 'required|boolean',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            DB::transaction(function () use ($request, $outlet) {
                $data = $request->only([
                    'owner_id', 'outlet_name', 'city_name', 'address',
                    'timezone', 'status', 'service_fee_percentage',
                    'min_monthly_service_fee'
                ]);

                if ($request->hasFile('image')) {
                    if ($outlet->image) {
                        Storage::disk('public')->delete($outlet->image);
                    }
                    $data['image'] = $request->file('image')->store('outlets', 'public');
                }

                if ($request->filled('lat') && $request->filled('lon')) {
                    $data['latlong'] = [
                        'lat' => $request->lat,
                        'lon' => $request->lon
                    ];
                }

                $outlet->update($data);
            });

            return redirect()->route('admin.outlets.index')->with('success', 'Outlet berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui: ' . $e->getMessage());
        }
    }

    public function destroy(Outlet $outlet)
    {
        try {
            if ($outlet->image) {
                Storage::disk('public')->delete($outlet->image);
            }
            $outlet->delete();
            return redirect()->route('admin.outlets.index')->with('success', 'Outlet berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Request $request, Outlet $outlet)
    {
        $outlet->update(['status' => $request->status]);
        return response()->json(['success' => true, 'message' => 'Status outlet diperbarui']);
    }

    private function generateUniqueCode()
    {
        do {
            $code = 'OUT-' . strtoupper(Str::random(6));
        } while (Outlet::where('code', $code)->exists());

        return $code;
    }

public function regenerateToken(Request $request, $id)
{
    // 1. Validasi Input
    $request->validate([
        'password' => 'required'
    ]);

    // 2. Identifikasi Guard yang sedang aktif (admin_config atau web)
    $guard = Auth::guard('admin_config')->check() ? 'admin_config' : 'web';
    $user = Auth::guard($guard)->user();

    if (!$user) {
        return redirect()->back()->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
    }

    // 3. Validasi Password ke Guard yang tepat
    // Ini akan mengecek password terhadap hardcoded config atau database users
    $isPasswordCorrect = Auth::guard($guard)->validate([
        'email'    => $user->email,
        'password' => $request->password,
    ]);

    if (!$isPasswordCorrect) {
        return redirect()->back()
            ->withInput() // Agar input selain password tidak hilang
            ->with('error_password', 'Konfirmasi password gagal. Password yang Anda masukkan salah.');
    }

    // 4. Proses Update Token menggunakan Transaction
    DB::beginTransaction();
    try {
        $outlet = Outlet::findOrFail($id);

        // Memanggil service helper yang sudah kita buat sebelumnya
        // Logic generate token ada di dalam Service agar konsisten
        outletService()->syncToken($outlet);

        DB::commit();

        return redirect()->back()->with('success', 'Device Token untuk outlet ' . $outlet->outlet_name . ' berhasil diperbarui.');

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Outlet tidak ditemukan.');
    } catch (\Exception $e) {
        DB::rollBack();

        // Log error untuk kebutuhan debugging dev
        Log::error('Regenerate Token Error: ' . $e->getMessage(), [
            'outlet_id' => $id,
            'user' => $user->email
        ]);

        return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat memperbarui token.');
    }
}
}
