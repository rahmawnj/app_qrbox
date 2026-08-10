<?php

namespace App\Http\Controllers\Partner;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function form(Request $request)
    {
        $currentPage = $request->query('page', 'profile');

        return view('partner.brand.edit-profile', compact('currentPage'));
    }

public function submit(Request $request)
{
    $owner = getBrand();
    $page = $request->query('page', 'profile');

    if ($page === 'profile') {
        $rules = [
            'brand_name'        => ['required', 'string', 'max:255'],
            // Jika brand_email ada di tabel owners gunakan ini:
            'brand_phone'       => ['nullable', 'string', 'max:20'],
            'brand_description' => ['nullable', 'string'], // Sesuai skema tabel
            'brand_logo'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'delete_logo'       => ['nullable', 'boolean'],
        ];

        $request->validate($rules);

        // Update data owner
        $owner->brand_name        = $request->brand_name;
        $owner->brand_phone       = $request->brand_phone;
        $owner->brand_description = $request->brand_description; // Tambahan sesuai view

        // --- Logika Logo ---
        if ($request->input('delete_logo') && $owner->brand_logo) {
            deleteImage($owner->brand_logo);
            $owner->brand_logo = null;
        }

        if ($request->hasFile('brand_logo')) {
            if ($owner->brand_logo) {
                deleteImage($owner->brand_logo);
            }
            $newLogoPath = uploadImage($request->file('brand_logo'), 'brands');
            if ($newLogoPath) {
                $owner->brand_logo = $newLogoPath;
            }
        }

        $message = 'Informasi Brand berhasil diperbarui!';

    } elseif ($page === 'bank') {
        $rules = [
            'bank_name'                => ['required', 'string', 'max:255'], // Diwajibkan agar tidak kosong
            'bank_account_number'      => ['required', 'string', 'max:50'],
            'bank_account_holder_name' => ['required', 'string', 'max:255'],
        ];

        $request->validate($rules);

        $owner->bank_name                = $request->bank_name;
        $owner->bank_account_number      = $request->bank_account_number;
        $owner->bank_account_holder_name = $request->bank_account_holder_name;

        $message = 'Informasi Bank berhasil diperbarui!';
    } else {
        return redirect()->back()->with('error', 'Halaman tidak valid!');
    }

    $owner->save();
    return redirect()->route('partner.brand.profile.edit', ['page' => $page])->with('success', $message);
}

}
