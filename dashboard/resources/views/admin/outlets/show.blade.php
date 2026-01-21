@props([
    'items' => ['Admin', 'Manajemen Outlet', 'Detail Outlet'],
    'title' => 'Detail Outlet',
    'subtitle' => 'Informasi lengkap data dan konfigurasi outlet'
])
@extends('layouts.dashboard.app')

@section('content')
<x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

{{-- Alert Success --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    {{-- Kolom Kiri: Profil Singkat --}}
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

    {{-- Kolom Kanan: Detail --}}
    <div class="col-xl-8 col-lg-7">
        {{-- Panel Lokasi --}}
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
                                <a href="https://www.google.com/maps?q={{ $geo['lat'] ?? 0 }},{{ $geo['lon'] ?? 0 }}"
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
        <h4 class="panel-title"><i class="fas fa-microchip me-2"></i>Konfigurasi Perangkat Outlet</h4>
    </div>
    <div class="panel-body">
        <div class="alert alert-warning py-3 border-0 shadow-sm mb-4">
            <div class="d-flex">
                <i class="fas fa-exclamation-triangle fa-2x me-3 text-warning"></i>
                <div>
                    <h6 class="alert-heading fw-bold mb-1">Peringatan Akses Perangkat</h6>
                    <p class="mb-0 small text-dark">
                        Token ini adalah <strong>kunci akses utama</strong> untuk semua perangkat (Hardware/Device) di outlet ini.
                        Jika Anda me-regenerate token, Anda <strong>wajib mengupdate token baru</strong> di seluruh device agar tetap terhubung.
                    </p>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="fw-bold mb-2 text-muted">Master Device Token</label>
            <div class="input-group input-group-lg">
                <input type="text" id="device_token_val" class="form-control bg-light fw-bold text-primary font-monospace"
                       value="{{ $outlet->device_token ?? 'TOKEN_BELUM_DI_SET' }}" readonly>
                <button class="btn btn-outline-secondary" type="button" onclick="copyToken()" title="Salin Token">
                    <i class="fas fa-copy"></i>
                </button>
                <button class="btn btn-danger" type="button" data-bs-toggle="modal" data-bs-target="#modalConfirmPassword">
                    <i class="fas fa-sync-alt me-1"></i> Ganti Token Baru
                </button>
            </div>
            <small class="text-muted mt-2 d-block small">Terakhir diperbarui: <span class="fw-bold">{{ $outlet->updated_at->diffForHumans() }}</span></small>
        </div>
    </div>
</div>
      
        <div class="d-flex gap-2 mb-4 mt-3">
            <a href="{{ route('admin.outlets.edit', $outlet) }}" class="btn btn-primary px-4">
                <i class="fas fa-edit me-1"></i> Edit Data
            </a>
            <a href="{{ route('admin.outlets.index') }}" class="btn btn-default px-4">Kembali</a>
        </div>
    </div>
</div>

<div class="modal fade shadow-lg" id="modalConfirmPassword" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <form action="{{ route('outlets.regenerate-token', $outlet->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-dark text-white border-0">
                    <h5 class="modal-title"><i class="fas fa-user-shield me-2 text-danger"></i>Verifikasi Otoritas Admin</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger mb-4">
                        <small><i class="fas fa-exclamation-circle me-1"></i> Perangkat yang sedang aktif akan segera kehilangan akses setelah token diperbarui.</small>
                    </div>

                    <p class="mb-3 text-dark">Silakan masukkan password akun Anda untuk mengonfirmasi perubahan <strong>Master Device Token</strong> pada outlet ini:</p>

                    <div class="form-group">
                        <label class="mb-2 fw-bold text-muted small">PASSWORD KONFIRMASI</label>
                        <input type="password" name="password"
                               class="form-control form-control-lg @if(session('error_password')) is-invalid @endif"
                               placeholder="******" required autofocus>

                        @if(session('error_password'))
                            <div class="invalid-feedback fw-bold">
                                {{ session('error_password') }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-default" data-bs-dismiss="modal">Batalkan</button>
                    <button type="submit" class="btn btn-danger px-4 shadow-sm">
                        <i class="fas fa-check-double me-1"></i> Konfirmasi & Generate Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Fungsi Copy ke Clipboard
    function copyToken() {
        const tokenInput = document.getElementById('device_token_val');
        tokenInput.select();
        tokenInput.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(tokenInput.value);
        alert("Token berhasil disalin!");
    }

    // Jika ada error password setelah submit, otomatis buka modalnya lagi
    @if(session('error_password'))
        $(document).ready(function() {
            var myModal = new bootstrap.Modal(document.getElementById('modalConfirmPassword'));
            myModal.show();
        });
    @endif
</script>
@endpush
