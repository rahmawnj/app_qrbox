<?php

namespace App\Http\Controllers\Admin;

use App\Models\ServiceType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ServiceTypeController extends Controller
{
    public function index()
    {
        try {
            $serviceTypes = ServiceType::all();

            return view('admin.service_types.index', compact('serviceTypes'));
        } catch (\Exception $e) {
            return redirect()->route('admin.service_types.index')
                ->with('error', $e->getMessage());
        }
    }

    public function create()
    {
        try {
            return view('admin.service_types.create');
        } catch (\Exception $e) {
            return redirect()->route('admin.service_types.index')
                ->with('error', $e->getMessage());
        }
    }

public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:service_types,name',
            'items' => 'required|array|min:1',
            'items.*.key' => 'required|string',
            'items.*.label' => 'required|string',
        ]);

        try {
            // Mapping data agar sesuai format JSON di migrasi
            $formattedItems = collect($request->items)->map(function($item) {
                return [
                    'key' => strtolower(str_replace(' ', '_', $item['key'])),
                    'label' => $item['label'],
                    'has_duration' => isset($item['has_duration']) ? true : false,
                ];
            })->toArray();

            ServiceType::create([
                'name' => $request->name,
                'items' => $formattedItems,
            ]);

            return redirect()->route('admin.service_types.index')->with('success', 'Service Type berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, ServiceType $serviceType)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:service_types,name,' . $serviceType->id,
            'items' => 'required|array|min:1',
            'items.*.key' => 'required|string',
            'items.*.label' => 'required|string',
        ]);

        try {
            $formattedItems = collect($request->items)->map(function($item) {
                return [
                    'key' => strtolower(str_replace(' ', '_', $item['key'])),
                    'label' => $item['label'],
                    'has_duration' => isset($item['has_duration']) ? true : false,
                ];
            })->toArray();

            $serviceType->update([
                'name' => $request->name,
                'items' => $formattedItems,
            ]);

            return redirect()->route('admin.service_types.index')->with('success', 'Service Type diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }    public function edit(ServiceType $serviceType)
    {
        try {
            return view('admin.service_types.edit', compact('serviceType'));
        } catch (\Exception $e) {
            return redirect()->route('admin.service_types.index')
                ->with('error', $e->getMessage());
        }
    }
public function show(ServiceType $serviceType)
{
    try {
        return view('admin.service_types.show', compact('serviceType'));
    } catch (\Exception $e) {
        return redirect()->route('admin.service_types.index')
            ->with('error', 'Gagal memuat detail: ' . $e->getMessage());
    }
}

    public function destroy(ServiceType $serviceType)
    {
        try {
            $serviceType->delete();
            return redirect()->route('admin.service_types.index')
                ->with('success', 'Service Type berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.service_types.index')
                ->with('error', $e->getMessage());
        }
    }
}
