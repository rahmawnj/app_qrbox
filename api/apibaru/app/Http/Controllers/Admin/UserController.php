<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log; // Gunakan Log untuk mencatat kesalahan

class UserController extends Controller
{
    /**
     * Menampilkan daftar semua pengguna dengan peran 'admin'.
     */
    public function index()
    {
        try {
            $users = User::where('role', 'admin')->get();
            return view('admin.users.index', compact('users'));
        } catch (\Exception $e) {
            Log::error("Failed to load users: " . $e->getMessage());
            return redirect()->route('admin.users.index')->with('error', 'Failed to load users');
        }
    }

    /**
     * Menampilkan formulir untuk membuat pengguna baru.
     */
    public function create()
    {
        try {
            return view('admin.users.create');
        } catch (\Exception $e) {
            Log::error("Failed to load create user form: " . $e->getMessage());
            return redirect()->route('admin.users.index')->with('error', 'Failed to load create user form');
        }
    }

    /**
     * Menyimpan pengguna baru ke dalam database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|string|email|max:255|unique:users',
            'password'              => 'required|string|min:6|confirmed',
            'image'                 => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $user = new User([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'admin',
            ]);

            // Panggil helper global untuk mengunggah gambar
            if ($request->hasFile('image')) {
                $user->image = uploadImage($request->file('image'), 'users');
            }

            $user->save();

            return redirect()->route('admin.users.index')->with('success', 'User created successfully');
        } catch (\Exception $e) {
            Log::error("Failed to create user: " . $e->getMessage());
            return redirect()->route('admin.users.index')->with('error', 'Failed to create user');
        }
    }

    /**
     * Menampilkan formulir untuk mengedit pengguna yang sudah ada.
     */
    public function edit(User $user)
    {
        try {
            return view('admin.users.edit', compact('user'));
        } catch (\Exception $e) {
            Log::error("Failed to load edit user form: " . $e->getMessage());
            return redirect()->route('admin.users.index')->with('error', 'Failed to load edit user form');
        }
    }

    /**
     * Memperbarui pengguna yang sudah ada di database.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password'              => 'nullable|string|min:6|confirmed',
            'image'                 => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $user->name = $request->name;
            $user->email = $request->email;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            // Panggil helper global untuk mengunggah dan menghapus gambar
            if ($request->hasFile('image')) {
                // Hapus gambar lama sebelum mengunggah yang baru
                deleteImage($user->image);

                $user->image = uploadImage($request->file('image'), 'users');
            }

            $user->save();

            return redirect()->route('admin.users.index')->with('success', 'User updated successfully');
        } catch (\Exception $e) {
            Log::error("Failed to update user: " . $e->getMessage());
            return redirect()->route('admin.users.index')->with('error', 'Failed to update user');
        }
    }

    /**
     * Menghapus pengguna dari database.
     */
    public function destroy(User $user)
    {
        try {
            // Panggil helper global untuk menghapus gambar dari penyimpanan
            deleteImage($user->image);

            $user->delete();

            return redirect()->route('admin.users.index')->with('success', 'User deleted successfully');
        } catch (\Exception $e) {
            Log::error("Failed to delete user: " . $e->getMessage());
            return redirect()->route('admin.users.index')->with('error', 'Failed to delete user');
        }
    }
}