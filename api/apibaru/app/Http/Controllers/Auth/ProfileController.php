<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule; // Import untuk Rule::unique

class ProfileController extends Controller
{
    public function form()
    {
        return view('auth.profile');
    }

 public function submit(Request $request)
{
    $user = Auth::user();

    $rules = [
        'name'          => ['required', 'string', 'max:255'],
        'email'         => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        'image'         => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'], // Max 2MB
        'password'      => ['nullable', 'string', 'min:8', 'confirmed'],
    ];

    if ($request->filled('password')) {
        $rules['current_password'] = ['required', function ($attribute, $value, $fail) use ($user) {
            if (!Hash::check($value, $user->password)) {
                $fail('Password saat ini salah.');
            }
        }];
    }

    $request->validate($rules);

    // Update data user
    $user->name = $request->name;
    $user->email = $request->email;

    // Logika update password
    if ($request->filled('password')) {
        // Karena validasi sudah dijalankan, kita tidak perlu memeriksa ulang.
        // Jika validasi `current_password` lolos, maka password benar.
        $user->password = Hash::make($request->password);
    }

    // --- Logika untuk mengelola gambar menggunakan helper ---
    if ($request->hasFile('image')) {
        // Hapus gambar lama jika ada dan bukan gambar default
        if ($user->image) {
            // Kita perlu membersihkan path 'storage/' atau 'public/'
            // sebelum mengirimnya ke helper deleteImage.
            $oldImagePath = str_replace('storage/', '', $user->image);

            // Hindari menghapus gambar default
            if (!str_contains($oldImagePath, 'default-user.png')) {
                 deleteImage($user->image);
            }
        }

        // Unggah gambar baru menggunakan helper
        $newImagePath = uploadImage($request->file('image'), 'users');
        if ($newImagePath) {
            $user->image = $newImagePath;
        }
    }

    $user->save();

    return redirect()->back()->with('success', 'Profil berhasil diperbarui!');
}
}
