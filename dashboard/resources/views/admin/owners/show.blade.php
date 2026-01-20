@props([
    'items' => ['Admin', 'Brand Management', 'Detail Brand'],
    'title' => 'Detail Brand & Owner',
    'subtitle' => 'Informasi lengkap data pemilik dan kontrak',
])
@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    {{-- Kartu Keuangan (Saldo & Deposit) - Ditampilkan di Atas --}}
    <div class="card border-0 shadow-sm mb-4 bg-white">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-6 border-end">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-50px me-3 bg-light-success p-3 rounded">
                            <i class="fa fa-wallet fs-2 text-success"></i>
                        </div>
                        <div>
                            <span class="text-muted d-block fw-bold small uppercase">Saldo Tersedia</span>
                            <h2 class="mb-0 fw-bold text-dark">Rp {{ number_format($owner->balance, 0, ',', '.') }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 ps-md-5">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-50px me-3 bg-light-info p-3 rounded">
                            <i class="fa fa-hand-holding-usd fs-2 text-info"></i>
                        </div>
                        <div>
                            <span class="text-muted d-block fw-bold small uppercase">Deposit / Jaminan</span>
                            <h2 class="mb-0 fw-bold text-dark">Rp {{ number_format($owner->deposit_amount, 0, ',', '.') }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Sisi Kiri: Informasi Profil & Bank --}}
        <div class="col-md-4">
            <div class="card border-0 mb-4 shadow-sm">
                <div class="card-body text-center">
                    <img src="{{ $owner->user->image ? asset($owner->user->image) : asset('assets/img/default-user.png') }}"
                         class="rounded-circle mb-3 border p-1 shadow-sm" style="width: 120px; height: 120px; object-fit: cover;">
                    <h5 class="mb-0 fw-bold">{{ $owner->user->name }}</h5>
                    <p class="text-muted mb-3">{{ $owner->user->email }}</p>
                    <span class="badge {{ $owner->status ? 'bg-success' : 'bg-danger' }} px-3 py-2 rounded-pill">
                        {{ $owner->status ? 'Status: Aktif' : 'Status: Nonaktif' }}
                    </span>
                </div>
            </div>

            <div class="card border-0 mb-4 shadow-sm">
                <div class="card-header bg-white fw-bold border-bottom py-3">
                    <i class="fa fa-university me-2 text-primary"></i> Informasi Rekening
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small d-block uppercase fw-bold">Bank</label>
                        <span class="fw-bold fs-14px">{{ $owner->bank_name ?? '-' }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block uppercase fw-bold">Nomor Rekening</label>
                        <span class="fw-bold fs-16px text-primary">{{ $owner->bank_account_number ?? '-' }}</span>
                    </div>
                    <div class="mb-0">
                        <label class="text-muted small d-block uppercase fw-bold">Atas Nama</label>
                        <span class="fw-bold fs-14px">{{ $owner->bank_account_holder_name ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sisi Kanan: Detail Brand & Outlet --}}
        <div class="col-md-8">
            <div class="card border-0 mb-4 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom py-3">
                    <span class="fw-bold">Detail Brand & Kontrak</span>
                    <span class="badge bg-dark">Kode: {{ $owner->code }}</span>
                </div>
                <div class="card-body">
                    <div class="row mb-4 align-items-center">
                        <div class="col-auto">
                            <img src="{{ $owner->brand_logo ? asset($owner->brand_logo) : asset('assets/img/default-img.png') }}"
                                 class="rounded border shadow-sm" style="width: 80px; height: 80px; object-fit: cover;">
                        </div>
                        <div class="col">
                            <h4 class="mb-1 fw-bold">{{ $owner->brand_name }}</h4>
                            <p class="mb-0 text-muted"><i class="fa fa-phone-alt me-1"></i> {{ $owner->brand_phone ?? 'Tidak ada telepon' }}</p>
                        </div>
                    </div>

                    <div class="row border-top pt-4">
                        <div class="col-md-6 mb-4">
                            <label class="text-muted d-block small uppercase fw-bold mb-1">Nomor Kontrak</label>
                            <p class="fs-15px fw-bold text-dark">{{ $owner->contract_number ?? 'Belum diatur' }}</p>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="text-muted d-block small uppercase fw-bold mb-1">Masa Berlaku Kontrak</label>
                            <p class="fs-15px fw-bold">
                                @if($owner->contract_start)
                                    <span class="text-success">{{ \Carbon\Carbon::parse($owner->contract_start)->translatedFormat('d M Y') }}</span>
                                    <span class="mx-2 text-muted">s/d</span>
                                    <span class="text-danger">{{ \Carbon\Carbon::parse($owner->contract_end)->translatedFormat('d M Y') }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted d-block small uppercase fw-bold mb-1">Deskripsi Brand</label>
                            <p class="text-dark leading-relaxed">{{ $owner->brand_description ?? 'Tidak ada deskripsi.' }}</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light-subtle text-end py-3">
                    <a href="{{ route('admin.owners.index') }}" class="btn btn-outline-secondary px-4 me-2">Kembali</a>
                    <a href="{{ route('admin.owners.edit', $owner->id) }}" class="btn btn-primary px-4">
                        <i class="fa fa-edit me-1"></i> Edit Data
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span>Daftar Outlet Terdaftar</span>
                    <span class="badge bg-primary rounded-pill">{{ $owner->outlets->count() }} Outlet</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Nama Outlet</th>
                                <th>Alamat</th>
                                <th class="text-end pe-4">Telepon</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($owner->outlets as $outlet)
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">{{ $outlet->outlet_name }}</td>
                                    <td class="text-muted">{{ Str::limit($outlet->address, 60) }}</td>
                                    <td class="text-end pe-4 text-dark">{{ $outlet->phone_number }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-5">
                                        <i class="fa fa-store-slash d-block mb-2 fs-2"></i>
                                        Belum ada outlet terdaftar untuk brand ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .bg-light-success { background-color: rgba(25, 135, 84, 0.1); }
    .bg-light-info { background-color: rgba(13, 202, 240, 0.1); }
    .uppercase { text-transform: uppercase; letter-spacing: 0.5px; }
    .fs-14px { font-size: 14px; }
    .fs-15px { font-size: 15px; }
    .fs-16px { font-size: 16px; }
    .leading-relaxed { line-height: 1.6; }
</style>
@endpush
