<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Owner;
use App\Models\Withdrawal;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class OwnerController extends Controller
{
    public function index(Request $request)
    {
        // try {
            $query = Owner::with(['user', 'outlets']);

            $owners = $query->latest()->paginate(10);

            return view('admin.owners.index', compact(
                'owners',
            ));
        // } catch (\Exception $e) {
        //     Log::error("Error in Admin Owner Index: " . $e->getMessage());
        //     return redirect()->route('admin.dashboard')
        //         ->with('error', 'Terjadi kesalahan saat memuat data owner. Silakan coba lagi.');
        // }
    }

    public function create()
    {
        try {
            return view('admin.owners.create');
        } catch (\Exception $e) {
            return redirect()->route('admin.owners.index')
                ->with('error', $e->getMessage());
        }
    }

  public function store(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:8|confirmed',
            'brand_name'            => 'required|string',
            'brand_phone'           => 'nullable|string|max:255',
            'brand_description'     => 'nullable|string',
            'image'                 => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'brand_logo'            => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status'                => 'required|boolean',
            // Validasi Field Baru
            'deposit_amount'        => 'nullable|numeric|min:0', // Tambahkan ini

            'contract_start'        => 'nullable|date',
            'contract_end'          => 'nullable|date|after_or_equal:contract_start',
            'contract_number'       => 'nullable|string|unique:owners,contract_number',
            'bank_name'             => 'nullable|string',
            'bank_account_number'   => 'nullable|string',
            'bank_account_holder_name' => 'nullable|string',
            'code'                  => 'required|string|unique:owners,code',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $user = User::create([
                    'name'      => $request->name,
                    'email'     => $request->email,
                    'password'  => Hash::make($request->password),
                    'role'      => 'owner',
                ]);

                if ($request->hasFile('image')) {
                    $user->image = uploadImage($request->file('image'), 'users');
                    $user->save();
                }

                $brandLogoPath = null;
                if ($request->hasFile('brand_logo')) {
                    $brandLogoPath = uploadImage($request->file('brand_logo'), 'brands');
                }

                $user->owner()->create([
                    'brand_name'            => $request->brand_name,
                    'brand_phone'           => $request->brand_phone,
                    'brand_description'     => $request->brand_description,
                    'brand_logo'            => $brandLogoPath,
                    'status'                => $request->status,
                    'code'                  => $request->code,
                    'contract_start'        => $request->contract_start,
                    'contract_end'          => $request->contract_end,
                    'contract_number'       => $request->contract_number,
                    'bank_name'             => $request->bank_name,
                    'bank_account_number'   => $request->bank_account_number,
                    'bank_account_holder_name' => $request->bank_account_holder_name,
                    'balance'               => 0,
                    'deposit_amount'        => $request->deposit_amount ?? 0, // Tambahkan ini
                ]);
            });

            return redirect()->route('admin.owners.index')->with('success', 'Owner berhasil dibuat');
        } catch (\Exception $e) {
            Log::error("Failed to create owner: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Owner $owner)
    {
        try {
            return view('admin.owners.show', compact('owner'));
        } catch (\Exception $e) {
            return redirect()->route('admin.owners.index')
                ->with('error', $e->getMessage());
        }
    }

    public function edit(Owner $owner)
    {
        try {
            return view('admin.owners.edit', compact('owner'));
        } catch (\Exception $e) {
            return redirect()->route('admin.owners.index')
                ->with('error', $e->getMessage());
        }
    }

 public function update(Request $request, Owner $owner)
    {
        $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email,' . $owner->user->id,
            'password'              => 'nullable|string|min:8|confirmed',
            'brand_name'            => 'required|string',
            'brand_phone'           => 'nullable|string|max:255',
            'brand_description'     => 'nullable|string',
            'image'                 => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'brand_logo'            => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status'                => 'required|boolean',
            'contract_start'        => 'nullable|date',
            'contract_end'          => 'nullable|date|after_or_equal:contract_start',
            'contract_number'       => 'nullable|string|unique:owners,contract_number,' . $owner->id,
            'bank_name'             => 'nullable|string',
            'bank_account_number'   => 'nullable|string',
            'bank_account_holder_name' => 'nullable|string',
            'code'                  => 'required|string|unique:owners,code,' . $owner->id,
            'deposit_amount'        => 'nullable|numeric|min:0', // Tambahkan ini
        ]);

        try {
            DB::transaction(function () use ($request, $owner) {
                $user = $owner->user;
                $user->name = $request->name;
                $user->email = $request->email;
                if ($request->filled('password')) {
                    $user->password = Hash::make($request->password);
                }

                if ($request->hasFile('image')) {
                    if ($user->image) deleteImage($user->image);
                    $user->image = uploadImage($request->file('image'), 'users');
                }
                $user->save();

                if ($request->hasFile('brand_logo')) {
                    if ($owner->brand_logo) deleteImage($owner->brand_logo);
                    $owner->brand_logo = uploadImage($request->file('brand_logo'), 'brands');
                }

                $owner->update([
                    'brand_name'            => $request->brand_name,
                    'brand_phone'           => $request->brand_phone,
                    'brand_description'     => $request->brand_description,
                    'status'                => $request->status,
                    'code'                  => $request->code,
                    'contract_start'        => $request->contract_start,
                    'contract_end'          => $request->contract_end,
                    'contract_number'       => $request->contract_number,
                    'bank_name'             => $request->bank_name,
                    'bank_account_number'   => $request->bank_account_number,
                    'bank_account_holder_name' => $request->bank_account_holder_name,
                    'deposit_amount'        => $request->deposit_amount ?? 0, // Tambahkan ini
                ]);
            });

            return redirect()->route('admin.owners.index')->with('success', 'Owner berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error("Failed to update owner: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(Owner $owner)
    {
        try {
            DB::transaction(function () use ($owner) {
                $user = $owner->user;
                // Hapus gambar user jika ada menggunakan helper
                if ($user->image) {
                    deleteImage($user->image);
                }
                // Hapus brand logo jika ada
                if ($owner->brand_logo) {
                    deleteImage($owner->brand_logo);
                }
                // Hapus record user yang otomatis akan menghapus record owner
                $user->delete();
            });

            return redirect()->route('admin.owners.index')
                ->with('success', 'Owner berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.owners.index')
                ->with('error', $e->getMessage());
        }
    }

    public function profile_update(Request $request, Owner $owner)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $owner->user->id,
            'password'      => 'nullable|string|min:8|confirmed',
            'brand_name'    => 'required|string',
            'brand_email'   => 'required|email',
            'brand_phone'   => 'nullable|string|max:255',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'brand_logo'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Tambahkan validasi
        ]);

        try {
            DB::transaction(function () use ($request, $owner) {
                $user = $owner->user;
                $user->name = $request->name;
                $user->email = $request->email;
                if ($request->filled('password')) {
                    $user->password = Hash::make($request->password);
                }
                if ($request->hasFile('image')) {
                    // Gunakan helper deleteImage
                    if ($user->image) {
                        deleteImage($user->image);
                    }
                    $user->image = uploadImage($request->file('image'), 'users');
                }
                $user->save();

                // Unggah brand logo baru dan hapus brand logo lama
                if ($request->hasFile('brand_logo')) {
                    deleteImage($owner->brand_logo); // Hapus brand logo lama
                    $owner->brand_logo = uploadImage($request->file('brand_logo'), 'brands'); // Unggah yang baru
                }

                // Update data owner
                $owner->update([
                    'brand_name' => $request->brand_name,
                    'brand_email' => $request->brand_email,
                    'brand_phone'   => $request->brand_phone,
                    'brand_logo' => $owner->brand_logo, // Pastikan brand_logo ter-update
                ]);
            });

            return redirect()->route('admin.owners.index')
                ->with('success', 'Profile Owner berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->route('admin.owners.index')
                ->with('error', $e->getMessage());
        }
    }
}
