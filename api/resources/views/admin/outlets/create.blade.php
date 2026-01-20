@props([
    'items' => ['Admin', 'Manajemen Outlet', 'Tambah Outlet'],
    'title' => 'Tambah Outlet Baru',
    'subtitle' => 'Konfigurasi informasi dan biaya layanan outlet'
])
@extends('layouts.dashboard.app')

@section('content')
<x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

<form action="{{ route('admin.outlets.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row">
        <div class="col-xl-8">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Informasi Dasar</h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Outlet <span class="text-danger">*</span></label>
                            <input type="text" name="outlet_name" class="form-control @error('outlet_name') is-invalid @enderror" value="{{ old('outlet_name') }}" placeholder="Contoh: Kopi Kenangan Sudirman" required>
                            @error('outlet_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Owner / Brand <span class="text-danger">*</span></label>
                            <select name="owner_id" id="owner_id" class="form-control @error('owner_id') is-invalid @enderror" required onchange="updateBrandPreview()">
                                <option value="">-- Pilih Owner --</option>
                                @foreach($owners as $owner)
                                    <option value="{{ $owner->id }}" data-logo="{{ asset($owner->brand_logo) }}" {{ old('owner_id') == $owner->id ? 'selected' : '' }}>
                                        {{ $owner->brand_name }} ({{ $owner->brand_email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kota <span class="text-danger">*</span></label>
                            <input type="text" name="city_name" class="form-control @error('city_name') is-invalid @enderror" value="{{ old('city_name') }}" placeholder="Contoh: Jakarta Selatan" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Zona Waktu <span class="text-danger">*</span></label>
                            <select name="timezone" class="form-control @error('timezone') is-invalid @enderror" required>
                                <option value="Asia/Jakarta" {{ old('timezone') == 'Asia/Jakarta' ? 'selected' : '' }}>WIB (GMT+7)</option>
                                <option value="Asia/Makassar" {{ old('timezone') == 'Asia/Makassar' ? 'selected' : '' }}>WITA (GMT+8)</option>
                                <option value="Asia/Jayapura" {{ old('timezone') == 'Asia/Jayapura' ? 'selected' : '' }}>WIT (GMT+9)</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3" required>{{ old('address') }}</textarea>
                        </div>
                    </div>

                    <hr>
                    <h5 class="mb-3">Koordinat Lokasi</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Latitude</label>
                            <input type="text" name="lat" class="form-control" value="{{ old('lat', '0') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Longitude</label>
                            <input type="text" name="lon" class="form-control" value="{{ old('lon', '0') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Konfigurasi Biaya (Service Fee)</h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fee Transaksi (%)</label>
                            <div class="input-group">
                                <input type="number" step="0.001" name="service_fee_percentage" class="form-control" value="{{ old('service_fee_percentage', '0.100') }}">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">Default: 0.100 (10%)</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Min. Fee Bulanan (IDR)</label>
                            <input type="number" name="min_monthly_service_fee" class="form-control" value="{{ old('min_monthly_service_fee', '100000') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Deposit Perangkat (IDR)</label>
                            <input type="number" name="device_deposit_price" class="form-control" value="{{ old('device_deposit_price', '500000') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Media & Status</h4>
                </div>
                <div class="panel-body text-center">
                    <div class="mb-3">
                        <label class="form-label d-block">Foto Outlet</label>
                        <img id="outletPreview" src="{{ asset('assets/img/default-img.png') }}"
                             class="img-thumbnail mb-2" style="width: 200px; height: 150px; object-fit: cover; cursor: pointer"
                             onclick="document.getElementById('image').click()">
                        <input type="file" name="image" id="image" class="d-none" onchange="previewImage(this, 'outletPreview')">
                    </div>

                    <div class="mb-4">
                        <label class="form-label d-block">Preview Logo Brand</label>
                        <img id="brandLogoPreview" src="{{ asset('assets/img/default-user.png') }}"
                             class="img-thumbnail" style="width: 100px; height: 100px; object-fit: contain; background: #f8f9fa;">
                    </div>

                    <div class="mb-3 text-start">
                        <label class="form-label">Status Outlet</label>
                        <select name="status" class="form-control border-primary">
                            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>

                    <hr>
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-save me-1"></i> Simpan Outlet
                    </button>
                    <a href="{{ route('admin.outlets.index') }}" class="btn btn-default w-100">Batal</a>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(previewId).src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function updateBrandPreview() {
        const select = document.getElementById('owner_id');
        const logo = select.options[select.selectedIndex].getAttribute('data-logo');
        document.getElementById('brandLogoPreview').src = logo ? logo : "{{ asset('assets/img/default-user.png') }}";
    }

    document.addEventListener('DOMContentLoaded', updateBrandPreview);
</script>
@endsection
