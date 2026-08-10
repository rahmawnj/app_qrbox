@props([
    'items' => ['Admin', 'Withdrawal Management', 'Withdrawal Histories'],
    'title' => 'Riwayat Penarikan Dana',
    'subtitle' => 'Lihat riwayat seluruh penarikan dana oleh owner.',
])

@push('styles')
    <link href="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet" />
    <style>
        /* Base Card Styles - Inherited from Partner's view for consistency */
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
            background-color: #d1ecf1;
            /* Light blue background */
            border-color: #bee5eb;
            /* Light blue border */
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            height: 100%;
            /* Fill column height */
            justify-content: center;
            /* Vertically center content */
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

@push('scripts')
    <script src="{{ asset('assets/plugins/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script>
        $(function() {
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
        });
    </script>
@endpush

@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    <div class="card-grid-container">
        {{-- Kartu Besar: Total Dana Belum Ditarik (Global) --}}
        <div>
            <div class="summary-card large-card bg-info-light"> {{-- Added bg-info-light for better visual --}}
                <div class="icon-circle bg-info-dark">
                    <i class="fa fa-money-bill-wave"></i> {{-- Icon for total funds --}}
                </div>
                <div>
                    <h5 class="card-title">Total Dana Belum Ditarik (Global)</h5>
                    <p class="card-text h3">Rp {{ number_format($totalUnwithdrawnFunds, 0, ',', '.') }}</p>
                    <p class="card-text small-desc">Akumulasi saldo yang belum ditarik oleh semua owner.</p>
                </div>
            </div>
        </div>

        {{-- Grid untuk Kartu-kartu Kecil (Ringkasan Global) --}}
        <div class="small-cards-grid">
            <div class="summary-card">
                <div class="icon-circle bg-primary">
                    <i class="fa fa-clipboard-list"></i>
                </div>
                <div>
                    <h5 class="card-title">Total Permintaan</h5>
                    <p class="card-text h3">{{ $totalGlobalWithdrawalsCount }}</p>
                    <p class="card-text small-desc">Jumlah seluruh permintaan penarikan dari semua owner.</p>
                </div>
            </div>
            <div class="summary-card">
                <div class="icon-circle bg-success">
                    <i class="fa fa-check-circle"></i>
                </div>
                <div>
                    <h5 class="card-title">Disetujui</h5>
                    <p class="card-text h3">Rp {{ number_format($approvedGlobalWithdrawalsAmount, 0, ',', '.') }}</p>
                    <p class="card-text small-desc">{{ $approvedGlobalWithdrawalsCount }} permintaan.</p>
                </div>
            </div>
            <div class="summary-card">
                <div class="icon-circle bg-danger">
                    <i class="fa fa-times-circle"></i>
                </div>
                <div>
                    <h5 class="card-title">Ditolak</h5>
                    <p class="card-text h3">Rp {{ number_format($rejectedGlobalWithdrawalsAmount, 0, ',', '.') }}</p>
                    <p class="card-text small-desc">{{ $rejectedGlobalWithdrawalsCount }} permintaan.</p>
                </div>
            </div>
            <div class="summary-card">
                <div class="icon-circle bg-warning-dark">
                    <i class="fa fa-hourglass-half"></i>
                </div>
                <div>
                    <h5 class="card-title">Pending</h5>
                    <p class="card-text h3">Rp {{ number_format($pendingGlobalWithdrawalsAmount, 0, ',', '.') }}</p>
                    <p class="card-text small-desc">{{ $pendingGlobalWithdrawalsCount }} permintaan sedang diproses.</p>
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

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="1%">#</th>
                            <th>Owner (Brand)</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th>Diajukan</th>
                            <th>Dikonfirmasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($withdrawalHistories as $withdrawal)
                            @php

                                $statusBadgeClass = '';
                                switch ($withdrawal->status) {
                                    case 'pending':
                                        $statusBadgeClass = 'bg-warning text-dark';
                                        break;
                                    case 'approved':
                                        $statusBadgeClass = 'bg-success';
                                        break;
                                    case 'rejected':
                                        $statusBadgeClass = 'bg-danger';
                                        break;
                                    default:
                                        $statusBadgeClass = 'bg-secondary';
                                        break;
                                }
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration + ($withdrawalHistories->currentPage() - 1) * $withdrawalHistories->perPage() }}
                                </td>
                                <td>
                                    <strong>{{ $withdrawal->owner->brand_name ?? '-' }}</strong><br>
                                    <small class="text-muted">{{ $withdrawal->owner->user->name ?? '-' }}</small>
                                </td>
                                <td>Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $statusBadgeClass }}">
                                        {{ ucfirst($withdrawal->status) }}
                                    </span>
                                </td>
                                <td>{{ $withdrawal->notes ?? '-' }}</td>
                                <td>
                                    {{ $withdrawal->created_at->setTimezone($withdrawal->timezone)->format('d-m-Y H:i') }}
                                    <small
                                        class="text-muted d-block">{{ strtoupper($withdrawal->timezone ?? 'WIB') }}</small>
                                </td>
                                <td>
                                    @if ($withdrawal->approved_at)
                                        {{ \Carbon\Carbon::parse($withdrawal->approved_at)->setTimezone($withdrawal->timezone)->format('d-m-Y H:i') }}
                                        <small
                                            class="text-muted d-block">{{ strtoupper($withdrawal->timezone ?? 'WIB') }}</small>
                                    @elseif ($withdrawal->rejected_at)
                                        {{-- Menampilkan tanggal ditolak jika ada --}}
                                        Ditolak pada
                                        {{ \Carbon\Carbon::parse($withdrawal->rejected_at)->setTimezone($withdrawal->timezone)->format('d-m-Y H:i') }}
                                        <small
                                            class="text-muted d-block">{{ strtoupper($withdrawal->timezone ?? 'WIB') }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($withdrawal->status == 'pending')
                                        <a href="{{ route('admin.withdrawal.request', $withdrawal->id) }}"
                                            class="btn btn-sm btn-info" title="Lihat Detail & Proses">
                                            <i class="fas fa-eye"></i> Proses
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Tidak ada riwayat penarikan dana
                                    yang sesuai dengan filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">Total yang Ditampilkan:</th>
                            <th>Rp {{ number_format($totalAmountInTable, 0, ',', '.') }}</th>
                            <th colspan="3"></th>
                            <th>Total Permintaan (Filter):</th>
                            <th>{{ $totalCountInTable }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="pagination-container">
                <div>Menampilkan {{ $withdrawalHistories->firstItem() }} hingga {{ $withdrawalHistories->lastItem() }}
                    dari
                    {{ $withdrawalHistories->total() }} riwayat penarikan</div>
                <div>{{ $withdrawalHistories->appends(request()->query())->links() }}</div>
            </div>

        </div>
    </div>
@endsection
