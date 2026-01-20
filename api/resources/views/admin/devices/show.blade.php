@props([
    'items' => ['Admin', 'Manajemen Device', 'Detail Device'],
    'title' => 'Detail Perangkat',
    'subtitle' => 'Informasi lengkap dan konfigurasi harga device',
])
@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    <div class="row">
        <div class="col-md-4">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Informasi Dasar</h4>
                </div>
                <div class="panel-body text-center">
                    <div class="mb-3">
                        <img src="{{ asset($device->outlet->owner->brand_logo ?? 'assets/img/default-user.png') }}"
                             class="img-thumbnail rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                    </div>
                    <h3 class="m-t-10">{{ $device->name }}</h3>
                    <p class="text-muted">{{ $device->code }}</p>
                    <hr>
                    <div class="text-left">
                        <p><strong>Brand:</strong> {{ $device->outlet->owner->brand_name ?? '-' }}</p>
                        <p><strong>Outlet:</strong> {{ $device->outlet->outlet_name ?? '-' }}</p>
                        <p><strong>Tipe Service:</strong> {{ $device->serviceType->name ?? '-' }}</p>
                        <p><strong>Status Saat Ini:</strong>
                            <span class="badge {{ $device->device_status === 'off' ? 'badge-success' : 'badge-danger' }}">
                                {{ strtoupper($device->device_status) }}
                            </span>
                        </p>
                    </div>
                </div>
                <div class="panel-footer text-right">
                    <a href="{{ route('admin.devices.edit', $device) }}" class="btn btn-primary btn-sm">Sunting</a>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Konfigurasi Menu & Harga (QRIS)</h4>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-valign-middle">
                        <thead>
                            <tr class="bg-light">
                                <th width="10%">Option</th>
                                <th>Nama Menu</th>
                                <th>Type (Slug)</th>
                                <th>Harga (Gross)</th>
                                <th>Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 1; $i <= 4; $i++)
                                @php
                                    $opt = $device->{"option_$i"};
                                    $data = is_string($opt) ? json_decode($opt, true) : $opt;
                                @endphp
                                <tr>
                                    <td class="text-center font-weight-bold">{{ $i }}</td>
                                    @if($data)
                                        <td>{{ $data['name'] ?? '-' }}</td>
                                        <td><code>{{ $data['type'] ?? '-' }}</code></td>
                                        <td>Rp {{ number_format($data['price'] ?? 0, 0, ',', '.') }}</td>
                                        <td class="small text-muted">{{ $data['description'] ?? '-' }}</td>
                                    @else
                                        <td colspan="4" class="text-center text-muted italic">Tidak dikonfigurasi</td>
                                    @endif
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Informasi Bypass Terakhir</h4>
                </div>
                <div class="panel-body">
                    @if($device->bypass_activation)
                        <div class="alert alert-yellow">
                            <h5><i class="fa fa-info-circle"></i> Device sedang/pernah dibypass</h5>
                            <p><strong>Waktu Aktivasi:</strong> {{ \Carbon\Carbon::parse($device->bypass_activation)->format('d M Y H:i') }}</p>
                            <p><strong>Catatan Bypass:</strong> {{ $device->bypass_note ?? 'Tidak ada catatan' }}</p>
                        </div>
                    @else
                        <p class="text-muted">Belum ada riwayat bypass pada perangkat ini.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
