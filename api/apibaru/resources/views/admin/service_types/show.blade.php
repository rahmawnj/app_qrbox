@props([
    'items' => ['Admin', 'Tipe Layanan', 'Detail'],
    'title' => 'Detail Tipe Layanan',
    'subtitle' => 'Konfigurasi teknis untuk perangkat hardware'
])
@extends('layouts.dashboard.app')

@section('content')
<x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

<div class="panel panel-inverse">
    <div class="panel-heading">
        <h4 class="panel-title">{{ $serviceType->name }} - System Configuration</h4>
        <div class="panel-heading-btn">
            <a href="{{ route('admin.service_types.index') }}" class="btn btn-xs btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
            <a href="{{ route('admin.service_types.edit', $serviceType->id) }}" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> Edit</a>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            {{-- Bagian Kiri: Ringkasan --}}
            <div class="col-md-4 border-end">
                <h5 class="mb-3 text-primary"><i class="fa fa-info-circle"></i> Informasi Utama</h5>
                <div class="mb-3">
                    <label class="small text-muted d-block">Nama Layanan</label>
                    <span class="fw-bold fs-15px">{{ $serviceType->name }}</span>
                </div>
                <div class="mb-3">
                    <label class="small text-muted d-block">System Slug</label>
                    <code>{{ Str::snake($serviceType->name) }}</code>
                </div>
                <div class="mb-3">
                    <label class="small text-muted d-block">Total Slot Terkonfigurasi</label>
                    <span class="badge bg-dark">{{ count($serviceType->items) }} Item</span>
                </div>
                <div class="mb-0">
                    <label class="small text-muted d-block">Terdaftar Pada</label>
                    <span>{{ $serviceType->created_at->format('d F Y, H:i') }}</span>
                </div>

                <hr class="my-4 opacity-5">

                <div class="alert alert-yellow mb-0 p-2">
                    <h6 class="mb-1 small fw-bold"><i class="fa fa-microchip"></i> Hardware Sync</h6>
                    <p class="small mb-0 opacity-8">Key yang terdaftar harus didefinisikan pada firmware perangkat agar relay/perintah dapat dikenali.</p>
                </div>
            </div>

            {{-- Bagian Kanan: Tabel Detail Item --}}
            <div class="col-md-8">
                <h5 class="mb-3 text-primary ps-md-3"><i class="fa fa-list"></i> Mapping Slot Menu</h5>
                <div class="ps-md-3">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="text-dark">
                                    <th width="5%" class="text-center">#</th>
                                    <th>Key Perangkat</th>
                                    <th>Label Layar</th>
                                    <th class="text-center">Fitur Timer</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($serviceType->items as $index => $item)
                                <tr>
                                    <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="badge bg-dark font-monospace" style="letter-spacing: 1px;">
                                            {{ $item['key'] }}
                                        </span>
                                    </td>
                                    <td class="fw-bold text-dark">{{ $item['label'] }}</td>
                                    <td class="text-center">
                                        @if($item['has_duration'])
                                            <span class="badge bg-success-transparent-2 text-success border border-success px-2">
                                                <i class="fa fa-clock me-1"></i> Enabled
                                            </span>
                                        @else
                                            <span class="badge bg-light text-muted border px-2">
                                                <i class="fa fa-ban me-1"></i> Disabled
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .fs-15px { font-size: 15px; }
    .bg-success-transparent-2 { background-color: rgba(0, 172, 172, 0.1); }
</style>
@endpush
