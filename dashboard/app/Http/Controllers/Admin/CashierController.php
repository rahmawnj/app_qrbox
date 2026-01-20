<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Cashier;
use App\Models\Outlet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CashierController extends Controller
{
    public function index()
    {
        try {
            $cashiers = Cashier::with('user', 'outlet')->get();
            return view('admin.cashiers.index', compact('cashiers'));
        } catch (\Exception $e) {
            return redirect()->route('admin.cashiers.index')
                ->with('error', $e->getMessage());
        }
    }

    public function create()
    {
        try {
            $outlets = Outlet::all();
            return view('admin.cashiers.create', compact('outlets'));
        } catch (\Exception $e) {
            return redirect()->route('admin.cashiers.index')
                ->with('error', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'image'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'outlet_id'=> 'nullable|exists:outlets,id',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $user = User::create([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                    'role'     => 'cashier'
                ]);

                if ($request->hasFile('image')) {
                    $path = $request->file('image')->store('public/images/users');
                    $user->image = Storage::url($path);
                    $user->save();
                }

                $user->cashier()->create([
                    'outlet_id' => $request->outlet_id,
                    'status'    => false
                ]);
            });

            return redirect()->route('admin.cashiers.index')
                ->with('success', 'Cashier berhasil dibuat');
        } catch (\Exception $e) {
            return redirect()->route('admin.cashiers.index')
                ->with('error', $e->getMessage());
        }
    }

    public function edit(Cashier $cashier)
    {
        try {
            $cashier->load('user');
            $outlets = Outlet::all();
            return view('admin.cashiers.edit', compact('cashier', 'outlets'));
        } catch (\Exception $e) {
            return redirect()->route('admin.cashiers.index')
                ->with('error', $e->getMessage());
        }
    }
public function show(Cashier $cashier)
{
    try {
        // Load relasi user, outlet, dan owner melalui outlet
        $cashier->load(['user', 'outlet.owner']);
        return view('admin.cashiers.show', compact('cashier'));
    } catch (\Exception $e) {
        return redirect()->route('admin.cashiers.index')
            ->with('error', $e->getMessage());
    }
}
    public function update(Request $request, Cashier $cashier)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $cashier->user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'image'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'outlet_id'=> 'nullable|exists:outlets,id',
        ]);

        try {
            DB::transaction(function () use ($request, $cashier) {
                $cashier->update([
                    'outlet_id' => $request->outlet_id,
                ]);

                $user = $cashier->user;
                $user->name = $request->name;
                $user->email = $request->email;
                if ($request->filled('password')) {
                    $user->password = Hash::make($request->password);
                }
                if ($request->hasFile('image')) {
                    if ($user->image) {
                        $imageName = basename($user->image);
                        Storage::delete('public/images/users/' . $imageName);
                    }
                    $path = $request->file('image')->store('public/images/users');
                    $user->image = Storage::url($path);
                }
                $user->save();
            });

            return redirect()->route('admin.cashiers.index')
                ->with('success', 'Cashier berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->route('admin.cashiers.index')
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(Cashier $cashier)
    {
        try {
            DB::transaction(function () use ($cashier) {
                $user = $cashier->user;
                if ($user->image) {
                    $imageName = basename($user->image);
                    Storage::delete('public/images/users/' . $imageName);
                }
                $user->delete();
            });

            return redirect()->route('admin.cashiers.index')
                ->with('success', 'Cashier berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.cashiers.index')
                ->with('error', $e->getMessage());
        }
    }
}
