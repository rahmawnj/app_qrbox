<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Outlet;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    /**
     * Tampilkan daftar semua layanan.
     * Mengambil semua layanan dengan relasi outlet dan tipe layanan (ServiceType).
     */
    public function index()
    {
        try {
            $services = Service::with(['outlet', 'serviceTypes'])->get();
            return view('admin.services.index', compact('services'));
        } catch (\Exception $e) {
            Log::error("Error loading services for admin: " . $e->getMessage());
            return redirect()->route('admin.dashboard')
                ->with('error', 'Gagal memuat daftar layanan. Silakan coba lagi.');
        }
    }

    /**
     * Tampilkan form untuk membuat layanan baru.
     * Mengambil semua outlet dan tipe layanan untuk dropdown.
     */
    public function create()
    {
        try {
            $outlets = Outlet::all();
            $serviceTypes = ServiceType::all();
            return view('admin.services.create', compact('outlets', 'serviceTypes'));
        } catch (\Exception $e) {
            Log::error("Error loading create service form: " . $e->getMessage());
            return redirect()->route('admin.services.index')
                ->with('error', 'Gagal memuat formulir pembuatan layanan.');
        }
    }

    /**
     * Simpan layanan baru ke database.
     * Menggunakan transaksi untuk memastikan data tersimpan dengan benar.
     */
    public function store(Request $request)
    {
        // Validasi data yang masuk
        $request->validate([
            'name'             => 'required|string|max:255',
            'unit'             => 'required|string|in:kg,pcs,liter,hour,unit',
            'member_price'     => 'required|numeric|min:0',
            'non_member_price' => 'required|numeric|min:0',
            'outlet_id'        => 'required|exists:outlets,id',
            'service_type_ids' => 'nullable|array',
            'service_type_ids.*' => 'exists:service_types,id',
        ], [
            'name.required' => 'Nama layanan tidak boleh kosong.',
            'unit.required' => 'Satuan layanan tidak boleh kosong.',
            'unit.in'       => 'Satuan layanan tidak valid.',
            'member_price.required' => 'Harga member tidak boleh kosong.',
            'non_member_price.required' => 'Harga non-member tidak boleh kosong.',
            'outlet_id.required' => 'Outlet harus dipilih.',
            'outlet_id.exists' => 'Outlet yang dipilih tidak valid.',
            'service_type_ids.*.exists' => 'Salah satu tipe layanan yang dipilih tidak valid.',
        ]);

        DB::beginTransaction();

        try {
            // Buat layanan baru
            $service = Service::create([
                'name'             => $request->name,
                'unit'             => $request->unit, // Menambahkan kolom unit
                'member_price'     => $request->member_price,
                'non_member_price' => $request->non_member_price,
                'outlet_id'        => $request->outlet_id,
            ]);

            // Hubungkan layanan dengan tipe layanan yang dipilih (jika ada)
            if ($request->filled('service_type_ids')) {
                $service->serviceTypes()->attach($request->input('service_type_ids'));
            }

            DB::commit();

            return redirect()->route('admin.services.index')
                ->with('success', 'Layanan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error storing service: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menambahkan layanan. Silakan coba lagi.')
                ->withInput();
        }
    }

    /**
     * Tampilkan form untuk mengedit layanan yang sudah ada.
     */
    public function edit(Service $service)
    {
        try {
            $outlets = Outlet::all();
            $serviceTypes = ServiceType::all();
            // Ambil ID tipe layanan yang terkait dengan layanan saat ini
            $selectedServiceTypeIds = $service->serviceTypes->pluck('id')->toArray();

            return view('admin.services.edit', compact('service', 'outlets', 'serviceTypes', 'selectedServiceTypeIds'));
        } catch (\Exception $e) {
            Log::error("Error loading edit service form for service ID {$service->id}: " . $e->getMessage());
            return redirect()->route('admin.services.index')
                ->with('error', 'Gagal memuat formulir edit layanan.');
        }
    }

    /**
     * Perbarui layanan yang sudah ada di database.
     */
    public function update(Request $request, Service $service)
    {
        // Validasi data yang masuk
        $request->validate([
            'name'             => 'required|string|max:255',
            'unit'             => 'required|string|in:kg,pcs,liter,hour,unit',
            'member_price'     => 'required|numeric|min:0',
            'non_member_price' => 'required|numeric|min:0',
            'outlet_id'        => 'required|exists:outlets,id',
            'service_type_ids' => 'nullable|array',
            'service_type_ids.*' => 'exists:service_types,id',
        ], [
            'name.required' => 'Nama layanan tidak boleh kosong.',
            'unit.required' => 'Satuan layanan tidak boleh kosong.',
            'unit.in'       => 'Satuan layanan tidak valid.',
            'member_price.required' => 'Harga member tidak boleh kosong.',
            'non_member_price.required' => 'Harga non-member tidak boleh kosong.',
            'outlet_id.required' => 'Outlet harus dipilih.',
            'outlet_id.exists' => 'Outlet yang dipilih tidak valid.',
            'service_type_ids.*.exists' => 'Salah satu tipe layanan yang dipilih tidak valid.',
        ]);

        DB::beginTransaction();

        try {
            // Perbarui data layanan
            $service->update([
                'name'             => $request->name,
                'unit'             => $request->unit, // Menambahkan kolom unit
                'member_price'     => $request->member_price,
                'non_member_price' => $request->non_member_price,
                'outlet_id'        => $request->outlet_id,
            ]);

            // Sinkronkan relasi tipe layanan menggunakan metode sync()
            $serviceTypeIds = $request->input('service_type_ids', []);
            $service->serviceTypes()->sync($serviceTypeIds);

            DB::commit();

            return redirect()->route('admin.services.index')
                ->with('success', 'Layanan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating service ID {$service->id}: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal memperbarui layanan. Silakan coba lagi.')
                ->withInput();
        }
    }

    /**
     * Hapus layanan dari database.
     */
    public function destroy(Service $service)
    {
        DB::beginTransaction();

        try {
            // Hapus semua relasi di tabel pivot terlebih dahulu
            $service->serviceTypes()->detach();

            // Hapus layanan
            $service->delete();

            DB::commit();

            return redirect()->route('admin.services.index')
                ->with('success', 'Layanan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting service ID {$service->id}: " . $e->getMessage());
            return redirect()->route('admin.services.index')
                ->with('error', 'Gagal menghapus layanan. Silakan coba lagi.');
        }
    }
}
