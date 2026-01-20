@props([
    'title' => 'Transaksi Drop Off',
])

@push('styles')
    <link href="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet" />
    <style>
        /* Custom styles for better table and filter appearance */
        .panel-body .form-control-sm {
            height: calc(1.5em + .5rem + 2px);
            padding: .25rem .5rem;
            font-size: .875rem;
            line-height: 1.5;
            border-radius: .2rem;
        }

        .filter-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-group .input-group {
            flex: 1;
            min-width: 180px;
        }

        .filter-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
        }

        .filter-left,
        .filter-right {
            display: flex;
            gap: 10px;
        }

        .table thead th {
            vertical-align: middle;
            white-space: nowrap;
        }

        .table tbody td {
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: #f5f5f5;
        }

        .badge-status {
            padding: .4em .6em;
            border-radius: .25rem;
            font-size: 85%;
            font-weight: 600;
            display: inline-block;
        }

        .badge-pending {
            background-color: #ffc107;
            color: #343a40;
        }

        .badge-success {
            background-color: #28a745;
            color: #fff;
        }

        .badge-failed {
            background-color: #dc3545;
            color: #fff;
        }

        .badge-drop-off {
            background-color: #6c757d;
            color: #fff;
        }

        .badge-self-service {
            background-color: #0d6efd;
            color: #fff;
        }

        .badge-qris {
            /* Badge style for QRIS */
            background-color: #fd7e14;
            /* Orange */
            color: #fff;
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

        /* NEW STYLES FOR MODERN CARDS */
        .summary-card {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: .5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            height: 100%;
            /* Ensure equal height */
        }

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

        .icon-circle.bg-primary {
            background-color: #007bff !important;
        }

        .icon-circle.bg-success {
            background-color: #28a745 !important;
        }

        .icon-circle.bg-info {
            background-color: #17a2b8 !important;
        }

        .icon-circle.bg-yellow {
            background-color: #ffc107 !important;
        }

        /* Corrected for consistency */
        .icon-circle.bg-danger {
            background-color: #dc3545 !important;
        }

        .icon-circle.bg-purple {
            background-color: #6f42c1 !important;
        }

        /* Added for consistency */

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
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('assets/plugins/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.js') }}"></script>

    <script>
        $(function() {
            // Inisialisasi Date Range Picker
            $('#filter-daterange').daterangepicker({
                timePicker: false,
                showDropdowns: true,
                autoUpdateInput: false, // Prevents auto-updating the input field until "Apply"
                autoApply: false, // Prevents auto-closing the picker
                locale: {
                    format: 'YYYY-MM-DD',
                    separator: ' - ',
                    cancelLabel: 'Clear',
                    applyLabel: 'Terapkan',
                    customRangeLabel: 'Custom'
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

            // Set nilai input saat tanggal dipilih
            var initialDateRange = "{{ request('daterange') }}";
            if (initialDateRange) {
                $('#filter-daterange').val(initialDateRange);
            } else {
                var today = moment().format('YYYY-MM-DD');
                $('#filter-daterange').val(today + ' - ' + today);
            }

            // Event listener for when the 'Apply' button is clicked in the daterangepicker
            $('#filter-daterange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format(
                    'YYYY-MM-DD'));
                $(this).closest('form').submit();
            });

            // Event listener for when the 'Clear' button is clicked in the daterangepicker
            $('#filter-daterange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $(this).closest('form').submit();
            });

            // Handle export button click
            $('#export-button').on('click', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                // Pastikan rute export sesuai dengan kebutuhan drop-off
                var exportUrl =
                    "{{ route('export.admin-transactions', array_merge(request()->query(), ['type' => 'drop_off', 'status' => 'success'])) }}";
                var queryString = form.serialize();
                window.location.href = exportUrl + '?' + queryString;
            });
        });
    </script>
@endpush

@extends('layouts.dashboard.app')

@section('content')
    <div class="row mb-4">
        {{-- Card Total Pembayaran & Top-up --}}
        <div class="col-xl-6 col-md-6 mb-3">
            <div class="summary-card">
                <div class="icon-circle bg-primary">
                    <i class="fa fa-money-bill-wave"></i>
                </div>
                <div>
                    <h5 class="card-title">Total Transaksi QRIS</h5>
                    {{-- Menggunakan totalCombinedCountOverall dari controller --}}
                    <p class="card-text h3">{{ $totalCombinedCountOverall }}</p>
                </div>
            </div>
        </div>
        {{-- Card Jumlah Uang Masuk Sukses --}}
        <div class="col-xl-6 col-md-6 mb-3">
            <div class="summary-card">
                <div class="icon-circle bg-success">
                    <i class="fa fa-wallet"></i>
                </div>
                <div>
                    <h5 class="card-title">Jumlah Uang Masuk Sukses</h5>
                    {{-- Menggunakan totalCombinedAmountOverall dari controller --}}
                    <p class="card-text h3">Rp {{ number_format($totalCombinedAmountOverall, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">{{ $title ?? 'Riwayat Transaksi QRIS' }}</h4>
            <div class="panel-heading-btn">
                <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand">
                    <i class="fa fa-expand"></i>
                </a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload">
                    <i class="fa fa-redo"></i>
                </a>
            </div>
        </div>
        <div class="panel-body">
            <form method="GET" action="{{ route('admin.payments.history') }}" class="mb-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="input-group input-group-sm" style="max-width: 280px;">
                        <input type="text" name="daterange" id="filter-daterange" class="form-control daterange-picker"
                            value="{{ request('daterange') }}" placeholder="Pilih Rentang Tanggal">
                        <span class="input-group-append">
                            <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                        </span>
                    </div>
                    <div class="flex-fill">
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Cari (ID Transaksi, Jumlah, Catatan)" value="{{ request('search') }}">
                    </div>
                    {{-- Filter untuk metode pembayaran - Dihapus karena hanya QRIS --}}
                    {{-- Filter untuk status pembayaran --}}
                    <div class="flex-fill" style="max-width: 180px;">
                        <select name="status" class="form-control form-control-sm">
                            <option value="">-- Semua Status --</option>
                            <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Sukses
                            </option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu
                            </option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Gagal
                            </option>
                        </select>
                    </div>
                    <div class="ms-auto">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Filter</button>
                        <a href="{{ route('admin.payments.history') }}" class="btn btn-default btn-sm"><i
                                class="fa fa-times"></i> Reset</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="1%">#</th>
                            <th>Tipe Transaksi</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Tanggal Dibuat</th>
                            <th>Tanggal Diubah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($paginatedTransactions as $item)
                            @php
                                $displayAmount = optional($item->transactionable)->amount ?? $item->amount;
                                $status = optional($item->transactionable)->status ?? ($item->status ?? 'N/A');

                                $statusBadgeClass = '';
                                switch ($status) {
                                    case 'pending':
                                        $statusBadgeClass = 'bg-warning';
                                        break;
                                    case 'success':
                                        $statusBadgeClass = 'bg-success';
                                        break;
                                    case 'failed':
                                        $statusBadgeClass = 'bg-danger';
                                        break;
                                    default:
                                        $statusBadgeClass = 'bg-secondary';
                                        break;
                                }
                            @endphp
                            <tr>
                                <td>{{ ($paginatedTransactions->currentPage() - 1) * $paginatedTransactions->perPage() + $loop->iteration }}
                                </td>
                                <td>
                                    @if ($item->transactionable_type === 'App\Models\Payment' || $item->type === 'payment')
                                        Pembayaran Laundry
                                    @else
                                        Topup
                                    @endif
                                </td>
                                <td>Rp {{ number_format($displayAmount, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $statusBadgeClass }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td>{{ $item->created_at->format('d-m-Y H:i') }} WIB</td>
                                <td>
                                    @if (isset($item->transactionable) && $item->transactionable->updated_at)
                                        {{ $item->transactionable->updated_at->format('d-m-Y H:i') }} WIB
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">Tidak ada data transaksi QRIS ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">Total (Difilter):</th>
                            <th>Rp {{ number_format($totalFilteredAmount, 0, ',', '.') }}</th>
                            <th colspan="2">{{ $totalFilteredCount }} Transaksi</th>
                            <th></th>
                        </tr>
                    </tfoot>

                </table>
            </div>

        </div>
    </div>
@endsection


@push('styles')
    <link href="{{ asset('assets/plugins/gritter/css/jquery.gritter.css') }}" rel="stylesheet" />
@endpush
