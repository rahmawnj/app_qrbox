<?php

namespace App\Http\Controllers\Partner;

use App\Models\User;
use App\Models\Outlet;
use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Events\NotificationEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage; // Pastikan ini diimpor

class OutletController extends Controller
{
    public function list()
    {
        $outlets = getData()->outlets->get();

        return view('partner.outlets.list', compact('outlets'));
    }

    public function detail(Outlet $outlet, Request $request)
    {
        $currentTab = $request->query('page', 'detail');
        $serviceTypes = ServiceType::all();

        return view('partner.outlets.detail', compact('outlet', 'serviceTypes', 'currentTab'));
    }

   public function serviceType(Request $request, Outlet $outlet)
{
    // Define validation rules for services and new services
    $rules = [
        'services' => 'nullable|array',
        'services.*.name' => 'required|string|max:255',
        'services.*.member_price' => 'required|numeric|min:0',
        'services.*.non_member_price' => 'required|numeric|min:0',
        // Aturan validasi baru untuk kolom 'unit'
        'services.*.unit' => 'required|in:kg,pcs,liter,hour,unit',
        'services.*.service_type_ids' => 'nullable|array',
        'services.*.service_type_ids.*' => 'exists:service_types,id',
        'services.*._delete' => 'nullable|boolean',

        'new_services' => 'nullable|array',
        'new_services.*.name' => 'required|string|max:255',
        'new_services.*.member_price' => 'required|numeric|min:0',
        'new_services.*.non_member_price' => 'required|numeric|min:0',
        // Aturan validasi baru untuk kolom 'unit' pada layanan baru
        'new_services.*.unit' => 'required|in:kg,pcs,liter,hour,unit',
        'new_services.*.service_type_ids' => 'nullable|array',
        'new_services.*.service_type_ids.*' => 'exists:service_types,id',
    ];

    // Custom validation messages
    $messages = [
        'services.*.name.required' => 'Nama layanan tidak boleh kosong.',
        'services.*.member_price.required' => 'Harga member layanan tidak boleh kosong.',
        'services.*.member_price.numeric' => 'Harga member layanan harus berupa angka.',
        'services.*.member_price.min' => 'Harga member layanan tidak boleh negatif.',
        'services.*.non_member_price.required' => 'Harga non-member layanan tidak boleh kosong.',
        'services.*.non_member_price.numeric' => 'Harga non-member layanan harus berupa angka.',
        'services.*.non_member_price.min' => 'Harga non-member layanan tidak boleh negatif.',
        // Pesan validasi baru untuk 'unit'
        'services.*.unit.required' => 'Unit layanan tidak boleh kosong.',
        'services.*.unit.in' => 'Unit layanan tidak valid.',
        'services.*.service_type_ids.*.exists' => 'Salah satu tipe layanan yang dipilih tidak valid.',

        'new_services.*.name.required' => 'Nama layanan baru tidak boleh kosong.',
        'new_services.*.member_price.required' => 'Harga member layanan baru tidak boleh kosong.',
        'new_services.*.member_price.numeric' => 'Harga member layanan baru harus berupa angka.',
        'new_services.*.member_price.min' => 'Harga member layanan baru tidak boleh negatif.',
        'new_services.*.non_member_price.required' => 'Harga non-member layanan baru tidak boleh kosong.',
        'new_services.*.non_member_price.numeric' => 'Harga non-member layanan baru harus berupa angka.',
        'new_services.*.non_member_price.min' => 'Harga non-member layanan baru tidak boleh negatif.',
        // Pesan validasi baru untuk 'unit' pada layanan baru
        'new_services.*.unit.required' => 'Unit layanan baru tidak boleh kosong.',
        'new_services.*.unit.in' => 'Unit layanan baru tidak valid.',
        'new_services.*.service_type_ids.*.exists' => 'Salah satu tipe layanan baru yang dipilih tidak valid.',
    ];

    // Validate the incoming request data
    $validatedData = $request->validate($rules, $messages);

    // Start a database transaction to ensure atomicity
    DB::beginTransaction();

    try {
        // 1. Process Existing Services
        if (isset($validatedData['services'])) {
            foreach ($validatedData['services'] as $serviceId => $serviceData) {
                $service = $outlet->services()->find($serviceId);

                if ($service) {
                    if (isset($serviceData['_delete']) && $serviceData['_delete'] == 1) {
                        $service->delete();
                        $service->serviceTypes()->detach();
                    } else {
                        // Perbarui data service dengan kolom 'unit'
                        $service->name = $serviceData['name'];
                        $service->member_price = $serviceData['member_price'];
                        $service->non_member_price = $serviceData['non_member_price'];
                        $service->unit = $serviceData['unit']; // <-- Menambahkan pembaruan unit
                        $service->save();

                        $serviceTypeIds = $serviceData['service_type_ids'] ?? [];
                        $service->serviceTypes()->sync($serviceTypeIds);
                    }
                }
            }
        }

        // 2. Process New Services
        if (isset($validatedData['new_services'])) {
            foreach ($validatedData['new_services'] as $newServiceData) {
                $newService = new Service([
                    'name' => $newServiceData['name'],
                    'member_price' => $newServiceData['member_price'],
                    'non_member_price' => $newServiceData['non_member_price'],
                    'outlet_id' => $outlet->id,
                    'unit' => $newServiceData['unit'], // <-- Menambahkan data unit untuk layanan baru
                ]);
                $outlet->services()->save($newService);

                $newServiceTypeIds = $newServiceData['service_type_ids'] ?? [];
                $newService->serviceTypes()->attach($newServiceTypeIds);
            }
        }

        DB::commit();

        return redirect()->route('partner.outlets.detail', ['outlet' => $outlet->id, 'tab' => 'services'])
            ->with('success', 'Daftar layanan berhasil diperbarui!');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Error updating services for outlet {$outlet->id}: " . $e->getMessage());

        return redirect()->back()
            ->with('error', 'Gagal memperbarui daftar layanan. Silakan coba lagi.')
            ->withInput();
    }
}

  public function update(Request $request, Outlet $outlet)
{
    // Validasi Input sesuai Skema Database
    $validated = $request->validate([
        'outlet_name'  => 'required|string|max:255',
        'address'      => 'nullable|string|max:500',
        'city_name'    => 'nullable|string|max:255',
        'timezone'     => ['required', Rule::in(['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'])],
        'image'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'latitude'     => 'nullable|numeric',
        'longitude'    => 'nullable|numeric',
    ]);

    try {
        $data = [
            'outlet_name'  => $validated['outlet_name'],
            'address'      => $validated['address'],
            'city_name'    => $validated['city_name'],
            'timezone'     => $validated['timezone'],
        ];

        // 1. Handle Upload Gambar
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($outlet->image && Storage::disk('public')->exists($outlet->image)) {
                Storage::disk('public')->delete($outlet->image);
            }
            // Simpan gambar baru ke folder 'outlets'
            $data['image'] = $request->file('image')->store('outlets', 'public');
        }

        // 2. Handle LatLong (Disimpan sebagai JSON sesuai skema)
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $data['latlong'] = [
                'latitude'  => (float) $request->latitude,
                'longitude' => (float) $request->longitude,
            ];
        }

        // 3. Update Database
        $outlet->update($data);

        return redirect()->route('partner.outlets.detail', [
            'outlet' => $outlet->id,
            'tab'    => 'edit-profile'
        ])->with('success', 'Profil ' . $outlet->outlet_name . ' berhasil diperbarui.');

    } catch (\Exception $e) {
        return back()->withInput()->with('error', 'Gagal memperbarui profil: ' . $e->getMessage());
    }
}


      public function destroy(Outlet $outlet)
    {
        try {
            $outlet_name = $outlet->outlet_name;

            // Lakukan soft delete pada outlet
            $outlet->delete();

            // Ambil semua user dengan role 'admin'
            $admins = User::where('role', 'admin')->get();
            event(new NotificationEvent(
                recipients: $admins,
                title: '🗑️ Outlet Dihapus', // Ikon ditambahkan di sini
                message: 'Outlet "' . $outlet_name . '" telah berhasil dihapus.',
                url: route('admin.outlets.index') // Arahkan ke daftar outlet
            ));

            return redirect()->route('partner.outlets.list')
                ->with('success', 'Outlet "' . $outlet_name . '" berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus outlet: ' . $e->getMessage());
            return redirect()->route('partner.outlets.list')
                ->with('error', 'Gagal menghapus outlet: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Outlet $outlet)
    {
        $feature = getData();
        if (!$feature->can('partner.outlets.update-status')) {
            abort(403, 'Anda tidak memiliki izin.');
        }

        $statusText = $request->status ? 'buka' : 'tutup';
        $outlet->status = $request->status;
        $outlet->save();

        return redirect()->back()->with('success', "Status outlet `{$outlet->outlet_name}` berhasil diubah menjadi `{$statusText}`.");
    }

    public function store(Request $request)
    {
        $request->validate([
            'outlet_name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'city_name' => 'nullable|string|max:255', // <-- Tambahkan validasi
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'timezone' => 'required',
            'status' => 'boolean',
        ]);

        try {
            $outlet = new Outlet();
            $outlet->owner_id = getBrand()->id;
            $outlet->outlet_name = $request->outlet_name;
            $outlet->code =  $this->generateUniqueCode();
            $outlet->address = $request->address;
            $outlet->city_name = $request->city_name; // <-- Simpan data city_name
            $outlet->phone_number = $request->phone_number;
            $outlet->timezone = $request->timezone;
            $outlet->save();

            return redirect()->route('partner.outlets.list')->with('success', 'Outlet "' . $outlet->outlet_name . '" berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('Error adding new outlet: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan outlet. Silakan coba lagi. Error: ' . $e->getMessage());
        }
    }

    private function generateUniqueCode()
    {
        do {
            $code = 'OUT-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        } while (Outlet::where('code', $code)->exists());

        return $code;
    }
}
