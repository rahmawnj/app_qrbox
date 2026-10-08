@props([
    'items' => ['Admin', 'Manajemen Outlet', 'Detail Outlet'],
    'title' => 'Detail Outlet',
    'subtitle' => 'Informasi lengkap data dan konfigurasi outlet'
])
@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="row">
        <div class="col-xl-4 col-lg-5">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Informasi Outlet</h4>
                </div>
                <div class="panel-body text-center">
                    <img src="{{ $outlet->image ? asset($outlet->image) : asset('assets/img/default-img.png') }}"
                         class="img-thumbnail m-b-15"
                         style="width: 100%; max-height: 230px; object-fit: cover;">
                    <h3 class="m-t-5">{{ $outlet->outlet_name }}</h3>
                    <p class="text-muted"><code>{{ $outlet->code }}</code></p>
                    <p>
                        <span class="label {{ $outlet->status ? 'label-success' : 'label-danger' }}">
                            {{ $outlet->status ? 'Aktif' : 'Nonaktif' }}
                        </span>
                        <span class="label label-primary">{{ $outlet->timezone }}</span>
                    </p>
                    <hr>
                    <div class="text-left">
                        <p class="m-b-10 text-muted">Brand Owner</p>
                        @if($outlet->owner)
                            <a href="{{ route('admin.owners.show', $outlet->owner) }}" class="d-flex align-items-center text-decoration-none">
                                <img src="{{ $outlet->owner->brand_logo ? asset($outlet->owner->brand_logo) : asset('assets/img/default-user.png') }}"
                                     class="img-circle m-r-10" style="width: 40px; height: 40px; object-fit: cover;">
                                <span>
                                    <strong>{{ $outlet->owner->brand_name }}</strong><br>
                                    <small class="text-muted">{{ $outlet->owner->brand_email }}</small>
                                </span>
                            </a>
                        @else
                            <span class="text-muted">Belum terhubung</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title"><i class="fas fa-map-marked-alt m-r-5"></i> Lokasi & Alamat</h4>
                </div>
                <div class="panel-body">
                    <table class="table table-profile">
                        <tbody>
                            <tr><td class="field">Kota</td><td>{{ $outlet->city_name ?? '-' }}</td></tr>
                            <tr><td class="field">Alamat Lengkap</td><td>{{ $outlet->address ?? '-' }}</td></tr>
                            <tr>
                                <td class="field">Koordinat</td>
                                <td>
                                    @php $geo = is_array($outlet->latlong) ? $outlet->latlong : json_decode($outlet->latlong, true); @endphp
                                    <span class="label label-default m-r-5">Lat: {{ $geo['lat'] ?? '0' }}</span>
                                    <span class="label label-default m-r-5">Long: {{ $geo['lon'] ?? '0' }}</span>
                                    <a href="https://www.google.com/maps?q={{ $geo['lat'] ?? 0 }},{{ $geo['lon'] ?? 0 }}"
                                       target="_blank" class="btn btn-xs btn-primary">
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
                    <h4 class="panel-title"><i class="fas fa-microchip m-r-5"></i> Konfigurasi Perangkat Outlet</h4>
                </div>
                <div class="panel-body">
                    <div class="alert alert-warning">
                        <strong><i class="fas fa-exclamation-triangle"></i> Peringatan:</strong>
                        Token ini adalah kunci akses utama untuk semua device di outlet ini.
                        Jika di-regenerate, token pada seluruh device harus diperbarui.
                    </div>
                    <div class="form-group">
                        <label>Master Device Token</label>
                        <div class="input-group">
                            <input type="text" id="device_token_val" class="form-control" value="{{ $outlet->device_token ?? 'TOKEN_BELUM_DI_SET' }}" readonly>
                            <span class="input-group-btn">
                                <button class="btn btn-default" type="button" onclick="copyToken()" title="Salin Token"><i class="fas fa-copy"></i></button>
                                <button class="btn btn-danger" type="button" data-toggle="modal" data-target="#modalConfirmPassword">
                                    <i class="fas fa-sync-alt"></i> Ganti Token Baru
                                </button>
                            </span>
                        </div>
                        <small class="text-muted">Terakhir diperbarui: {{ $outlet->updated_at ? $outlet->updated_at->diffForHumans() : '-' }}</small>
                    </div>
                </div>
            </div>

            <div class="panel panel-inverse">
                <div class="panel-footer text-right">
                    <a href="{{ route('admin.outlets.index') }}" class="btn btn-default m-r-5">Kembali</a>
                    <a href="{{ route('admin.outlets.edit', $outlet) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Data
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalConfirmPassword" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('outlets.regenerate-token', $outlet->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title"><i class="fas fa-user-shield text-danger"></i> Verifikasi Otoritas Admin</h5>
                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <small><i class="fas fa-exclamation-circle"></i> Device aktif akan kehilangan akses setelah token diperbarui.</small>
                        </div>
                        <p>Masukkan password akun Anda untuk mengonfirmasi perubahan <strong>Master Device Token</strong>.</p>
                        <div class="form-group">
                            <label>Password Konfirmasi</label>
                            <input type="password" name="password" class="form-control @if(session('error_password')) is-invalid @endif" placeholder="******" required>
                            @if(session('error_password'))
                                <div class="text-danger m-t-5">{{ session('error_password') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batalkan</button>
                        <button type="submit" class="btn btn-danger"><i class="fas fa-check"></i> Konfirmasi & Generate Baru</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function copyToken() {
    const tokenInput = document.getElementById('device_token_val');
    tokenInput.select();
    tokenInput.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(tokenInput.value);
    if (window.swal) {
        swal('Berhasil', 'Token berhasil disalin!', 'success');
    }
}
@if(session('error_password'))
$(document).ready(function() { $('#modalConfirmPassword').modal('show'); });
@endif
</script>
@endpush