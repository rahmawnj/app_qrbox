@props([
    'items' => ['Partner', 'Transaksi', 'Transaksi Drop Off'],
    'title' => 'Transaksi Drop Off',
    'subtitle' => 'Kelola dan pantau seluruh transaksi drop off.',
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
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    <div class="row">
        {{-- Card 1: Total Transaksi --}}
        <div class="col-md-3">
            <div class="widget widget-stats bg-blue mb-10px">
                <div class="stats-icon stats-icon-lg"><i class="fa fa-list-ul fa-fw"></i></div>
                <div class="stats-content">
                    <div class="stats-title">Total Transaksi</div>
                    <div class="stats-number">{{ $totalTransactionsCount }}</div>
                    <div class="stats-desc">Jumlah total transaksi non-member yang sukses.</div>
                </div>
            </div>
        </div>

        {{-- Card 2: Total Pendapatan Cash --}}
        <div class="col-md-3">
            <div class="widget widget-stats bg-yellow mb-10px">
                <div class="stats-icon stats-icon-lg"><i class="fa fa-money-bill-wave fa-fw"></i></div>
                <div class="stats-content">
                    <div class="stats-title">Total Pendapatan Cash</div>
                    <div class="stats-number">Rp{{ number_format($totalCashRevenue, 0, ',', '.') }}</div>
                    <div class="stats-desc">Pendapatan dari pembayaran tunai.</div>
                </div>
            </div>
        </div>

        {{-- Card 3: Total Pendapatan Non-Cash --}}
        <div class="col-md-3">
            <div class="widget widget-stats bg-green mb-10px">
                <div class="stats-icon stats-icon-lg"><i class="fa fa-credit-card fa-fw"></i></div>
                <div class="stats-content">
                    <div class="stats-title">Total Pendapatan Non-Cash</div>
                    <div class="stats-number">Rp{{ number_format($totalNonCashRevenue, 0, ',', '.') }}</div>
                    <div class="stats-desc">Pendapatan dari pembayaran non-tunai.</div>
                </div>
            </div>
        </div>

        {{-- Card 4: Perangkat Aktif --}}
        <div class="col-md-3">
            <div class="widget widget-stats bg-red mb-10px">
                <div class="stats-icon stats-icon-lg"><i class="fa fa-power-off fa-fw"></i></div>
                <div class="stats-content">
                    <div class="stats-title">Perangkat Aktif</div>
                    <div class="stats-number">{{ $totalActiveDevices }}</div>
                    <div class="stats-desc">Jumlah perangkat yang berstatus aktif ('true').</div>
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


            {{-- Pastikan Anda telah menyertakan CSS yang diperlukan untuk kelas 'widget', 'bg-blue', 'stats-icon', dll., dan Font Awesome --}}



<form method="GET" action="{{ route('partner.transactions.drop-off.non-member') }}" class="mb-3">
    {{-- Baris 1: Input Filter --}}
    <div class="row g-2 mb-2">
        <div class="col-md-4 col-lg-3">
            <div class="input-group input-group-sm">
                <input type="text" name="daterange" id="filter-daterange" class="form-control daterange-picker"
                    value="{{ request('daterange') }}" placeholder="Pilih Rentang Tanggal">
                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
            </div>
        </div>

        <div class="col-md-4 col-lg-5">
            <input type="text" name="search" class="form-control form-control-sm"
                placeholder="Cari (ID Pesanan, Owner, Jumlah, Nama Pelanggan, Catatan)"
                value="{{ request('search') }}">
        </div>

        <div class="col-md-4 col-lg-4">
            <select name="payment_method_filter" class="form-control form-control-sm">
                <option value="">-- Semua Metode Pembayaran --</option>
                <option value="cash" {{ request('payment_method_filter') == 'cash' ? 'selected' : '' }}>Tunai</option>
                <option value="non_cash" {{ request('payment_method_filter') == 'non_cash' ? 'selected' : '' }}>Non-Tunai</option>
            </select>
        </div>
    </div>

    {{-- Baris 2: Tombol Aksi --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <button type="submit" class="btn btn-primary btn-sm me-2">
                <i class="fa fa-filter"></i> Filter
            </button>
            <a href="{{ route('partner.transactions.drop-off.non-member') }}" class="btn btn-default btn-sm me-2">
                <i class="fa fa-times"></i> Reset
            </a>
        </div>
        <div class="ms-auto">
            <a href="{{ route('export.partner-transactions', array_merge(request()->query(), ['type' => 'drop_off', 'status' => 'success'])) }}"
                class="btn btn-success btn-sm">
                <i class="fa fa-file-excel"></i> Export ke Excel
            </a>
        </div>
    </div>
</form>


            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="1%">#</th>
                            <th>ID Pesanan</th>
                            <th>Owner & Outlet</th>
                            <th>Pelanggan & Catatan</th>
                            <th>Jumlah</th>
                            <th>Tipe Pembayaran</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            @php

                                $paymentMethodDisplayName = 'N/A';
                                $paymentMethodBadgeClass = 'bg-secondary';
                                // Removed $qrisDetail initialization as it's not relevant for drop-off QRIS details

// Logic to determine paymentMethodDisplayName and paymentMethodBadgeClass
if ($transaction->member) {
    // If transaction has a member_id, it's a Member transaction
                                    $paymentMethodDisplayName = 'Member';
                                    $paymentMethodBadgeClass = 'badge-self-service'; // Badge for Member
                                } elseif ($transaction->payments->isNotEmpty()) {
                                    // If no member_id, check the first payment for type (cash/non_cash)
                                    $payment = $transaction->payments->first();
                                    if ($payment->payment_method === 'non_member') {
                                        if ($payment->payment_type === 'cash') {
                                            $paymentMethodDisplayName = 'Tunai';
                                            $paymentMethodBadgeClass = 'badge-drop-off'; // Badge for Tunai
                                        } elseif ($payment->payment_type === 'non_cash') {
                                            $paymentMethodDisplayName = 'Non-Tunai';
                                            $paymentMethodBadgeClass = 'badge-qris'; // Badge for Non-Tunai
                                        } else {
                                            // Fallback for non_member with unknown payment_type
                                            $paymentMethodDisplayName = 'Non-Member (Unknown Type)';
                                            $paymentMethodBadgeClass = 'bg-secondary';
                                        }
                                    } else {
                                        // This case would imply payment_method is 'member' in payments table
                                        // but transaction->member is null, indicating a potential data inconsistency.
                                        $paymentMethodDisplayName = 'Non-Member (Payment Method Mismatch)';
                                        $paymentMethodBadgeClass = 'bg-danger'; // Indicate an issue
                                    }
                                } else {
                                    // No member and no payments found for this transaction
                                    $paymentMethodDisplayName = 'N/A (No Payment Data)';
                                    $paymentMethodBadgeClass = 'bg-secondary';
                                }

                                $statusBadgeClass = '';
                                switch ($transaction->status) {
                                    case 'pending':
                                        $statusBadgeClass = 'badge-pending';
                                        break;
                                    case 'success':
                                        $statusBadgeClass = 'badge-success';
                                        break;
                                    case 'failed':
                                        $statusBadgeClass = 'badge-failed';
                                        break;
                                    default:
                                        $statusBadgeClass = 'bg-secondary';
                                        break;
                                }
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $transaction->order_id }}</td>
                                <td>
                                    <strong>{{ $transaction->owner->brand_name ?? '-' }}</strong><br>
                                    <small class="text-muted">
                                        Outlet: {{ $transaction->outlet->outlet_name ?? 'N/A' }}
                                    </small>
                                </td>
                                <td>
                                    @if ($transaction->dropOffTransaction)
                                        <strong>{{ $transaction->dropOffTransaction->customer_name ?? 'N/A' }}</strong><br>
                                        <small class="text-muted">
                                            Catatan: {{ Str::limit($transaction->dropOffTransaction->notes ?? 'N/A', 30) }}
                                        </small>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                <td>
                                    {{-- CORRECTED: Use the pre-calculated display name and badge class --}}
                                    <span class="badge badge-status {{ $paymentMethodBadgeClass }}">
                                        {{ $paymentMethodDisplayName }}
                                    </span>
                                </td>
                                <td><span
                                        class="badge badge-status {{ $statusBadgeClass }}">{{ ucfirst($transaction->status) }}</span>
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($transaction->created_at)->setTimezone($transaction->timezone)->format('d-m-Y H:i') }}
                                    {{ strtoupper($transaction->timezone) }}
                                </td>
                                <td>
                                    <x-print-invoice :transaction="$transaction" />
                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#dropOffDetailModal{{ $transaction->id }}">
                                        <i class="fa fa-info-circle"></i>
                                    </button>
                                    <div class="modal fade" id="dropOffDetailModal{{ $transaction->id }}" tabindex="-1"
                                        role="dialog" aria-labelledby="dropOffDetailModalLabel{{ $transaction->id }}"
                                        aria-hidden="true">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"
                                                        id="dropOffDetailModalLabel{{ $transaction->id }}">
                                                        Detail
                                                        Transaksi Drop Off #{{ $transaction->order_id }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6><i class="fa fa-info-circle"></i> Data Transaksi Utama</h6>
                                                            <hr class="mt-1 mb-2">
                                                            <dl class="row">
                                                                <dt class="col-sm-4">Order ID:</dt>
                                                                <dd class="col-sm-8">{{ $transaction->order_id ?? 'N/A' }}
                                                                </dd>

                                                                <dt class="col-sm-4">Jumlah:</dt>
                                                                <dd class="col-sm-8">Rp
                                                                    {{ number_format($transaction->amount, 0) ?? 'N/A' }}
                                                                </dd>

                                                                <dt class="col-sm-4">Owner:</dt>
                                                                <dd class="col-sm-8">
                                                                    {{ $transaction->owner->brand_name ?? 'N/A' }}</dd>

                                                                <dt class="col-sm-4">Outlet:</dt>
                                                                <dd class="col-sm-8">
                                                                    {{ $transaction->outlet->outlet_name ?? 'N/A' }}
                                                                    ({{ $transaction->outlet->address ?? 'N/A' }})
                                                                </dd>

                                                                <dt class="col-sm-4">Status Transaksi:</dt>
                                                                <dd class="col-sm-8"><span
                                                                        class="badge bg-success">{{ ucfirst($transaction->status) ?? 'N/A' }}</span>
                                                                </dd>

                                                                <dt class="col-sm-4">Tanggal Transaksi:</dt>
                                                                <dd class="col-sm-8">
                                                                    {{ \Carbon\Carbon::parse($transaction->created_at)->setTimezone($transaction->timezone)->format('d-m-Y H:i') }}
                                                                    {{ strtoupper($transaction->timezone) }}
                                                                </dd>
                                                            </dl>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6><i class="fa fa-user"></i> Detail Pelanggan Drop Off</h6>
                                                            <hr class="mt-1 mb-2">
                                                            <dl class="row">
                                                                <dt class="col-sm-4">Tipe Pembayaran:</dt>
                                                                <dd class="col-sm-8">
                                                                    {{-- CORRECTED: Use the pre-calculated display name and badge class --}}
                                                                    <span
                                                                        class="badge badge-status {{ $paymentMethodBadgeClass }}">
                                                                        {{ $paymentMethodDisplayName }}
                                                                    </span>
                                                                </dd>
                                                                @if ($transaction->member)
                                                                    <dt class="col-sm-4">Nama Member:</dt>
                                                                    <dd class="col-sm-8">
                                                                        {{ $transaction->member->user->name ?? 'N/A' }}
                                                                    </dd>
                                                                    <dt class="col-sm-4">Telepon Member:</dt>
                                                                    <dd class="col-sm-8">
                                                                        {{ $transaction->member->phone_number ?? 'N/A' }}
                                                                    </dd>
                                                                @else
                                                                    <dt class="col-sm-4">Tipe Pelanggan:</dt>
                                                                    <dd class="col-sm-8">Non-Member</dd>
                                                                @endif

                                                                @if ($transaction->dropOffTransaction)
                                                                    <dt class="col-sm-4">Nama Pelanggan:</dt>
                                                                    <dd class="col-sm-8">
                                                                        {{ $transaction->dropOffTransaction->customer_name ?? 'N/A' }}
                                                                    </dd>
                                                                    <dt class="col-sm-4">Telepon Pelanggan:</dt>
                                                                    <dd class="col-sm-8">
                                                                        {{ $transaction->dropOffTransaction->customer_phone ?? 'N/A' }}
                                                                    </dd>
                                                                    <dt class="col-sm-4">Catatan:</dt>
                                                                    <dd class="col-sm-8">
                                                                        {{ $transaction->dropOffTransaction->notes ?? 'N/A' }}
                                                                    </dd>
                                                                    <dt class="col-sm-4">Status Pesanan:</dt>
                                                                    <dd class="col-sm-8"><span
                                                                            class="badge badge-status badge-drop-off">{{ ucfirst($transaction->dropOffTransaction->order_status ?? 'N/A') }}</span>
                                                                    </dd>
                                                                @endif

                                                                {{-- Removed QRIS details block as it's not relevant for drop-off non-cash payments --}}
                                                            </dl>
                                                        </div>
                                                    </div>

                                                    <h6 class="mt-4"><i class="fa fa-desktop"></i> Detail Aktivasi
                                                        Perangkat
                                                    </h6>
                                                    <hr class="mt-1 mb-2">
                                                    @if ($transaction->deviceTransactions->isNotEmpty())
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered table-sm mt-3">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Kode Perangkat</th>
                                                                        <th>Tipe Layanan</th>
                                                                        <th>Status Jalankan</th>
                                                                        <th>Waktu Aktivasi</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($transaction->deviceTransactions as $deviceTrans)
                                                                        <tr>
                                                                            <td>{{ $deviceTrans->device->code ?? $deviceTrans->device_code }}
                                                                            </td>
                                                                            <td>{{ ucfirst($deviceTrans->service_type) }}
                                                                            </td>
                                                                            <td>
                                                                                @if ($deviceTrans->activated_at)
                                                                                    <span
                                                                                        class="badge bg-success">Dijalankan</span>
                                                                                @else
                                                                                    <span
                                                                                        class="badge bg-warning text-dark">Belum
                                                                                        Dijalankan</span>
                                                                                @endif
                                                                            </td>
                                                                            <td>
                                                                                @if ($deviceTrans->activated_at)
                                                                                    {{ \Carbon\Carbon::parse($deviceTrans->activated_at)->setTimezone($deviceTrans->timezone)->format('d-m-Y H:i') }}
                                                                                @else
                                                                                    N/A
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @else
                                                        <p class="text-muted">Tidak ada detail aktivasi perangkat untuk
                                                            transaksi
                                                            ini.</p>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Tutup</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            {{-- Modal Detail Transaksi Drop Off --}}

                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">Tidak ada transaksi drop off ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="1">Total:</th>
                            <th colspan="3">{{ $totalTransactionsCount }} Transaksi</th>
                            <th colspan="5"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @if ($transactions instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="pagination-container">
                    <div>Menampilkan {{ $transactions->firstItem() }} hingga {{ $transactions->lastItem() }} dari
                        {{ $transactions->total() }} transaksi</div>
                    <div>{{ $transactions->appends(request()->query())->links() }}</div>
                </div>
            @else
                <div class="pagination-container">
                    <div>Menampilkan {{ $transactions->count() }} transaksi</div>
                    {{-- Tidak ada link pagination jika tidak dipaginasi --}}
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
