@props([
'items' => ['Admin', 'Withdrawal Management', 'Withdrawal Histories'],
'title' => 'Riwayat Penarikan Dana',
'subtitle' => 'Lihat riwayat seluruh penarikan dana oleh owner.',
])

@extends('layouts.dashboard.app')

@push('styles')
<link href="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet" />
<style>
/* Base Card Styles */
.summary-card {
background-color: #ffffff;
border: 1px solid #e0e0e0;
border-radius: .5rem;
box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
padding: 1.5rem;
display: flex;
align-items: center;
gap: 1rem;
margin-bottom: 1rem;
}

    /* Large Card Specific Styles */
    .summary-card.large-card {
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 1rem;
        background-color: #d1ecf1; /* Light blue background */
        border-color: #bee5eb; /* Light blue border */
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        height: 100%; /* Fill column height */
        justify-content: center; /* Vertically center content */
    }

    .summary-card.large-card .icon-circle {
        width: 70px;
        height: 70px;
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    .summary-card.large-card .card-title {
        font-size: 1.25rem;
        color: #0c5460;
    }

    .summary-card.large-card .card-text.h3 {
        font-size: 2.5rem;
        font-weight: bold;
        color: #0c5460;
    }

    .summary-card.large-card .card-text.small-desc {
        font-size: 1rem;
        color: #3a8e9e;
    }

    /* Small Card Icon Circle Colors */
    .summary-card .icon-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 1.5rem;
        color: #ffffff;
        flex-shrink: 0;
    }

    .icon-circle.bg-info-dark {
        background-color: #0d6efd !important;
    }

    .icon-circle.bg-blue {
        background-color: #007bff !important;
    }

    .icon-circle.bg-success {
        background-color: #28a745 !important;
    }

    .icon-circle.bg-danger {
        background-color: #dc3545 !important;
    }

    .icon-circle.bg-secondary {
        background-color: #6c757d !important;
    }

    .icon-circle.bg-primary {
        background-color: #007bff !important;
    }

    .icon-circle.bg-warning-dark {
        background-color: #ffc107 !important;
        color: #343a40 !important;
    }

    .summary-card .card-title {
        font-size: 1rem;
        color: #6c757d;
        margin-bottom: 0.25rem;
    }

    .summary-card .card-text.h3 {
        font-size: 1.75rem;
        margin-bottom: 0.5rem;
        color: #343a40;
    }

    .summary-card .card-text.small-desc {
        font-size: 0.875rem;
        color: #999;
        margin-bottom: 0;
    }

    /* Filter & Table Specific Styles */
    .filter-group {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 15px;
    }

    .filter-group .form-control {
        height: calc(1.5em + .75rem + 2px);
    }

    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
    }

    .pagination-container nav {
        margin-bottom: 0;
    }

    /* Grid for Cards */
    .card-grid-container {
        display: grid;
        grid-template-columns: 1.2fr 2fr;
        gap: 1rem;
        margin-bottom: 1.5rem;
        align-items: stretch;
    }

    .small-cards-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    /* Table row clickable style */
    .table tbody tr {
        cursor: pointer;
    }
    .table tbody tr:hover {
        background-color: #f5f5f5;
    }

    /* Modal detail styles */
    .modal-body .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px dashed #eee;
    }
    .modal-body .detail-row:last-child {
        border-bottom: none;
    }
    .modal-body .detail-label {
        font-weight: bold;
        color: #555;
    }
    .modal-body .detail-value {
        color: #333;
        text-align: right;
    }

    @media (max-width: 992px) {
        .card-grid-container {
            grid-template-columns: 1fr;
        }
        .small-cards-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 768px) {
        .small-cards-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

@endpush

@section('content')
<x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

<div class="row mb-3">
    {{-- Kartu Utama: Saldo Aktif Global --}}
    <div class="col-md-6">
        <div class="card border-0 bg-info text-white mb-3">
            <div class="card-body">
                <div class="mb-3 text-white-transparent-8">
                    <b>TOTAL DANA BELUM DITARIK (GLOBAL)</b>
                </div>
                <h3 class="mb-10 text-white">Rp {{ number_format($totalUnwithdrawnFunds, 0, ',', '.') }}</h3>
                <p class="mb-0 small">Total akumulasi saldo dari seluruh Owner yang tersedia di sistem.</p>
            </div>
        </div>
    </div>

    {{-- Kartu Ringkasan Penarikan --}}
    <div class="col-md-6">
        <div class="row">
            <div class="col-6">
                <div class="card border-0 bg-blue text-white mb-3">
                    <div class="card-body">
                        <div class="text-white-transparent-8 small"><b>TOTAL TRANSAKSI</b></div>
                        <h4 class="text-white">{{ $totalGlobalWithdrawalsCount }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 bg-success text-white mb-3">
                    <div class="card-body">
                        <div class="text-white-transparent-8 small"><b>TOTAL DANA DITARIK</b></div>
                        <h4 class="text-white">Rp {{ number_format($totalGlobalWithdrawalsAmount, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

   <div class="panel panel-inverse mb-4">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <i class="fa fa-filter me-2"></i>Filter Data
                </h4>
            </div>
            <div class="panel-body bg-light">
                <form action="{{ url()->current() }}" method="GET" id="filterForm">
                    <div class="d-flex flex-wrap gap-4">
                        <div class="flex-fill" style="min-width:260px;">
                            <label class="form-label fw-bold">
                                <i class="fa fa-calendar-alt me-2 text-primary"></i>
                                Rentang Waktu Analisis
                            </label>
                            <div class="input-group">
                                <input
                                    type="text"
                                    name="daterange"
                                    id="filter-daterange"
                                    class="form-control border-primary"
                                    value="{{ $daterange }}"
                                >
                                <span class="input-group-text bg-primary text-white">
                                    <i class="fa fa-calendar"></i>
                                </span>
                            </div>
                        </div>

                        <div class="flex-fill" style="min-width:260px;">
                            <label class="form-label fw-bold">
                                <i class="fa fa-search me-2 text-primary"></i>
                                Cari ID Pesanan / Brand
                            </label>
                            <input
                                type="text"
                                name="search"
                                class="form-control border-primary"
                                placeholder="Masukkan kata kunci..."
                                value="{{ request('search') }}"
                            >
                        </div>

                    </div>
                    <hr class="my-4">

                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="text-muted small me-auto">
                            <i class="fa fa-info-circle"></i>
                            Hasil pencarian disesuaikan dengan filter yang dipilih.
                        </div>
                        <a href="{{ url()->current() }}" class="btn btn-white w-100px">
                            <i class="fa fa-undo me-1"></i> Reset
                        </a>
                        <button type="submit" class="btn btn-primary w-120px">
                            <i class="fa fa-sync me-1"></i> Update Data
                        </button>
                    </div>
                </form>
            </div>
        </div>

<div class="panel panel-inverse">
    <div class="panel-heading">
        <h4 class="panel-title">Riwayat Penarikan Dana</h4>
    </div>
    <div class="panel-body">


        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th width="1%">No</th>
                        <th>Owner (Brand)</th>
                        <th>Rekening Tujuan</th>
                        <th>Nominal Penarikan</th>
                        <th>Biaya Admin</th>
                        <th>Dana Ditransfer</th>
                        <th>Waktu Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($withdrawalHistories as $withdrawal)
                        <tr>
                            <td>{{ $loop->iteration + ($withdrawalHistories->currentPage() - 1) * $withdrawalHistories->perPage() }}</td>
                            <td>
                                <strong>{{ $withdrawal->owner->brand_name ?? '-' }}</strong><br>
                                <small class="text-muted">{{ $withdrawal->owner->user->name ?? '-' }}</small>
                            </td>
                            <td>
                                <small>
                                    {{ $withdrawal->bank_name }}<br>
                                    {{ $withdrawal->bank_account_number }}<br>
                                    a/n {{ $withdrawal->bank_account_holder_name }}
                                </small>
                            </td>
                            <td class="text-nowrap text-dark">Rp {{ number_format($withdrawal->requested_amount, 0, ',', '.') }}</td>
                            <td class="text-danger">- Rp {{ number_format($withdrawal->withdrawal_fee, 0, ',', '.') }}</td>
                            <td class="text-success fw-bold">Rp {{ number_format($withdrawal->amount_after_fee, 0, ',', '.') }}</td>
                            <td>
                                {{ $withdrawal->created_at->format('d/m/Y') }}<br>
                                <small class="text-muted">{{ $withdrawal->created_at->format('H:i') }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Belum ada data riwayat penarikan.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-light fw-bold">
                    <tr>
                        <td colspan="3" class="text-end">TOTAL:</td>
                        <td>Rp {{ number_format($totalAmountInTable, 0, ',', '.') }}</td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="small text-muted">
                Menampilkan {{ $withdrawalHistories->firstItem() }} - {{ $withdrawalHistories->lastItem() }} dari {{ $withdrawalHistories->total() }} data
            </div>
            <div>{{ $withdrawalHistories->appends(request()->query())->links() }}</div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/moment/min/moment.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
<script>
$(function() {
// Daterangepicker initialization
$('#filter-daterange').daterangepicker({
timePicker: false,
showDropdowns: true,
autoUpdateInput: false,
autoApply: false,
locale: {
format: 'YYYY-MM-DD',
separator: ' - ',
cancelLabel: 'Clear',
applyLabel: 'Terapkan',
customRangeLabel: 'Custom',
daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
firstDay: 1
},
ranges: {
'Hari Ini': [moment(), moment()],
'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
'7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
'30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
'month').endOf('month')]
}
});

        var initialDateRange = "{{ request('daterange') }}";
        if (initialDateRange) {
            $('#filter-daterange').val(initialDateRange);
        }

        $('#filter-daterange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format(
                'YYYY-MM-DD'));
        });

        $('#filter-daterange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        // Function to format number as Indonesian Rupiah
        function formatRupiah(number) {
            if (typeof number === 'string' && number.toLowerCase() === 'n/a') {
                return number; // Return 'N/A' as is
            }
            // Ensure number is parsed as float before formatting to handle potential string numbers
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(parseFloat(number));
        }

        // Admin Withdrawal Detail Modal Logic
        var adminWithdrawalDetailModal = document.getElementById('adminWithdrawalDetailModal');
        adminWithdrawalDetailModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget; // Button that triggered the modal
            var row = $(button).closest('tr'); // Get the table row

            // Extract data from data-* attributes
            var id = row.data('id');
            var ownerName = row.data('owner-name');
            var brandName = row.data('brand-name');
            var requestedAmount = row.data('requested-amount');
            var withdrawalFee = row.data('withdrawal-fee');
            var netAmountTransferred = row.data('net-amount-transferred');
            var amountBeforeFee = row.data('amount-before-fee');
            var amountAfterFee = row.data('amount-after-fee');
            var status = row.data('status');
            var notes = row.data('notes');
            var createdAt = row.data('created-at');
            var approvedAt = row.data('approved-at');
            var rejectedAt = row.data('rejected-at'); // Get rejected_at
            var bankName = row.data('bank-name');
            var bankAccountNumber = row.data('bank-account-number');
            var bankAccountHolderName = row.data('bank-account-holder-name');

            // Update modal content
            adminWithdrawalDetailModal.querySelector('#detail-id').textContent = id;
            adminWithdrawalDetailModal.querySelector('#detail-owner-brand').textContent = brandName;
            adminWithdrawalDetailModal.querySelector('#detail-owner-name').textContent = ownerName;
            adminWithdrawalDetailModal.querySelector('#detail-requested-amount').textContent = formatRupiah(requestedAmount);
            adminWithdrawalDetailModal.querySelector('#detail-withdrawal-fee').textContent = formatRupiah(withdrawalFee);
            adminWithdrawalDetailModal.querySelector('#detail-net-amount-transferred').textContent = formatRupiah(netAmountTransferred);
            adminWithdrawalDetailModal.querySelector('#detail-status').textContent = status;
            adminWithdrawalDetailModal.querySelector('#detail-notes').textContent = notes;
            adminWithdrawalDetailModal.querySelector('#detail-created-at').textContent = createdAt;
            adminWithdrawalDetailModal.querySelector('#detail-approved-at').textContent = approvedAt;
            adminWithdrawalDetailModal.querySelector('#detail-rejected-at').textContent = rejectedAt; // Set rejected_at
            adminWithdrawalDetailModal.querySelector('#detail-bank-name').textContent = bankName;
            adminWithdrawalDetailModal.querySelector('#detail-bank-account-number').textContent = bankAccountNumber;
            adminWithdrawalDetailModal.querySelector('#detail-bank-account-holder-name').textContent = bankAccountHolderName;
            adminWithdrawalDetailModal.querySelector('#detail-amount-before-fee').textContent = formatRupiah(amountBeforeFee);
            adminWithdrawalDetailModal.querySelector('#detail-amount-after-fee').textContent = formatRupiah(amountAfterFee);

            // Admin action buttons in modal footer
            var actionButtonsContainer = adminWithdrawalDetailModal.querySelector('#admin-action-buttons');
            actionButtonsContainer.innerHTML = ''; // Clear previous buttons


        });
    });
</script>

@endpush
