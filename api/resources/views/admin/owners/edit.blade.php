@props([
    'items' => ['Admin', 'Manajemen Brand', 'Edit Brand'],
    'title' => 'Edit Brand',
    'subtitle' => 'Perbarui Data Brand & Kontrak'
])
@extends('layouts.dashboard.app')
@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">{{ $title ?? '' }}</h4>
            <div class="panel-heading-btn">
                <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand"><i class="fa fa-expand"></i></a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload"><i class="fa fa-redo"></i></a>
            </div>
        </div>
        <div class="panel-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.owners.update', $owner) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row mb-4">
                    <div class="col-12"><h5 class="text-primary border-bottom pb-2">1. Akun Pemilik (Login)</h5></div>
                    <div class="col-md-12 mb-3 text-center">
                        <img id="imagePreview" src="{{ $owner->user->image ? asset($owner->user->image) : asset('assets/img/default-user.png') }}"
                            style="width: 80px; height: 80px; cursor: pointer; border-radius: 50%; object-fit: cover;"
                            onclick="document.getElementById('image').click();">
                        <input type="file" class="d-none" id="image" name="image" accept="image/*" onchange="previewImage(event)">
                        <div class="mt-2">
                            <button type="button" class="btn btn-xs btn-purple" onclick="document.getElementById('image').click();">Ganti Foto Profil</button>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="{{ old('name', $owner->user->name) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email User <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" value="{{ old('email', $owner->user->email) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kata Sandi (Kosongkan jika tidak diganti)</label>
                        <input type="password" class="form-control" name="password">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Konfirmasi Kata Sandi</label>
                        <input type="password" class="form-control" name="password_confirmation">
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12"><h5 class="text-primary border-bottom pb-2">2. Informasi Brand & Kontrak</h5></div>
                    <div class="col-md-12 mb-3 text-center">
                        <img id="brandLogoPreview" src="{{ $owner->brand_logo ? asset($owner->brand_logo) : asset('assets/img/default-img.png') }}"
                            style="height: 80px; cursor: pointer; border-radius: 10px; object-fit: cover;"
                            onclick="document.getElementById('brand_logo').click();">
                        <input type="file" class="d-none" id="brand_logo" name="brand_logo" accept="image/*" onchange="previewBrandLogo(event)">
                        <div class="mt-2">
                            <button type="button" class="btn btn-xs btn-purple" onclick="document.getElementById('brand_logo').click();">Ganti Logo Brand</button>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Kode Unik Brand <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="code" value="{{ old('code', $owner->code) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nama Brand <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="brand_name" value="{{ old('brand_name', $owner->brand_name) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Telepon Brand</label>
                        <input type="text" class="form-control" name="brand_phone" value="{{ old('brand_phone', $owner->brand_phone) }}">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Deskripsi Brand</label>
                        <textarea class="form-control" name="brand_description" rows="2">{{ old('brand_description', $owner->brand_description) }}</textarea>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nomor Kontrak</label>
                        <input type="text" class="form-control" name="contract_number" value="{{ old('contract_number', $owner->contract_number) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jumlah Deposit (Saldo Jaminan) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="number"
                                class="form-control @error('deposit_amount') is-invalid @enderror"
                                name="deposit_amount"
                                value="{{ old('deposit_amount', number_format($owner->deposit_amount, 0, '', '')) }}"
                                required>
                        </div>
                        <small class="text-muted">Total deposit saat ini: <strong>Rp {{ number_format($owner->deposit_amount, 0, ',', '.') }}</strong></small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Mulai Kontrak</label>
                        <input type="date" class="form-control" name="contract_start" value="{{ old('contract_start', $owner->contract_start) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Berakhir Kontrak</label>
                        <input type="date" class="form-control" name="contract_end" value="{{ old('contract_end', $owner->contract_end) }}">
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12"><h5 class="text-primary border-bottom pb-2">3. Rekening Bank (Penarikan Dana)</h5></div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nama Bank</label>
                        <input type="text" class="form-control" name="bank_name" value="{{ old('bank_name', $owner->bank_name) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nomor Rekening</label>
                        <input type="text" class="form-control" name="bank_account_number" value="{{ old('bank_account_number', $owner->bank_account_number) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nama Pemilik Rekening</label>
                        <input type="text" class="form-control" name="bank_account_holder_name" value="{{ old('bank_account_holder_name', $owner->bank_account_holder_name) }}">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Status Brand</label>
                        <select class="form-control" name="status" required>
                            <option value="1" {{ old('status', $owner->status) == 1 ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('status', $owner->status) == 0 ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                </div>

                <div class="pt-3 border-top">
                    <button type="submit" class="btn btn-primary w-100px">Update</button>
                    <a href="{{ route('admin.owners.index') }}" class="btn btn-default w-100px">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                document.getElementById('imagePreview').src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        function previewBrandLogo(event) {
            const reader = new FileReader();
            reader.onload = function() {
                document.getElementById('brandLogoPreview').src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
@endpush
