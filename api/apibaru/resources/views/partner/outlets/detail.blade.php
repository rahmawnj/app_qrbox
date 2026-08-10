@extends('layouts.dashboard.app')

@php
    $feature = getData();
    $currentTab = request()->query('tab', 'profile');
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card overflow-hidden mb-4 rounded-4 shadow-sm border-0">

                {{-- Card Header --}}
                <div class="card-header p-4 d-flex flex-column flex-md-row align-items-md-center align-items-start bg-light border-bottom">
                    <div class="d-flex align-items-center mb-3 mb-md-0 me-md-4">
                        <img src="{{ asset($outlet->owner->brand_logo ?? 'assets/img/default-logo.png') }}"
                             alt="Brand Logo"
                             class="me-3 rounded-circle border border-2 border-primary p-1 shadow-sm"
                             style="width: 80px; height: 80px; object-fit: contain; background-color: white;">
                        <div>
                            <h1 class="mb-1 text-dark fw-bold fs-4">{{ $outlet->outlet_name }}</h1>
                            <p class="mb-0 text-muted fs-6">
                                <span class="badge bg-secondary-soft text-dark fw-medium">Kode: #{{ $outlet->code }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="ms-md-auto mt-3 mt-md-0 d-flex align-items-center gap-3">
                        <div class="text-end d-none d-sm-block">
                            <small class="text-muted d-block">Status Operasional</small>
                            <span class="fw-bold {{ $outlet->status ? 'text-success' : 'text-danger' }}">
                                {{ $outlet->status ? 'Aktif / Buka' : 'Non-Aktif / Tutup' }}
                            </span>
                        </div>

                        <form id="statusForm" action="{{ route('partner.outlets.update-status', $outlet) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="form-select fw-semibold border-primary-subtle" onchange="confirmStatusChange(this)">
                                <option value="1" {{ $outlet->status == 1 ? 'selected' : '' }}>🟢 Buka</option>
                                <option value="0" {{ $outlet->status == 0 ? 'selected' : '' }}>🔴 Tutup</option>
                            </select>
                        </form>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="card-body p-4 p-md-5 bg-white">

                    {{-- Tab Navigation --}}
                    <ul class="nav nav-pills mb-4 gap-2" id="outletTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link px-4 {{ $currentTab == 'profile' ? 'active shadow-sm' : 'bg-light text-dark' }}"
                               href="{{ route('partner.outlets.detail', ['outlet' => $outlet->id, 'tab' => 'profile']) }}">
                                <i class="fas fa-info-circle me-2"></i>Profil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-4 {{ $currentTab == 'edit-profile' ? 'active shadow-sm' : 'bg-light text-dark' }}"
                               href="{{ route('partner.outlets.detail', ['outlet' => $outlet->id, 'tab' => 'edit-profile']) }}">
                                <i class="fas fa-edit me-2"></i>Edit Profil
                            </a>
                        </li>
                    </ul>

                    <hr class="mb-4 opacity-10">

                    {{-- Content Section --}}
                    <div class="tab-content">
                        @if ($currentTab == 'profile')
                            <div class="row g-4">
                                 <div class="col-12">
                                    <div class="card bg-primary bg-gradient text-white border-0 shadow-sm rounded-4">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <h5 class="mb-1 opacity-75">Total Infrastruktur</h5>
                                                    <h2 class="display-6 fw-bold mb-0">{{ $feature->devices->count() ?? 0 }}</h2>
                                                    <p class="mb-0">Mesin yang terhubung di outlet ini</p>
                                                </div>
                                                <div class="display-4 opacity-25">
                                                    <i class="fas fa-desktop"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="card border border-light-subtle h-100 shadow-sm rounded-4">
                                        <div class="card-body p-4">
                                            <h5 class="text-primary fw-bold mb-4 border-bottom pb-2">
                                                <i class="fas fa-store me-2"></i>Detail Outlet
                                            </h5>
                                            <div class="row mb-3">
                                                <div class="col-sm-4 text-muted">Nama</div>
                                                <div class="col-sm-8 fw-semibold">{{ $outlet->outlet_name }}</div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-sm-4 text-muted">Lokasi</div>
                                                <div class="col-sm-8 fw-semibold">{{ $outlet->city_name ?? '-' }}</div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-sm-4 text-muted">Alamat</div>
                                                <div class="col-sm-8 fw-semibold">{{ $outlet->address ?? '-' }}</div>
                                            </div>
                                            <div class="row mb-0">
                                                <div class="col-sm-4 text-muted">Zona Waktu</div>
                                                <div class="col-sm-8 fw-semibold">
                                                    <span class="badge bg-info-subtle text-info px-3">{{ $outlet->timezone }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Brand Info --}}
                                <div class="col-lg-6">
                                    <div class="card border border-light-subtle h-100 shadow-sm rounded-4">
                                        <div class="card-body p-4">
                                            <h5 class="text-primary fw-bold mb-4 border-bottom pb-2">
                                                <i class="fas fa-building me-2"></i>Informasi Pemilik
                                            </h5>
                                            <div class="mb-3">
                                                <small class="text-muted d-block">Nama Brand</small>
                                                <span class="fw-semibold">{{ $outlet->owner->brand_name ?? '-' }}</span>
                                            </div>
                                            <div class="mb-3">
                                                <small class="text-muted d-block">Kontak Email</small>
                                                <span class="fw-semibold text-primary">{{ $outlet->owner->brand_email ?? '-' }}</span>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">WhatsApp / Telp</small>
                                                <span class="fw-semibold">{{ $outlet->owner->brand_phone ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Statistics --}}

                            </div>

                        @elseif ($currentTab == 'edit-profile')
                            @include('partner.outlets._profile_detail')
                        @endif
                    </div>

                    {{-- Footer Actions --}}
                    <div class="mt-5 pt-4 border-top d-flex justify-content-between align-items-center">
                        <p class="text-muted small mb-0 d-none d-md-block">
                            Terdaftar sejak: {{ $outlet->created_at->format('d M Y') }}
                        </p>

                        {{-- <form action="{{ route('partner.outlets.destroy', $outlet->id) }}" method="POST" id="deleteForm">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-outline-danger rounded-pill px-4"
                                    onclick="confirmDeletion()"
                                    {{ !$feature->can('partner.outlets.destroy') ? 'disabled' : '' }}>
                                <i class="fas fa-trash-alt me-2"></i> Hapus Outlet
                            </button>
                        </form> --}}
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
    .card { transition: transform 0.2s ease; }
    .nav-pills .nav-link {
        border-radius: 12px;
        font-weight: 500;
        transition: 0.3s;
    }
    .nav-pills .nav-link.active { background-color: #0d6efd; color: white; }
    .bg-secondary-soft { background-color: #e9ecef; }
    .form-select { border-radius: 10px; cursor: pointer; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/plugins/sweetalert/dist/sweetalert.min.js') }}"></script>
<script>
    // Konfirmasi Ganti Status menggunakan SweetAlert
    function confirmStatusChange(selectElement) {
        const statusText = selectElement.value === '1' ? 'Membuka' : 'Menutup';
        swal({
            title: "Konfirmasi Status",
            text: `Apakah Anda yakin ingin ${statusText} outlet ini?`,
            icon: "warning",
            buttons: ["Batal", "Ya, Ubah!"],
            dangerMode: selectElement.value === '0',
        }).then((willUpdate) => {
            if (willUpdate) {
                document.getElementById('statusForm').submit();
            } else {
                // Reset select ke nilai sebelumnya jika batal
                location.reload();
            }
        });
    }

    // Konfirmasi Hapus
    function confirmDeletion() {
        swal({
            title: "Hapus Outlet?",
            text: "Aksi ini tidak dapat dibatalkan dan semua data terkait akan hilang!",
            icon: "danger",
            buttons: ["Batal", "Hapus Permanen"],
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                document.getElementById('deleteForm').submit();
            }
        });
    }

    // Notifikasi Flash
    document.addEventListener('DOMContentLoaded', function() {
        @if (session('success'))
            swal("Berhasil!", "{{ session('success') }}", "success");
        @endif
        @if (session('error'))
            swal("Gagal!", "{{ session('error') }}", "error");
        @endif
    });
</script>
@endpush
