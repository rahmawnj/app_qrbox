@props([
    'items' => ['Partner', 'Manajemen Outlet', 'Riwayat Topup'],
    'title' => 'Riwayat Topup Outlet',
    'subtitle' => 'Lihat histori transaksi topup dari outlet',
])

@push('styles')
    <link href="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet" />
    <style>
        /* Reusing and refining styles from the transaction page for consistency */
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

        /* Badge for Topup Channel */
        .badge-qris {
            background-color: #0d6efd; /* Blue */
            color: #fff;
        }

        .badge-cashier {
            background-color: #6c757d; /* Gray */
            color: #fff;
        }

        .badge-other {
            background-color: #17a2b8; /* Info Blue */
            color: #fff;
        }

        /* Badge for Topup Status */
        .badge-success-topup {
            background-color: #28a745;
            color: #fff;
        }

        .badge-pending-topup {
            background-color: #ffc107;
            color: #343a40;
        }

        .badge-failed-topup {
            background-color: #dc3545;
            color: #fff;
        }

        /* Pagination alignment */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
        }

        .pagination-container nav {
            margin-bottom: 0;
        }

        /* Modern Cards for Summary */
        .summary-card {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: .5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
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

        /* Specific icon circle colors */
        .icon-circle.bg-primary { background-color: #007bff !important; }
        .icon-circle.bg-success { background-color: #28a745 !important; }
        .icon-circle.bg-info { background-color: #17a2b8 !important; }
        .icon-circle.bg-warning { background-color: #ffc107 !important; }
        .icon-circle.bg-danger { background-color: #dc3545 !important; }

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
            // Initialize daterangepicker
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
                    customRangeLabel: 'Custom'
                },
                ranges: {
                    'Hari Ini': [moment(), moment()],
                    'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                    '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                    'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                    'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            });

            // Set initial value for daterange input if a value exists
            var initialDateRange = "{{ request('daterange') }}";
            if (initialDateRange) {
                $('#filter-daterange').val(initialDateRange);
            }

            $('#filter-daterange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            });

            $('#filter-daterange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
        });
    </script>
@endpush

@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="summary-card h-100">
                <div class="icon-circle bg-primary">
                    <i class="fa fa-money-bill-transfer"></i> {{-- Icon for Total Topups --}}
                </div>
                <div>
                    <h5 class="card-title">Total Topup</h5>
                    <p class="card-text h3">{{ $totalTopupsCount ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="summary-card h-100">
                <div class="icon-circle bg-success">
                    <i class="fa fa-wallet"></i> {{-- Icon for Total Topup Amount --}}
                </div>
                <div>
                    <h5 class="card-title">Total Jumlah Topup</h5>
                    <p class="card-text h3">Rp {{ number_format($totalTopupsAmount ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="summary-card h-100">
                <div class="icon-circle bg-info">
                    <i class="fa fa-cash-register"></i> {{-- Icon for Cashier Topups (example) --}}
                </div>
                <div>
                    <h5 class="card-title">Topup via Kasir</h5>
                    <p class="card-text h3">{{ $cashierTopupsCount ?? 0 }}</p>
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
            <form method="GET" action="{{ route('partner.topup.histories') }}" class="mb-4">
                <input type="hidden" name="out" value="{{ request()->get('out') }}">
                <div class="row g-3">
                    <div class="col-md-3 col-sm-6">
                        <label for="filter-daterange" class="form-label visually-hidden">Rentang Waktu</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                            <input type="text" name="daterange" class="form-control" id="filter-daterange"
                                value="{{ request('daterange') }}" placeholder="Pilih Rentang Waktu">
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label for="filter-status" class="form-label visually-hidden">Status</label>
                        <select name="status" id="filter-status" class="form-control form-control-sm">
                            <option value="">-- Semua Status --</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Sukses</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Gagal</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label for="filter-channel" class="form-label visually-hidden">Metode Topup</label>
                        <select name="channel" id="filter-channel" class="form-control form-control-sm">
                            <option value="">-- Semua Metode --</option>
                            <option value="cashier" {{ request('channel') == 'cashier' ? 'selected' : '' }}>Kasir</option>
                            <option value="qris" {{ request('channel') == 'qris' ? 'selected' : '' }}>QRIS</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label for="filter-search" class="form-label visually-hidden">Pencarian</label>
                        <input type="text" name="search" id="filter-search" class="form-control form-control-sm"
                            placeholder="Nama Member, No. Hp, Catatan" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="filter-actions">
                    <div class="filter-left">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa fa-filter me-1"></i> Terapkan Filter
                        </button>
                        <a href="{{ route('partner.topup.histories', ['out' => request()->get('out')]) }}"
                            class="btn btn-default btn-sm">
                            <i class="fa fa-redo me-1"></i> Reset
                        </a>
                    </div>
                    {{-- You can add an Export button here if needed --}}
                    {{-- <div class="filter-right">
                        <button type="button" id="export-button" class="btn btn-success btn-sm">
                            <i class="fa fa-file-excel me-1"></i> Export ke Excel
                        </button>
                    </div> --}}
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="1%">#</th>
                            <th>Outlet</th>
                            <th>Member</th>
                            <th class="text-end">Jumlah Topup</th>
                            <th>Saldo Awal</th>
                            <th>Saldo Akhir</th>
                            <th>Metode Topup</th>
                            <th>Status</th>
                            <th>Waktu</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topupHistories as $history)
                            @php

                                $channelType = '';
                                $channelBadgeClass = 'badge-other'; // Default
                                if ($history->qris_transaction_detail_id) {
                                    $channelType = 'QRIS';
                                    $channelBadgeClass = 'badge-qris';
                                } elseif ($history->cashier_name) {
                                    $channelType = 'Kasir';
                                    $channelBadgeClass = 'badge-cashier';
                                } else {
                                    $channelType = 'Lain-lain';
                                }

                                $statusBadgeClass = '';
                                switch ($history->status) {
                                    case 'success':
                                        $statusBadgeClass = 'badge-success-topup';
                                        break;
                                    case 'pending':
                                        $statusBadgeClass = 'badge-pending-topup';
                                        break;
                                    case 'failed':
                                        $statusBadgeClass = 'badge-failed-topup';
                                        break;
                                    default:
                                        $statusBadgeClass = 'bg-secondary';
                                        break;
                                }
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration + ($topupHistories->currentPage() - 1) * $topupHistories->perPage() }}</td>
                                <td>
                                    <strong>{{ $history->owner->brand_name ?? '-' }}</strong><br>
                                    <small class="text-muted">{{ $history->outlet->outlet_name ?? '-' }}</small>
                                </td>
                                <td>
                                    <strong>{{ $history->member->user->name ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $history->member->phone_number ?? '-' }}</small>
                                </td>
                                <td class="text-end">Rp {{ number_format($history->amount, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($history->initial_balance, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($history->final_balance, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge badge-status {{ $channelBadgeClass }}">
                                        {{ $channelType }}
                                    </span>
                                    @if ($channelType === 'Kasir')
                                        <br><small class="text-muted">{{ $history->cashier_name ?? '-' }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-status {{ $statusBadgeClass }}">
                                        {{ ucfirst($history->status) }}
                                    </span>
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($history->time)->setTimezone($history->timezone)->format('d-m-Y H:i:s') }}
                                    <small class="text-muted">{{ strtoupper($history->timezone) }}</small>
                                </td>
                                <td>{{ Str::limit($history->notes, 50, '...') ?? '-' }}</td> {{-- Limit notes length --}}
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">Tidak ada data topup ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3">Total:</th>
                            <th class="text-end">Rp {{ number_format($totalTopupsAmount ?? 0, 0, ',', '.') }}</th>
                            <th colspan="6"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if ($topupHistories instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="pagination-container">
                    <div>Menampilkan {{ $topupHistories->firstItem() }} hingga {{ $topupHistories->lastItem() }} dari
                        {{ $topupHistories->total() }} riwayat topup</div>
                    <div>{{ $topupHistories->appends(request()->query())->links() }}</div>
                </div>
            @else
                <div class="pagination-container">
                    <div>Menampilkan {{ $topupHistories->count() }} riwayat topup</div>
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
