@extends('layouts.dashboard.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@section('content')
    <div class="row">
        <div class="col-lg-5">
            <h5 class="mb-3 fw-bold">Status Saldo</h5>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="text-muted small mb-1">Saldo Tersedia</div>
                    <div class="h2 fw-bold text-primary mb-0">
                        <span class="fs-4 fw-normal me-1">Rp</span>{{ number_format($availableBalance, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="fas fa-receipt me-2"></i>Rincian Biaya Penarikan</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between mb-2">
                            <span>Biaya Layanan</span>
                            <span class="fw-bold">Rp {{ number_format($withdrawalFee, 0, ',', '.') }}</span>
                        </li>
                        <li class="d-flex justify-content-between mb-2">
                            <span>Biaya Admin Bank</span>
                            @if($bankFee > 0)
                                <span class="fw-bold">Rp {{ number_format($bankFee, 0, ',', '.') }}</span>
                            @else
                                <span class="badge bg-success">FREE (BCA)</span>
                            @endif
                        </li>
                        <hr>
                        <li class="d-flex justify-content-between">
                            <span class="fw-bold">Total Potongan</span>
                            <span class="fw-bold text-danger">Rp {{ number_format($totalFeePerTransaction, 0, ',', '.') }}</span>
                        </li>
                    </ul>
                    <p class="text-muted small mt-3 mb-0">
                        *Saldo minimal mengendap: <strong>Rp 0</strong> (Bisa ditarik habis)
                    </p>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <h5 class="mb-3 fw-bold">Formulir Penarikan</h5>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('partner.withdrawal.store') }}" method="POST" id="withdrawalForm">
                        @csrf

                        <div class="p-3 rounded border mb-4 bg-light">
                            <div class="row small mb-1">
                                <div class="col-4 text-muted">Tujuan Bank</div>
                                <div class="col-8 fw-bold text-end">{{ getBrand()->bank_name }}</div>
                            </div>
                            <div class="row small">
                                <div class="col-4 text-muted">Nomor Rekening</div>
                                <div class="col-8 fw-bold text-end">{{ getBrand()->bank_account_number }}</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Jumlah Yang Ingin Diterima (Net)</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white border-end-0">Rp</span>
                                <input type="number" class="form-control border-start-0 ps-0 fw-bold shadow-none"
                                       id="amount" name="amount" placeholder="0" required>
                            </div>
                            <div id="amount-feedback" class="text-danger small mt-2"></div>
                        </div>

                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">Total Saldo Terpotong:</span>
                                <span class="h4 fw-bold mb-0 text-dark" id="total_final_display">Rp 0</span>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-bold" id="submitWithdrawalBtn">
                                AJUKAN PENARIKAN SEKARANG
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    const amountInput = $('#amount');
    const totalDisplay = $('#total_final_display');
    const submitBtn = $('#submitWithdrawalBtn');
    const feedback = $('#amount-feedback');

    // Pastikan variabel dari PHP ter-render dengan benar sebagai angka
    const availableBalance = {{ (int)$availableBalance }};
    const totalFee = {{ (int)$totalFeePerTransaction }};
    const minWithdrawal = {{ (int)$minWithdrawalAmount }};

    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    function calculate() {
        let val = parseInt(amountInput.val()) || 0;
        let totalToDeduct = val + totalFee;

        // Reset state UI
        amountInput.removeClass('is-invalid');
        feedback.text('');
        submitBtn.prop('disabled', false);

        // Update tampilan real-time
        totalDisplay.text('Rp ' + formatRupiah(totalToDeduct));

        // 1. Cek Input Kosong atau Nol
        if (val <= 0) {
            submitBtn.prop('disabled', true);
            return;
        }

        // 2. Cek Minimal Penarikan (misal Rp 5.000 atau Rp 50.000)
        if (val < minWithdrawal) {
            feedback.text('Minimal penarikan adalah Rp ' + formatRupiah(minWithdrawal));
            submitBtn.prop('disabled', true);
            return;
        }

        // 3. Cek Kecukupan Saldo
        // Syarat: Total Potong (Input + Fee) tidak boleh lebih dari Saldo
        if (totalToDeduct > availableBalance) {
            let maxNet = availableBalance - totalFee;
            maxNet = maxNet < 0 ? 0 : maxNet;

            feedback.text('Saldo tidak cukup. Maksimal penarikan bersih Anda adalah Rp ' + formatRupiah(maxNet));
            amountInput.addClass('is-invalid');
            submitBtn.prop('disabled', true);
        }
    }

    amountInput.on('input', calculate);

    $('#withdrawalForm').on('submit', function(e) {
        e.preventDefault();
        let val = parseInt(amountInput.val());
        let total = val + totalFee;

        Swal.fire({
            title: 'Konfirmasi Penarikan',
            html: `Anda akan menarik dana sebesar <b>Rp ${formatRupiah(val)}</b>.<br>` +
                 `Total saldo yang akan terpotong adalah <b>Rp ${formatRupiah(total)}</b> (termasuk biaya).`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Ajukan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit form secara manual
                this.submit();
            }
        });
    });
});
</script>
@endpush
