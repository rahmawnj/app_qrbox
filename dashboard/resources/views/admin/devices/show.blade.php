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
                <div class="panel-heading"><h4 class="panel-title">Informasi Dasar</h4></div>
                <div class="panel-body text-center">
                    <img src="{{ asset($device->outlet?->owner?->brand_logo ?? 'assets/img/default-user.png') }}"
                         class="img-circle img-thumbnail m-b-15" style="width: 120px; height: 120px; object-fit: cover;">
                    <h3 class="m-t-10">{{ $device->name }}</h3>
                    <p class="text-muted"><code>{{ $device->code }}</code></p>
                    <hr>
                    <table class="table table-profile text-left">
                        <tbody>
                            <tr><td class="field">Brand</td><td>
                                @if($device->outlet?->owner)
                                    <a href="{{ route('admin.owners.show', $device->outlet->owner) }}">{{ $device->outlet->owner->brand_name }}</a>
                                @else - @endif
                            </td></tr>
                            <tr><td class="field">Outlet</td><td>
                                @if($device->outlet)
                                    <a href="{{ route('admin.outlets.show', $device->outlet) }}">{{ $device->outlet->outlet_name }}</a>
                                @else - @endif
                            </td></tr>
                            <tr><td class="field">Tipe Service</td><td>{{ $device->serviceType->name ?? '-' }}</td></tr>
                            <tr><td class="field">Status Saat Ini</td><td>
                                <span class="label {{ $device->device_status === 'off' ? 'label-success' : 'label-danger' }}">
                                    {{ strtoupper($device->device_status) }}
                                </span>
                            </td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="panel-footer text-right">
                    <a href="{{ route('admin.devices.index') }}" class="btn btn-default m-r-5">Kembali</a>
                    <a href="{{ route('admin.devices.edit', $device) }}" class="btn btn-primary"><i class="fa fa-edit"></i> Sunting</a>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="panel panel-inverse">
                <div class="panel-heading"><h4 class="panel-title">Konfigurasi Menu & Harga (QRIS)</h4></div>
                <div class="table-responsive">
                    <table class="table table-hover table-valign-middle m-b-0">
                        <thead>
                            <tr><th width="10%">Option</th><th>Nama Menu</th><th>Type (Slug)</th><th>Harga (Gross)</th><th>Deskripsi</th></tr>
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
                                        <td colspan="4" class="text-center text-muted">Tidak dikonfigurasi</td>
                                    @endif
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel panel-inverse">
                <div class="panel-heading"><h4 class="panel-title">Informasi Bypass Terakhir</h4></div>
                <div class="panel-body">
                    @if($device->bypass_activation)
                        <div class="alert alert-warning">
                            <h5><i class="fa fa-info-circle"></i> Device sedang/pernah dibypass</h5>
                            <p><strong>Waktu Aktivasi:</strong> {{ CarbonCarbon::parse($device->bypass_activation)->format('d M Y H:i') }}</p>
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