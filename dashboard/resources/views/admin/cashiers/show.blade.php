@props([
    'items' => ['Admin', 'Cashier Management', 'Detail Kasir'],
    'title' => 'Detail Kasir',
    'subtitle' => 'Informasi lengkap akun dan penugasan kasir'
])

@extends('layouts.dashboard.app')

@section('content')
<x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

<div class="row">
    <div class="col-md-4">
        <div class="card border-0 mb-4 shadow-sm">
            <div class="card-body text-center p-4">
                <div class="mb-3">
                    <img src="{{ $cashier->user->image ? asset($cashier->user->image) : asset('assets/img/default-user.png') }}"
                         class="rounded-circle border p-1" style="width: 120px; height: 120px; object-fit: cover;">
                </div>
                <h4 class="mb-1 fw-bold">{{ $cashier->user->name }}</h4>
                <p class="text-muted small mb-3">{{ $cashier->user->email }}</p>

                <div class="d-inline-block px-3 py-1 rounded-pill {{ $cashier->status ? 'bg-success-transparent-2 text-success' : 'bg-light text-muted' }} fw-bold small">
                    <i class="fa fa-circle fs-9px me-1"></i> {{ $cashier->status ? 'Akun Aktif' : 'Nonaktif' }}
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-none fw-bold border-bottom">Informasi Akun</div>
            <div class="card-body p-0">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="ps-3 text-muted small">Dibuat</td>
                        <td class="pe-3 text-end fw-bold">{{ $cashier->created_at->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="ps-3 text-muted small">Role</td>
                        <td class="pe-3 text-end fw-bold text-uppercase">{{ $cashier->user->role }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-none d-flex align-items-center justify-content-between py-3">
                <h5 class="mb-0 fw-bold"><i class="fa fa-store me-2 text-primary"></i>Penugasan Outlet</h5>
                @if($cashier->outlet)
                    <span class="badge bg-primary">Ditugaskan</span>
                @else
                    <span class="badge bg-danger">Menganggur</span>
                @endif
            </div>
            <div class="card-body p-4">
                @if($cashier->outlet)
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <label class="text-muted small d-block mb-1">Nama Outlet</label>
                            <h5 class="fw-bold">{{ $cashier->outlet->outlet_name }}</h5>
                        </div>
                        <div class="col-sm-6 mb-3 text-sm-end">
                            <label class="text-muted small d-block mb-1">Kode Outlet</label>
                            <span class="badge bg-dark">{{ $cashier->outlet->code }}</span>
                        </div>
                        <div class="col-12 mb-3 border-top pt-3">
                            <label class="text-muted small d-block mb-1">Alamat Outlet</label>
                            <p class="mb-0">{{ $cashier->outlet->address }}</p>
                        </div>
                        <div class="col-12 border-top pt-3 mt-2">
                            <label class="text-muted small d-block mb-1">Dikelola Oleh (Brand)</label>
                            <div class="d-flex align-items-center">
                                <i class="fa fa-building me-2 text-muted"></i>
                                <span class="fw-bold text-primary">{{ $cashier->outlet->owner->brand_name ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fa fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <p class="mb-0">Kasir ini belum ditugaskan ke outlet mana pun.</p>
                        <small class="text-muted">Silakan edit akun untuk memilih lokasi tugas.</small>
                    </div>
                @endif
            </div>
            <div class="card-footer bg-light p-3 text-end">
                <a href="{{ route('admin.cashiers.index') }}" class="btn btn-white px-3">Kembali</a>
                <a href="{{ route('admin.cashiers.edit', $cashier->id) }}" class="btn btn-primary px-3">
                    <i class="fa fa-edit me-1"></i> Edit Akun
                </a>
            </div>
        </div>

        <div class="alert alert-yellow border-0 shadow-sm">
            <div class="d-flex">
                <i class="fa fa-shield-alt fa-2x me-3"></i>
                <div>
                    <h5 class="mt-0 fw-bold">Keamanan Akun</h5>
                    <p class="mb-0 small">Kasir login menggunakan email <b>{{ $cashier->user->email }}</b>. Jika kasir lupa kata sandi, Admin dapat mengubahnya melalui menu Edit. Pastikan kasir menjaga kerahasiaan kredensial akses.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
