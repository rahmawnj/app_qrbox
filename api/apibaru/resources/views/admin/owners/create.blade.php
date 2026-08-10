@props([
    'items' => ['Admin', 'Manajemen Brand', 'Tambah Brand'],
    'title' => 'Tambah Brand',
    'subtitle' => 'Tambahkan Brand Baru',
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
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.owners.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- 1. AKUN PEMILIK --}}
                <div class="row mb-4">
                    <div class="col-12"><h5 class="text-primary border-bottom pb-2"><i class="fa fa-user-circle me-2"></i>1. Akun Pemilik (Login)</h5></div>
                    <div class="col-md-12 mb-3 text-center">
                        <img id="imagePreview" src="{{ asset('assets/img/default-user.png') }}"
                            style="width: 80px; height: 80px; cursor: pointer; border-radius: 50%; object-fit: cover; border: 2px solid #ddd;"
                            onclick="document.getElementById('image').click();">
                        <input type="file" class="d-none" id="image" name="image" accept="image/*" onchange="previewImage(event)">
                        <div class="mt-2">
                            <button type="button" class="btn btn-xs btn-purple" onclick="document.getElementById('image').click();">Ganti Foto Profil</button>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="{{ old('name') }}" required placeholder="Nama asli pemilik">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email User <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" value="{{ old('email') }}" required placeholder="email@contoh.com">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kata Sandi <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Konfirmasi Kata Sandi <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password_confirmation" required>
                    </div>
                </div>

                {{-- 2. INFORMASI BRAND & KONTRAK --}}
                <div class="row mb-4">
                    <div class="col-12"><h5 class="text-primary border-bottom pb-2"><i class="fa fa-briefcase me-2"></i>2. Informasi Brand & Kontrak</h5></div>
                    <div class="col-md-12 mb-3 text-center">
                        <img id="brandLogoPreview" src="{{ asset('assets/img/default-img.png') }}"
                            style="height: 80px; width: 80px; cursor: pointer; border-radius: 10px; object-fit: cover; border: 2px solid #ddd;"
                            onclick="document.getElementById('brand_logo').click();">
                        <input type="file" class="d-none" id="brand_logo" name="brand_logo" accept="image/*" onchange="previewBrandLogo(event)">
                        <div class="mt-2">
                            <button type="button" class="btn btn-xs btn-purple" onclick="document.getElementById('brand_logo').click();">Ganti Logo Brand</button>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Kode Unik Brand <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="code" value="{{ old('code') }}" placeholder="Contoh: BR001" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nama Brand <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="brand_name" value="{{ old('brand_name') }}" required placeholder="Nama Laundry/Bisnis">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Telepon Brand</label>
                        <input type="text" class="form-control" name="brand_phone" value="{{ old('brand_phone') }}" placeholder="0812xxxx">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Deskripsi Brand</label>
                        <textarea class="form-control" name="brand_description" rows="2" placeholder="Keterangan singkat mengenai brand...">{{ old('brand_description') }}</textarea>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nomor Kontrak</label>
                        <input type="text" class="form-control" name="contract_number" value="{{ old('contract_number') }}" placeholder="Contoh: QBOX/2024/001">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jumlah Deposit (Saldo Jaminan) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold">Rp</span>
                            <input type="number" class="form-control" name="deposit_amount" value="{{ old('deposit_amount', 0) }}" min="0" required>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Mulai Kontrak</label>
                        <input type="date" class="form-control" name="contract_start" id="contract_start" value="{{ old('contract_start') }}">
                        <small class="text-info">Tanggal berakhir akan otomatis diatur +1 tahun.</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Berakhir Kontrak</label>
                        <input type="date" class="form-control" name="contract_end" id="contract_end" value="{{ old('contract_end') }}">
                    </div>
                </div>

                {{-- 3. REKENING BANK --}}
                <div class="row mb-4">
                    <div class="col-12"><h5 class="text-primary border-bottom pb-2"><i class="fa fa-university me-2"></i>3. Rekening Bank (Penarikan Dana)</h5></div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nama Bank</label>
                        <select class="form-control default-select2" name="bank_name">
                            <option value="">-- Pilih Bank --</option>
                            <optgroup label="Bank Umum">
                                <option value="BCA" {{ old('bank_name') == 'BCA' ? 'selected' : '' }}>Bank Central Asia (BCA)</option>
                                <option value="MANDIRI" {{ old('bank_name') == 'MANDIRI' ? 'selected' : '' }}>Bank Mandiri</option>
                                <option value="BNI" {{ old('bank_name') == 'BNI' ? 'selected' : '' }}>Bank Negara Indonesia (BNI)</option>
                                <option value="BRI" {{ old('bank_name') == 'BRI' ? 'selected' : '' }}>Bank Rakyat Indonesia (BRI)</option>
                                <option value="BTN" {{ old('bank_name') == 'BTN' ? 'selected' : '' }}>Bank Tabungan Negara (BTN)</option>
                                <option value="BSI" {{ old('bank_name') == 'BSI' ? 'selected' : '' }}>Bank Syariah Indonesia (BSI)</option>
                            </optgroup>
                            <optgroup label="Bank Swasta & Lainnya">
                                <option value="CIMB" {{ old('bank_name') == 'CIMB' ? 'selected' : '' }}>CIMB Niaga</option>
                                <option value="PERMATA" {{ old('bank_name') == 'PERMATA' ? 'selected' : '' }}>Permata Bank</option>
                                <option value="DANAMON" {{ old('bank_name') == 'DANAMON' ? 'selected' : '' }}>Bank Danamon</option>
                                <option value="MAYBANK" {{ old('bank_name') == 'MAYBANK' ? 'selected' : '' }}>Maybank Indonesia</option>
                                <option value="PANIN" {{ old('bank_name') == 'PANIN' ? 'selected' : '' }}>Panin Bank</option>
                                <option value="OCBC" {{ old('bank_name') == 'OCBC' ? 'selected' : '' }}>OCBC NISP</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nomor Rekening</label>
                        <input type="text" class="form-control" name="bank_account_number" value="{{ old('bank_account_number') }}" placeholder="Digit angka rekening">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nama Pemilik Rekening</label>
                        <input type="text" class="form-control" name="bank_account_holder_name" value="{{ old('bank_account_holder_name') }}" placeholder="Harus sesuai buku tabungan">
                    </div>
                    <div class="col-md-12 mb-3">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="status" id="status" value="1" checked>
                            <label class="form-check-label fw-bold text-success" for="status">Brand Langsung Aktifkan</label>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-top text-end">
                    <a href="{{ route('admin.owners.index') }}" class="btn btn-default w-100px me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary w-100px">Create</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Preview Foto Profil User
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const imgPreview = document.getElementById('imagePreview');
                imgPreview.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        // Preview Logo Brand
        function previewBrandLogo(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const logoPreview = document.getElementById('brandLogoPreview');
                logoPreview.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        // Auto set 1 tahun masa kontrak
        document.getElementById('contract_start').addEventListener('change', function() {
            const startDateValue = this.value;
            if (startDateValue) {
                const startDate = new Date(startDateValue);
                // Tambah 1 tahun
                startDate.setFullYear(startDate.getFullYear() + 1);

                // Kurangi 1 hari agar pas 1 tahun (misal: 01 Jan 2024 - 31 Des 2024)
                startDate.setDate(startDate.getDate() - 1);

                // Format ke YYYY-MM-DD
                const year = startDate.getFullYear();
                const month = String(startDate.getMonth() + 1).padStart(2, '0');
                const day = String(startDate.getDate()).padStart(2, '0');

                document.getElementById('contract_end').value = `${year}-${month}-${day}`;
            }
        });

        // Inisialisasi Select2 jika tersedia di template dashboard Anda
        $(document).ready(function() {
            if ($.fn.select2) {
                $('.default-select2').select2({
                    placeholder: "-- Pilih Bank --",
                    allowClear: true,
                    width: '100%'
                });
            }
        });
    </script>
@endpush
