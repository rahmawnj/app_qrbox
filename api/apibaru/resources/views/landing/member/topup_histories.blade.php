@extends('layouts.landingpage.dashboard-app')

@section('title', 'Riwayat Topup')
@section('header_title', 'Riwayat Topup Anda')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <h4 class="mb-4 fw-semibold">
                        <i class="fas fa-money-check-alt me-2"></i>Riwayat Topup Saldo
                    </h4>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="topup-history-table">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Waktu</th>
                                    <th scope="col">Brand</th>
                                    <th scope="col">Saldo Awal</th>
                                    <th scope="col">Topup</th>
                                    {{-- <th scope="col">Pajak</th> --}}
                                    <th scope="col">Saldo Akhir</th>
                                    <th scope="col">Metode</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Kasir</th>
                                    <th scope="col">Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topupHistories as $history)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>{{ \Carbon\Carbon::parse($history->time)->setTimezone($history->timezone)->format('d-m-Y H:i:s') }}</span>
                                            <small class="text-muted">{{ $history->created_at->timezoneName }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        {{ $history->outlet->owner->brand_name ?? 'N/A' }}
                                        <br>
                                        <small class="text-muted">{{ $history->outlet->outlet_name ?? 'N/A' }}</small>

                                        </td>
                                    <td><span class="text-muted">Rp {{ number_format($history->initial_balance, 0, ',', '.') }}</span></td>
                                    <td><span class="badge bg-primary">Rp {{ number_format($history->amount, 0, ',', '.') }}</span></td>
                                    {{-- <td><span class="text-danger">Rp {{ number_format($history->tax_amount, 0, ',', '.') }}</span></td> --}}
                                    <td><span class="fw-bold text-success">Rp {{ number_format($history->final_balance, 0, ',', '.') }}</span></td>
                                    <td>
                                        <span class="badge rounded-pill bg-{{ match($history->payment_method) {
                                            'cashier' => 'primary',
                                            'qris' => 'info',
                                            'bank_transfer' => 'secondary',
                                            'e_wallet' => 'success',
                                            default => 'dark',
                                        } }}">
                                            {{ ucfirst(str_replace('_', ' ', $history->payment_method)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill
                                            @if ($history->status == 'success') bg-success
                                            @elseif ($history->status == 'pending') bg-warning text-dark
                                            @else bg-danger
                                            @endif">
                                            {{ ucfirst($history->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $history->cashier_name ?? '-' }}</td>
                                    <td>{{ $history->notes ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="12" class="text-center py-5">
                                        <i class="fas fa-info-circle text-muted fs-4 mb-2 d-block"></i>
                                        <strong>Belum ada riwayat topup.</strong>
                                        <p class="text-muted mb-0">Mulai topup saldo Anda sekarang!</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($topupHistories->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $topupHistories->links('vendor.pagination.bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Menggunakan font yang lebih konsisten */
    body {
        font-family: 'Poppins', sans-serif;
    }

    .card {
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
</style>
@endpush
