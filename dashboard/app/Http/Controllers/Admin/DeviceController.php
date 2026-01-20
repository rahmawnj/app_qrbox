<?php

namespace App\Http\Controllers\Admin;

use App\Models\Device;
use App\Models\Outlet;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DeviceController extends Controller
{
    public function index()
    {
        try {
            $devices = Device::with(['outlet.owner', 'serviceType'])->latest()->get();
            return view('admin.devices.index', compact('devices'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function create()
    {
        $serviceTypes = ServiceType::all();
        $outlets = Outlet::with('owner')->where('status', 1)->get();
        return view('admin.devices.create', compact('outlets', 'serviceTypes'));
    }

public function store(Request $request)
{
    $request->validate([
        'name'            => 'required|string|max:255',
        'outlet_id'       => 'required|exists:outlets,id',
        'service_type_id' => 'required|exists:service_types,id',
        'menu'            => 'required|array|min:4|max:4',
    ]);

    try {
        $options = [];
        for ($i = 0; $i < 4; $i++) {
            $item = $request->menu[$i] ?? null;

            // Cek apakah slot diaktifkan oleh JavaScript (punya type)
            $isActive = is_array($item) && !empty($item['type']);

            $options[$i] = [
                'name'        => $isActive ? ($item['name'] ?? 'Menu ' . ($i + 1)) : '',
                'price'       => $isActive ? (float)($item['price'] ?? 0) : 0,
                'duration'    => $isActive ? (int)($item['duration'] ?? 0) : 0,
                'description' => $isActive ? ($item['description'] ?? '') : '',
                'type'        => $isActive ? $item['type'] : 'disabled',
                'active'      => $isActive, // Status eksplisit
            ];
        }

        DB::transaction(function () use ($request, $options) {
            $device = Device::create([
                'name'            => $request->name,
                'outlet_id'       => $request->outlet_id,
                'device_status'   => 'off',
                'service_type_id' => $request->service_type_id,
                'option_1'        => $options[0],
                'option_2'        => $options[1],
                'option_3'        => $options[2],
                'option_4'        => $options[3],
                'code'            => $this->generateUniqueCode(),
            ]);
        });

        return redirect()->route('admin.devices.index')->with('success', 'Device berhasil ditambahkan');
    } catch (\Exception $e) {
        return redirect()->back()->withInput()->with('error', 'Gagal: ' . $e->getMessage());
    }
}
    public function edit(Device $device)
    {
        $outlets = Outlet::with('owner')->get();
        // Mengambil menu settings (sudah otomatis array jika di-cast di Model)
        $menuSettings = $device->menu_settings;
$serviceTypes = ServiceType::all();
    // dd($device);

        return view('admin.devices.edit', compact('device','serviceTypes', 'outlets', 'menuSettings'));
    }


public function show(Device $device)
{
    // Eager load relasi agar tidak query berulang kali (N+1 Problem)
    $device->load(['outlet.owner', 'serviceType']);

    // Pastikan path view sesuai dengan lokasi file yang kamu buat tadi
    return view('admin.devices.show', compact('device'));
}

public function update(Request $request, Device $device)
{
    $request->validate([
        'name'            => 'required|string|max:255',
        'code'            => [
            'required',
            'string',
            'max:50',
            Rule::unique('devices', 'code')->ignore($device->id)
        ],
        'outlet_id'       => 'required|exists:outlets,id',
        'service_type_id' => 'required|exists:service_types,id',
        'menu'            => 'required|array|min:4|max:4',
    ]);

    try {
        $options = [];
        for ($i = 0; $i < 4; $i++) {
            $item = $request->menu[$i] ?? null;

            // Slot dianggap aktif jika input 'type' dikirim oleh JavaScript
            $isActive = is_array($item) && !empty($item['type']);

            $options[$i] = [
                'name'        => $isActive ? ($item['name'] ?? 'Menu ' . ($i + 1)) : '',
                'price'       => $isActive ? (float)($item['price'] ?? 0) : 0,
                'duration'    => $isActive ? (int)($item['duration'] ?? 0) : 0,
                'description' => $isActive ? ($item['description'] ?? '') : '',
                'type'        => $isActive ? $item['type'] : 'disabled',
                'active'      => $isActive,
            ];
        }

        $device->update([
            'name'            => $request->name,
            'code'            => $request->code,
            'outlet_id'       => $request->outlet_id,
            'service_type_id' => $request->service_type_id,
            'option_1'        => $options[0],
            'option_2'        => $options[1],
            'option_3'        => $options[2],
            'option_4'        => $options[3],
        ]);

        return redirect()->route('admin.devices.index')
            ->with('success', 'Device ' . $device->code . ' berhasil diperbarui');
    } catch (\Exception $e) {
        return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}
    public function destroy(Device $device)
    {
        try {
            $device->delete();
            return redirect()->route('admin.devices.index')->with('success', 'Device berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    private function generateUniqueCode()
    {
        do {
            $code = 'DEV-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        } while (Device::where('code', $code)->exists());
        return $code;
    }
}
