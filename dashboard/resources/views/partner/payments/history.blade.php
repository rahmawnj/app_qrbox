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
                    "{{ route('export.partner-transactions', array_merge(request()->query(), ['type' => 'drop_off', 'status' => 'success'])) }}";
                var queryString = form.serialize();
                window.location.href = exportUrl + '?' + queryString;
            });
        });
    </script>
@endpush

@extends('layouts.dashboard.app')

@section('content')

    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="summary-card">
                <div class="icon-circle bg-primary">
                    <i class="fa fa-money-bill-wave"></i>
                </div>
                <div>
                    <h5 class="card-title">Total Pembayaran</h5>
                    <p class="card-text h3">{{ $totalPaymentsCountOverall }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="summary-card">
                <div class="icon-circle bg-success">
                    <i class="fa fa-wallet"></i>
                </div>
                <div>
                    <h5 class="card-title">Jumlah Uang Masuk Sukses</h5>
                    <p class="card-text h3">Rp {{ number_format($totalPaymentsAmountOverall, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

    </div>

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">{{ $title ?? '' }}</h4>
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
            <form method="GET" action="{{ route('partner.payments.history') }}" class="mb-3">
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
                            placeholder="Cari (ID Transaksi, Jumlah, Catatan)"
                            value="{{ request('search') }}">
                    </div>
                    {{-- Filter untuk metode pembayaran --}}
                    <div class="flex-fill" style="max-width: 180px;">
                        <select name="payment_method_filter" class="form-control form-control-sm">
                            <option value="">-- Semua Metode Pembayaran --</option>
                            <option value="member" {{ request('payment_method_filter') == 'member' ? 'selected' : '' }}>
                                Member</option>
                            <option value="cash" {{ request('payment_method_filter') == 'cash' ? 'selected' : '' }}>Tunai
                            </option>
                            <option value="non_cash"
                                {{ request('payment_method_filter') == 'non_cash' ? 'selected' : '' }}>Non-Tunai</option>
                        </select>
                    </div>
                    <div class="ms-auto">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Filter</button>
                        <a href="{{ route('partner.payments.history') }}" class="btn btn-default btn-sm"><i
                                class="fa fa-times"></i> Reset</a>
                        {{-- <a href="{{ route('export.partner-payments', request()->query()) }}"
                            class="btn btn-success btn-sm">
                            <i class="fa fa-file-excel"></i> Export ke Excel
                        </a> --}}
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="1%">#</th>
                            <th>ID Transaksi</th>
                            <th>Owner & Outlet</th>
                            <th>Jumlah</th>
                            <th>Metode Pembayaran</th>
                            <th>Tipe Pembayaran</th>
                            <th>Tanggal Pembayaran</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            @php
                                $paymentMethodDisplayName = 'N/A';
                                $paymentMethodBadgeClass = 'bg-secondary';
                                $paymentTypeDisplayName = 'N/A';

                                if ($payment->payment_method === 'member') {
                                    $paymentMethodDisplayName = 'Member';
                                    $paymentMethodBadgeClass = 'badge-self-service';
                                    $paymentTypeDisplayName = '-';
                                } elseif ($payment->payment_method === 'non_member') {
                                    $paymentMethodDisplayName = 'Non-Member';
                                    if ($payment->payment_type === 'cash') {
                                        $paymentTypeDisplayName = 'Tunai';
                                        $paymentMethodBadgeClass = 'badge-drop-off';
                                    } elseif ($payment->payment_type === 'non_cash') {
                                        $paymentTypeDisplayName = 'Non-Tunai';
                                        $paymentMethodBadgeClass = 'badge-qris';
                                    }
                                }

                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ optional($payment->transaction)->order_id ?? 'N/A' }}</td>
                                <td>
                                    <strong>{{ optional($payment->owner)->brand_name ?? '-' }}</strong><br>
                                    <small class="text-muted">
                                        Outlet: {{ optional($payment->outlet)->outlet_name ?? 'N/A' }}
                                    </small>
                                </td>
                                <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge badge-status {{ $paymentMethodBadgeClass }}">
                                        {{ $paymentMethodDisplayName }}
                                    </span>
                                </td>
                                <td>{{ $paymentTypeDisplayName }}</td>
                                <td>
                                    @if ($payment->payment_time)
                                        {{ \Carbon\Carbon::parse($payment->payment_time)->setTimezone($payment->timezone)->format('d-m-Y H:i') }}
                                        {{ strtoupper($transactionTimezone) }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ Str::limit($payment->notes ?? 'N/A', 50) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">Tidak ada data pembayaran ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="1">Total:</th>
                            <th colspan="2">{{ $totalFilteredPaymentsCount }} Pembayaran</th>
                            <th>Rp {{ number_format($totalFilteredPaymentsAmount, 0, ',', '.') }}</th>
                            <th colspan="4"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @if ($payments instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="pagination-container">
                    <div>Menampilkan {{ $payments->firstItem() }} hingga {{ $payments->lastItem() }} dari
                        {{ $payments->total() }} pembayaran</div>
                    <div>{{ $payments->appends(request()->query())->links() }}</div>
                </div>
            @else
                <div class="pagination-container">
                    <div>Menampilkan {{ $payments->count() }} pembayaran</div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <link href="{{ asset('assets/plugins/gritter/css/jquery.gritter.css') }}" rel="stylesheet" />
@endpush

@push('scripts')
    <script src="{{ asset('assets/plugins/gritter/js/jquery.gritter.js') }}"></script>
    <script>
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
    </script>
@endpush
