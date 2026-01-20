@props([
    'items' => ['Admin', 'Manajemen Outlet', 'Detail Outlet'],
    'title' => 'Detail Outlet',
    'subtitle' => 'Informasi lengkap data dan konfigurasi outlet'
])
@extends('layouts.dashboard.app')

@section('content')
<x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

<div class="row">
    <div class="col-xl-4 col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center">
                <img src="{{ $outlet->image ? asset($outlet->image) : asset('assets/img/default-img.png') }}"
                     class="img-fluid rounded-3 mb-3" style="max-height: 250px; width: 100%; object-fit: cover;">

                <h4 class="mb-1">{{ $outlet->outlet_name }}</h4>
                <p class="text-muted mb-3"><code>{{ $outlet->code }}</code></p>

                <div class="d-flex justify-content-center gap-2 mb-3">
                    <span class="badge {{ $outlet->status ? 'bg-success' : 'bg-danger' }} px-3 py-2">
                        {{ $outlet->status ? 'Status: Aktif' : 'Status: Nonaktif' }}
                    </span>
                    <span class="badge bg-primary px-3 py-2">{{ $outlet->timezone }}</span>
                </div>

                <hr>

                <div class="text-start">
                    <label class="small text-muted d-block">Brand Owner</label>
                    <div class="d-flex align-items-center mt-1">
                        <img src="{{ asset($outlet->owner->brand_logo) }}" class="rounded-circle me-2" style="width: 40px; height: 40px;">
                        <div>
                            <div class="fw-bold">{{ $outlet->owner->brand_name }}</div>
                            <div class="small text-muted">{{ $outlet->owner->brand_email }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8 col-lg-7">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <h4 class="panel-title"><i class="fas fa-map-marked-alt me-2"></i>Lokasi & Alamat</h4>
            </div>
            <div class="panel-body">
                <table class="table table-profile">
                    <tbody>
                        <tr>
                            <td class="field">Kota</td>
                            <td>{{ $outlet->city_name }}</td>
                        </tr>
                        <tr>
                            <td class="field">Alamat Lengkap</td>
                            <td>{{ $outlet->address }}</td>
                        </tr>
                        <tr>
                            <td class="field">Koordinat</td>
                            <td>
                                @php $geo = is_array($outlet->latlong) ? $outlet->latlong : json_decode($outlet->latlong, true); @endphp
                                <span class="badge bg-light text-dark border">Lat: {{ $geo['lat'] ?? '0' }}</span>
                                <span class="badge bg-light text-dark border">Long: {{ $geo['lon'] ?? '0' }}</span>
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $geo['lat'] ?? 0 }},{{ $geo['lon'] ?? 0 }}"
                                   target="_blank" class="btn btn-xs btn-outline-primary ms-2">
                                    <i class="fas fa-external-link-alt"></i> Buka Maps
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel panel-inverse">
            <div class="panel-heading">
                <h4 class="panel-title"><i class="fas fa-wallet me-2"></i>Konfigurasi Biaya Layanan</h4>
            </div>
            <div class="panel-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="p-3 border rounded bg-light">
                            <div class="text-muted small mb-1">Service Fee (%)</div>
                            <h4 class="mb-0 text-primary">{{ number_format($outlet->service_fee_percentage * 100, 1) }}%</h4>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 border rounded bg-light">
                            <div class="text-muted small mb-1">Min. Fee Bulanan</div>
                            <h4 class="mb-0 text-dark">Rp {{ number_format($outlet->min_monthly_service_fee, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 border rounded bg-light">
                            <div class="text-muted small mb-1">Deposit Perangkat</div>
                            <h4 class="mb-0 text-dark">Rp {{ number_format($outlet->device_deposit_price, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.outlets.edit', $outlet) }}" class="btn btn-primary px-4">
                <i class="fas fa-edit me-1"></i> Edit Data
            </a>
            <a href="{{ route('admin.outlets.index') }}" class="btn btn-default px-4">Kembali</a>
        </div>
    </div>
</div>
@endsection
