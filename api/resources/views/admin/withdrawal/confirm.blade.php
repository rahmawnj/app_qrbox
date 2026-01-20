@props([
    'items' => ['Admin', 'Withdrawal Management', 'Konfirmasi Penarikan'],
    'title' => 'Konfirmasi Penarikan Dana',
    'subtitle' => 'Tinjau detail permintaan penarikan dan proses konfirmasi atau penolakan.',
])

@extends('layouts.dashboard.app')

@push('styles')
    <style>
        .detail-card { background-color: #fff; border: 1px solid #e0e0e0; border-radius: .5rem; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); padding: 1.5rem; margin-bottom: 1.5rem; }
        .detail-card h5 { color: #343a40; font-weight: bold; margin-bottom: 1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; }
        .detail-item { display: flex; align-items: center; margin-bottom: 0.75rem; }
        .detail-item .icon { font-size: 1.2rem; width: 30px; text-align: center; color: #007bff; flex-shrink: 0; }
        .detail-item strong { margin-right: 0.5rem; min-width: 160px; color: #555; }
        .amount-highlight { font-size: 2.2rem; font-weight: bold; color: #28a745; display: block; margin-top: 0.5rem; }
        .balance-card { background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 1.5rem; border-radius: .5rem; margin-bottom: 1.5rem; text-align: center; }
        .balance-amount { font-size: 2rem; font-weight: bold; color: #007bff; }
        .fee-box { background: #fff5f5; border: 1px dashed #feb2b2; padding: 10px; border-radius: 8px; }
    </style>
@endpush

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    <div class="row">
        <div class="col-lg-8">
            <div class="detail-card">
                <h5><i class="fa fa-info-circle me-2"></i> Detail Transaksi Penarikan</h5>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <span class="icon"><i class="fa fa-user"></i></span>
                            <strong>Nama Owner:</strong>
                            <span>{{ $transaction->owner->user->name ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="icon"><i class="fa fa-building"></i></span>
                            <strong>Brand:</strong>
                            <span>{{ $transaction->owner->brand_name ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="icon"><i class="fa fa-id-card"></i></span>
                            <strong>Order ID:</strong>
                            <span>{{ $transaction->order_id }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="icon"><i class="fa fa-calendar-alt"></i></span>
                            <strong>Waktu Pengajuan:</strong>
                            <span>{{ $transaction->date }} {{ $transaction->time }} <small class="text-muted">({{ $transaction->timezone }})</small></span>
                        </div>

                        <hr>
                        <h6 class="fw-bold mt-3">Informasi Rekening Tujuan:</h6>
                        <div class="detail-item">
                            <span class="icon"><i class="fa fa-university text-primary"></i></span>
                            <strong>Bank:</strong>
                            <span>{{ $transaction->owner->bank_name ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="icon"><i class="fa fa-credit-card text-primary"></i></span>
                            <strong>No. Rekening:</strong>
                            <span class="fw-bold text-dark fs-5">{{ $transaction->owner->bank_account_number ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="icon"><i class="fa fa-user-circle text-primary"></i></span>
                            <strong>Atas Nama:</strong>
                            <span>{{ $transaction->owner->bank_account_holder_name ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="col-md-6 border-start">
                        <div class="text-center mb-4">
                            <p class="mb-0 text-muted">Dana Yang Harus Ditransfer:</p>
                            <span class="amount-highlight">Rp {{ number_format($transaction->gross_amount, 0, ',', '.') }}</span>
                            <small class="text-success fw-bold"><i class="fa fa-arrow-down"></i> Nominal Bersih Diterima Owner</small>
                        </div>

                        <div class="fee-box mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Potongan Biaya Admin:</span>
                                <span class="text-danger fw-bold">Rp {{ number_format($transaction->service_fee_amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold border-top pt-1">
                                <span>Total Saldo Terpotong:</span>
                                <span>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="detail-item justify-content-between">
                            <strong>Status Saat Ini:</strong>
                            @php
                                $statusClass = match($transaction->status) {
                                    'pending' => 'bg-warning text-dark',
                                    'success' => 'bg-success',
                                    'failed' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $statusClass }} px-3 py-2 fs-6">{{ strtoupper($transaction->status) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Info Saldo --}}
            <div class="balance-card">
                <p class="mb-1 fw-bold text-muted">Saldo Tersisa di Akun Owner:</p>
                <div class="balance-amount">Rp {{ number_format($currentOwnerBalance, 0, ',', '.') }}</div>
                <p class="small text-muted mb-0">Saldo sebelum transaksi ini selesai.</p>
            </div>

            {{-- Aksi --}}
            @if ($transaction->status == 'pending')
                <div class="detail-card">
                    <h5><i class="fa fa-gavel me-2"></i> Aksi Admin</h5>
                    <p class="small text-muted">Pastikan Anda telah melakukan transfer ke rekening di atas sebelum menekan tombol Konfirmasi.</p>

                    <form id="withdrawal-action-form" method="POST" action="{{ route('admin.withdrawal.store') }}">
                        @csrf
                        <input type="hidden" name="withdrawal_id" value="{{ $transaction->id }}">
                        <input type="hidden" name="action" id="action-type">
                        <input type="hidden" name="rejection_reason" id="rejection_reason">

                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-success btn-lg py-3" onclick="confirmAction('approve')">
                                <i class="fa fa-check-circle me-2"></i> Konfirmasi Transfer
                            </button>
                            <button type="button" class="btn btn-outline-danger" onclick="confirmAction('reject')">
                                <i class="fa fa-times-circle me-2"></i> Tolak Permintaan
                            </button>
                            <a href="{{ route('admin.withdrawal.list') }}" class="btn btn-light mt-2">
                                <i class="fa fa-arrow-left me-2"></i> Kembali ke Daftar
                            </a>
                        </div>
                    </form>
                </div>
            @else
                <div class="alert alert-info text-center py-4">
                    <i class="fa fa-check-circle fa-3x mb-3 text-info"></i>
                    <h5>Transaksi {{ ucfirst($transaction->status) }}</h5>
                    <p class="mb-0">Transaksi ini sudah tidak dapat diubah kembali.</p>
                    <a href="{{ route('admin.withdrawal.list') }}" class="btn btn-sm btn-secondary mt-3">Kembali</a>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmAction(actionType) {
        const isApprove = actionType === 'approve';

        Swal.fire({
            title: isApprove ? 'Konfirmasi Pembayaran?' : 'Tolak Penarikan?',
            html: isApprove
                ? `Apakah Anda sudah mentransfer <b>Rp {{ number_format($transaction->gross_amount, 0, ',', '.') }}</b> ke rekening tujuan?`
                : 'Berikan alasan penolakan jika Anda memilih menolak.',
            icon: isApprove ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonColor: isApprove ? '#28a745' : '#d33',
            confirmButtonText: isApprove ? 'Ya, Sudah Transfer' : 'Ya, Tolak',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#action-type').val(actionType);

                if (actionType === 'reject') {
                    Swal.fire({
                        title: 'Alasan Penolakan',
                        input: 'textarea',
                        inputPlaceholder: 'Tulis alasan penolakan di sini...',
                        showCancelButton: true,
                        confirmButtonText: 'Kirim Penolakan',
                        confirmButtonColor: '#d33',
                    }).then((rejectResult) => {
                        if (rejectResult.isConfirmed) {
                            $('#rejection_reason').val(rejectResult.value);
                            $('#withdrawal-action-form').submit();
                        }
                    });
                } else {
                    $('#withdrawal-action-form').submit();
                }
            }
        });
    }
</script>
@endpush
