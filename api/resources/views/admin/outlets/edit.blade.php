@props([
    'items' => ['Admin', 'Manajemen Outlet', 'Edit Outlet'],
    'title' => 'Edit Informasi Outlet',
    'subtitle' => 'Perbarui detail data dan pengaturan outlet'
])
@extends('layouts.dashboard.app')

@section('content')
<x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

<form action="{{ route('admin.outlets.update', $outlet->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-xl-8">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Detail Outlet: {{ $outlet->code }}</h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Outlet <span class="text-danger">*</span></label>
                            <input type="text" name="outlet_name" class="form-control @error('outlet_name') is-invalid @enderror" value="{{ old('outlet_name', $outlet->outlet_name) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Owner / Brand <span class="text-danger">*</span></label>
                            <select name="owner_id" id="owner_id" class="form-control" required onchange="updateBrandPreview()">
                                @foreach($owners as $owner)
                                    <option value="{{ $owner->id }}" data-logo="{{ asset($owner->brand_logo) }}" {{ old('owner_id', $outlet->owner_id) == $owner->id ? 'selected' : '' }}>
                                        {{ $owner->brand_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kota <span class="text-danger">*</span></label>
                            <input type="text" name="city_name" class="form-control" value="{{ old('city_name', $outlet->city_name) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Zona Waktu <span class="text-danger">*</span></label>
                            <select name="timezone" class="form-control" required>
                                <option value="Asia/Jakarta" {{ old('timezone', $outlet->timezone) == 'Asia/Jakarta' ? 'selected' : '' }}>WIB</option>
                                <option value="Asia/Makassar" {{ old('timezone', $outlet->timezone) == 'Asia/Makassar' ? 'selected' : '' }}>WITA</option>
                                <option value="Asia/Jayapura" {{ old('timezone', $outlet->timezone) == 'Asia/Jayapura' ? 'selected' : '' }}>WIT</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="address" class="form-control" rows="3" required>{{ old('address', $outlet->address) }}</textarea>
                        </div>
                    </div>

                    <hr>
                    <div class="row">
                        @php $coords = is_array($outlet->latlong) ? $outlet->latlong : json_decode($outlet->latlong, true); @endphp
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Latitude</label>
                            <input type="text" name="lat" class="form-control" value="{{ old('lat', $coords['lat'] ?? '0') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Longitude</label>
                            <input type="text" name="lon" class="form-control" value="{{ old('lon', $coords['lon'] ?? '0') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Pengaturan Biaya Layanan</h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fee Transaksi (%)</label>
                            <input type="number" step="0.001" name="service_fee_percentage" class="form-control" value="{{ old('service_fee_percentage', $outlet->service_fee_percentage) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Min. Fee Bulanan</label>
                            <input type="number" name="min_monthly_service_fee" class="form-control" value="{{ old('min_monthly_service_fee', $outlet->min_monthly_service_fee) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Deposit Perangkat</label>
                            <input type="number" name="device_deposit_price" class="form-control" value="{{ old('device_deposit_price', $outlet->device_deposit_price) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Foto & Status</h4>
                </div>
                <div class="panel-body text-center">
                    <div class="mb-3">
                        <img id="outletPreview" src="{{ $outlet->image ? asset($outlet->image) : asset('assets/img/default-img.png') }}"
                             class="img-thumbnail mb-2" style="width: 200px; height: 150px; object-fit: cover; cursor: pointer"
                             onclick="document.getElementById('image').click()">
                        <input type="file" name="image" id="image" class="d-none" onchange="previewImage(this, 'outletPreview')">
                        <small class="d-block text-muted">Klik gambar untuk mengganti</small>
                    </div>

                    <div class="mb-3 text-start">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control border-primary">
                            <option value="1" {{ old('status', $outlet->status) == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('status', $outlet->status) == '0' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-warning w-100 mb-2 text-white">
                        <i class="fas fa-sync me-1"></i> Perbarui Data
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
            reader.onload = function(e) { document.getElementById(previewId).src = e.target.result; }
            reader.readAsDataURL(input.files[0]);
        }
    }
    function updateBrandPreview() {
        const select = document.getElementById('owner_id');
        const logo = select.options[select.selectedIndex].getAttribute('data-logo');
        document.getElementById('brandLogoPreview').src = logo ? logo : "{{ asset('assets/img/default-user.png') }}";
    }
</script>
@endsection
