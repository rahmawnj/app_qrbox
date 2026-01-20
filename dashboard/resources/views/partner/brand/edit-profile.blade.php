@php
    $title = 'Edit Profil Brand';
    $currentPage = request()->query('page', 'profile'); // Get current page from query parameter, default to 'profile'
@endphp

@extends('layouts.dashboard.app')

@push('styles')
    <link href="{{ asset('assets/plugins/gritter/css/jquery.gritter.css') }}" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">

    <style>
        /* Keep original img-thumbnail if you had custom styles for it */
        .img-thumbnail {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 0.25rem;
        }

        /* ----- ONLY Tabs Styling Changes Below This Line ----- */

        .nav-tabs-modern {
            /* Remove default Bootstrap tab border */
            border-bottom: 0;
            padding-left: 15px;
            /* Add some padding if needed, consistent with panel body */
            padding-right: 15px;
            /* Add some padding if needed */
        }

        .nav-tabs-modern .nav-item {
            margin-bottom: -1px;
            /* Overlap border if needed */
        }

        .nav-tabs-modern .nav-link {
            border: 0;
            /* Remove all borders by default */
            border-bottom: 2px solid transparent;
            /* Create an active indicator line */
            border-radius: 0;
            /* Remove border radius for clean underline effect */
            background-color: transparent;
            /* No background color */
            color: #ccc;
            /* Lighter color for inactive tabs */
            font-weight: 500;
            padding: 10px 15px;
            /* Adjust padding for spacing */
            transition: all 0.3s ease-in-out;
            /* Smooth transitions for hover/active */
        }

        .nav-tabs-modern .nav-link.active {
            color: #fff;
            /* White text for active tab on dark background */
            background-color: transparent;
            /* No background color */
            border-bottom-color: #007bff;
            /* Primary color underline for active tab */
        }

        .nav-tabs-modern .nav-link:hover:not(.active) {
            color: #eee;
            /* Slightly brighter on hover */
            border-bottom-color: rgba(255, 255, 255, 0.2);
            /* Subtle underline on hover */
        }

        /* Ensure the panel heading has consistent padding if tabs are moved */
        .panel-heading {
            padding-bottom: 0;
            /* Adjust if the tabs were part of its padding */
            border-bottom: 0;
            /* Remove default panel heading border if tabs are handled separately */
        }

        /* Adjust padding for tab-content if tabs are outside panel-body's default flow */
        .tab-content {
            padding-top: 20px;
            /* Add space between tabs and content */
        }
    </style>
@endpush

@section('content')

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">{{ $title }}</h4>
            <div class="panel-heading-btn">
                <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand"><i
                        class="fa fa-expand"></i></a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload"><i
                        class="fa fa-redo"></i></a>
            </div>
            {{-- Moved tabs here, directly under panel-heading, but not inside the h4 div --}}
            <ul class="nav nav-tabs nav-tabs-modern"> {{-- Applied custom class here --}}
                <li class="nav-item">
                    <a class="nav-link {{ $currentPage == 'profile' ? 'active' : '' }}"
                        href="{{ route('partner.brand.profile.edit', ['page' => 'profile']) }}">
                        <i class="fas fa-building me-2"></i> Informasi Brand
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $currentPage == 'bank' ? 'active' : '' }}"
                        href="{{ route('partner.brand.profile.edit', ['page' => 'bank']) }}">
                        <i class="fas fa-money-check-alt me-2"></i> Informasi Bank
                    </a>
                </li>
            </ul>
        </div>
        <div class="panel-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <h4 class="alert-heading"><i class="fa fa-exclamation-triangle"></i> Terjadi Kesalahan!</h4>
                    <p>Mohon periksa kembali input Anda:</p>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Tab content moved inside panel-body --}}
            <div class="tab-content pt-3">
                {{-- Profile Tab Pane --}}
                <div class="tab-pane fade {{ $currentPage == 'profile' ? 'show active' : '' }}" id="profile-tab-pane">
                 <form action="{{ route('partner.brand.profile.update', ['page' => 'profile']) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <h5 class="mb-3 text-primary"><i class="fas fa-info-circle me-2"></i>Detail Informasi Brand</h5>
<div class="p-3 bg-light rounded mb-3">
        <h6 class="text-muted mb-2"><i class="fas fa-file-contract me-1"></i> Informasi Kerjasama</h6>
        <div class="row">
            <div class="col-sm-4">
                <small class="d-block text-muted">No. Kontrak:</small>
                <strong>{{ getBrand()->contract_number ?? '-' }}</strong>
            </div>
            <div class="col-sm-4">
                <small class="d-block text-muted">Masa Berlaku:</small>
                <strong>{{ getBrand()->contract_start ? \Carbon\Carbon::parse(getBrand()->contract_start)->format('d M Y') : '-' }} s/d {{ getBrand()->contract_end ? \Carbon\Carbon::parse(getBrand()->contract_end)->format('d M Y') : '-' }}</strong>
            </div>
            <div class="col-sm-4">
                <small class="d-block text-muted">Status:</small>
                <span class="badge {{ getBrand()->status ? 'bg-success' : 'bg-warning' }}">
                    {{ getBrand()->status ? 'Aktif' : 'Non-Aktif' }}
                </span>
            </div>
        </div>
    </div>
    <div class="mb-3">
        <label for="brand_logo" class="form-label">Logo Brand</label>
        <input type="file" class="form-control @error('brand_logo') is-invalid @enderror"
            id="brand_logo" name="brand_logo" accept="image/*" onchange="previewImageBrand(event)">
        <small class="form-text text-muted">Format: JPG, PNG. Maksimal 2MB.</small>
        @error('brand_logo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="mt-2">
            <img id="brandLogoPreview"
                src="{{ getBrand()->brand_logo ? asset('storage/' . getBrand()->brand_logo) : asset('assets/img/default-brand-logo.png') }}"
                alt="Brand Logo Preview" class="img-thumbnail" style="max-width: 150px;">
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="brand_name" class="form-label">Nama Brand <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('brand_name') is-invalid @enderror"
                id="brand_name" name="brand_name" value="{{ old('brand_name', getBrand()->brand_name) }}" required>
            @error('brand_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="brand_phone" class="form-label">Nomor Telepon Brand</label>
            <input type="text" class="form-control @error('brand_phone') is-invalid @enderror"
                id="brand_phone" name="brand_phone"
                value="{{ old('brand_phone', getBrand()->brand_phone) }}" placeholder="Contoh: 08123456789">
            @error('brand_phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mb-3">
        <label for="brand_description" class="form-label">Deskripsi Brand</label>
        <textarea class="form-control @error('brand_description') is-invalid @enderror"
            id="brand_description" name="brand_description" rows="3"
            placeholder="Ceritakan sedikit tentang brand Anda...">{{ old('brand_description', getBrand()->brand_description) }}</textarea>
        @error('brand_description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>



    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i> Update Profil Brand</button>
</form>
                </div>

                {{-- Bank Tab Pane --}}
                <div class="tab-pane fade {{ $currentPage == 'bank' ? 'show active' : '' }}" id="bank-tab-pane">
                    <form action="{{ route('partner.brand.profile.update', ['page' => 'bank']) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h5 class="mb-3 text-primary"><i class="fas fa-money-check-alt me-2"></i>Informasi Bank untuk
                            Penarikan Dana</h5>
                        <p class="mb-4 text-muted">Pastikan informasi bank yang Anda masukkan benar untuk kelancaran proses
                            penarikan dana.</p>

                      <div class="mb-3">
                            <label for="bank_name" class="form-label">Nama Bank</label>
                            <select class="form-select @error('bank_name') is-invalid @enderror" id="bank_name" name="bank_name">
                                <option value="">-- Pilih Bank --</option>
                                @php
                                    $banks = [
                                        'BCA' => 'Bank Central Asia (BCA)',
                                        'BNI' => 'Bank Negara Indonesia (BNI)',
                                        'BRI' => 'Bank Rakyat Indonesia (BRI)',
                                        'Mandiri' => 'Bank Mandiri',
                                        'BSI' => 'Bank Syariah Indonesia (BSI)',
                                        'CIMB' => 'CIMB Niaga',
                                        'Permata' => 'Bank Permata',
                                        'Danamon' => 'Bank Danamon',
                                        'BTN' => 'Bank Tabungan Negara (BTN)',
                                    ];
                                    $currentBank = old('bank_name', getBrand()->bank_name);
                                @endphp

                                @foreach($banks as $code => $label)
                                    <option value="{{ $code }}" {{ $currentBank == $code ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                                <option value="LAINNYA" {{ $currentBank == 'LAINNYA' ? 'selected' : '' }}>Bank Lainnya...</option>
                            </select>

                            @error('bank_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="bank_account_number" class="form-label">Nomor Rekening</label>
                            <input type="text" class="form-control @error('bank_account_number') is-invalid @enderror"
                                id="bank_account_number" name="bank_account_number"
                                value="{{ old('bank_account_number', getBrand()->bank_account_number) }}"
                                placeholder="Contoh: 1234567890">
                            @error('bank_account_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="bank_account_holder_name" class="form-label">Nama Pemilik Rekening</label>
                            <input type="text"
                                class="form-control @error('bank_account_holder_name') is-invalid @enderror"
                                id="bank_account_holder_name" name="bank_account_holder_name"
                                value="{{ old('bank_account_holder_name', getBrand()->bank_account_holder_name) }}"
                                placeholder="Contoh: Nama Lengkap Sesuai Rekening">
                            @error('bank_account_holder_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-save me-2"></i> Update
                            Informasi Bank</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('assets/plugins/gritter/js/jquery.gritter.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

    <script>
        $(document).ready(function() {
            @if (session('success'))
                $.gritter.add({
                    title: 'Success!',
                    text: '{{ session('success') }}',
                    sticky: false,
                    time: 3000,
                    class_name: 'gritter-light'
                });
            @endif

            @if (session('error'))
                $.gritter.add({
                    title: 'Error!',
                    text: '{{ session('error') }}',
                    sticky: false,
                    time: 3000,
                    class_name: 'gritter-light'
                });
            @endif

            // Initialize Summernote editor for brand_description
            $('#brand_description').summernote({
                placeholder: 'Masukkan deskripsi brand Anda di sini...',
                tabsize: 2,
                height: 300, // Tinggi editor yang lebih besar
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });

            function previewImageBrand(event) {
                const reader = new FileReader();
                reader.onload = function() {
                    const output = document.getElementById('brandLogoPreview');
                    output.src = reader.result;
                };
                reader.readAsDataURL(event.target.files[0]);
            }

            // Attach the previewImageBrand function to the file input change event
            $('#brand_logo').on('change', previewImageBrand);
        });
    </script>
@endpush
