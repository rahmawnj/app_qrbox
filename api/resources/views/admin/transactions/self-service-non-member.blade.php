@props([
    'items' => ['admin', 'Transaksi', 'Transaksi Self Service Non-Member'],
    'title' => 'Transaksi Self Service Non-Member',
    'subtitle' => 'Kelola dan pantau seluruh transaksi self service non-member (QRIS).',
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
            background-color: #fd7e14;
            color: #fff;
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

        .icon-circle.bg-danger {
            background-color: #dc3545 !important;
        }

        .icon-circle.bg-purple {
            background-color: #6f42c1 !important;
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
                autoUpdateInput: false, // Prevent auto-updating the input field until "Apply"
                autoApply: false, // Prevent auto-closing the picker
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

            // Set initial value for daterange input if a value exists from the request
            var initialDateRange = "{{ request('daterange') }}";
            if (initialDateRange) {
                $('#filter-daterange').val(initialDateRange);
            }
            // HAPUS logika default pengisian tanggal 'hari ini' di sini
            // sehingga input dibiarkan kosong jika tidak ada request('daterange')

            // Event listener for when the 'Apply' button is clicked in the daterangepicker
            $('#filter-daterange').on('apply.daterangepicker', function(ev, picker) {
                // Set the input value and submit the form
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format(
                    'YYYY-MM-DD'));
                $(this).closest('form').submit();
            });

            // Event listener for when the 'Clear' button is clicked in the daterangepicker
            $('#filter-daterange').on('cancel.daterangepicker', function(ev, picker) {
                // Clear the input value and submit the form to reset the filter
                $(this).val('');
                $(this).closest('form').submit();
            });

            // Handle export button click
            $('#export-button').on('click', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                var exportUrl =
                    "{{ route('export.admin-transactions') }}"; // Perbarui ini jika rute berbeda
                var queryString = form.serialize(); // Get all form data as query string
                window.location.href = exportUrl + '?' + queryString;
            });
        });
    </script>
@endpush

@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    <div class="row">
        <div class="col-md-3">
            <div class="widget widget-stats bg-blue mb-10px">
                <div class="stats-icon stats-icon-lg"><i class="fa fa-list-ul fa-fw"></i></div>
                <div class="stats-content">
                    <div class="stats-title">Total Transaksi</div>
                    <div class="stats-number">{{ $totalTransactionsCount }}</div>
                    <div class="stats-desc">Total transaksi non-member yang sukses</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="widget widget-stats bg-yellow mb-10px">
                <div class="stats-icon stats-icon-lg"><i class="fa fa-money-bill-wave fa-fw"></i></div>
                <div class="stats-content">
                    <div class="stats-title">Total Pendapatan</div>
                    <div class="stats-number">Rp{{ number_format($totalRevenue, 0, ',', '.') }}</div>
                    <div class="stats-desc">Pendapatan dari transaksi non-member</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="widget widget-stats bg-green mb-10px">
                <div class="stats-icon stats-icon-lg"><i class="fa fa-power-off fa-fw"></i></div>
                <div class="stats-content">
                    <div class="stats-title">Perangkat Aktif</div>
                    <div class="stats-number">{{ $activeDevicesCount }}</div>
                    <div class="stats-desc">Jumlah perangkat yang berhasil diaktifkan</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="widget widget-stats bg-red mb-10px">
                <div class="stats-icon stats-icon-lg"><i class="fa fa-times-circle fa-fw"></i></div>
                <div class="stats-content">
                    <div class="stats-title">Perangkat Belum Aktif</div>
                    <div class="stats-number">{{ $totalDevicesInvolved - $activeDevicesCount }}</div>
                    <div class="stats-desc">Jumlah perangkat yang belum diaktifkan</div>
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
            <form method="GET" action="{{ route('admin.transactions.self-service.non-member') }}" class="mb-3">
                {{-- Baris 1: Input Filter --}}
                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <input type="text" name="daterange" id="filter-daterange" class="form-control daterange-picker" autocomplete="off"
                                value="{{ request('daterange') }}" placeholder="Pilih Rentang Tanggal">
                            <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Cari (ID Pesanan, Owner, Jumlah, Kode Perangkat, Nama Pelanggan)"
                            value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Baris 2: Tombol Aksi --}}
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <button type="submit" class="btn btn-primary btn-sm me-2">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.transactions.self-service.non-member') }}" class="btn btn-default btn-sm me-2">
                            <i class="fa fa-times"></i> Reset
                        </a>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('export.admin-transactions', array_merge(request()->query(), ['type' => 'self_service', 'status' => 'success'])) }}"
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
                            <th>Kode Perangkat</th>
                            <th>Jumlah</th>
                            <th>Tipe Pembayaran</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $selfServiceTransaction)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $selfServiceTransaction->order_id ?? 'N/A' }}</td>
                                <td>
                                    <strong>{{ $selfServiceTransaction->owner->brand_name ?? '-' }}</strong><br>
                                    <small class="text-muted">
                                        Outlet: {{ $selfServiceTransaction->outlet->outlet_name ?? 'N/A' }}
                                    </small>
                                </td>
                                <td>
                                    @php
                                        // Ambil semua kode perangkat dari relasi deviceTransactions
                                        $deviceCodes = $selfServiceTransaction->deviceTransactions->pluck('device.code')->filter()->implode(', ');
                                        if (empty($deviceCodes)) {
                                            $deviceCodes = $selfServiceTransaction->deviceTransactions->pluck('device_code')->filter()->implode(', ');
                                        }
                                        $deviceCodes = $deviceCodes ?: 'N/A';
                                    @endphp
                                    {{ $deviceCodes }}
                                </td>
                                <td>Rp {{ number_format($selfServiceTransaction->amount ?? 0, 0, ',', '.') }}</td>
                                <td>
                                    @php
                                        $paymentMethodDisplayName = 'N/A';
                                        $paymentMethodBadgeClass = 'bg-secondary';
                                        $payment = $selfServiceTransaction->payments->first();
                                        if ($payment) {
                                            if ($payment->payment_method === 'non_member') {
                                                $paymentMethodDisplayName = 'QRIS';
                                                $paymentMethodBadgeClass = 'badge-qris bg-info';
                                            } else {
                                                $paymentMethodDisplayName = ucfirst($payment->payment_method);
                                                $paymentMethodBadgeClass = 'badge-secondary';
                                            }
                                        }
                                    @endphp
                                    <span class="badge {{ $paymentMethodBadgeClass }}">
                                        {{ $paymentMethodDisplayName }}
                                    </span>
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($selfServiceTransaction->created_at)->setTimezone($selfServiceTransaction->timezone)->format('d-m-Y H:i') }}
                                    <br>
                                    <small>{{ strtoupper($selfServiceTransaction->timezone) }}</small>
                                </td>
                                <td>
                                    <x-print-invoice :transaction="$selfServiceTransaction" />
                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#selfServiceDetailModal{{ $selfServiceTransaction->id }}">
                                        <i class="fa fa-info-circle"></i>
                                    </button>
                                    {{-- Modal Detail Transaksi Self Service --}}
                                    <div class="modal fade" id="selfServiceDetailModal{{ $selfServiceTransaction->id }}" tabindex="-1" role="dialog"
                                        aria-labelledby="selfServiceDetailModalLabel{{ $selfServiceTransaction->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="selfServiceDetailModalLabel{{ $selfServiceTransaction->id }}">Detail Transaksi Self Service #{{ $selfServiceTransaction->order_id }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6><i class="fa fa-info-circle"></i> Data Transaksi Utama</h6>
                                                            <hr class="mt-1 mb-2">
                                                            <dl class="row">
                                                                <dt class="col-sm-4">Order ID:</dt>
                                                                <dd class="col-sm-8">{{ $selfServiceTransaction->order_id ?? 'N/A' }}</dd>

                                                                <dt class="col-sm-4">Jumlah:</dt>
                                                                <dd class="col-sm-8">Rp {{ number_format($selfServiceTransaction->amount ?? 0, 0) }}</dd>

                                                                <dt class="col-sm-4">Owner:</dt>
                                                                <dd class="col-sm-8">{{ $selfServiceTransaction->owner->brand_name ?? 'N/A' }}</dd>

                                                                <dt class="col-sm-4">Outlet:</dt>
                                                                <dd class="col-sm-8">
                                                                    {{ $selfServiceTransaction->outlet->outlet_name ?? 'N/A' }} ({{ $selfServiceTransaction->outlet->address ?? 'N/A' }})
                                                                </dd>

                                                                <dt class="col-sm-4">Status Transaksi:</dt>
                                                                <dd class="col-sm-8">
                                                                    @php
                                                                        $statusBadgeClass = '';
                                                                        switch ($selfServiceTransaction->status) {
                                                                            case 'pending': $statusBadgeClass = 'badge-pending bg-warning text-dark'; break;
                                                                            case 'success': $statusBadgeClass = 'badge-success bg-success'; break;
                                                                            case 'failed':  $statusBadgeClass = 'badge-failed bg-danger'; break;
                                                                            default: $statusBadgeClass = 'bg-secondary'; break;
                                                                        }
                                                                    @endphp
                                                                    <span class="badge {{ $statusBadgeClass }}">{{ ucfirst($selfServiceTransaction->status) ?? 'N/A' }}</span>
                                                                </dd>

                                                                <dt class="col-sm-4">Tanggal Transaksi:</dt>
                                                                <dd class="col-sm-8">
                                                                    {{ \Carbon\Carbon::parse($selfServiceTransaction->created_at)->setTimezone($selfServiceTransaction->timezone)->format('d-m-Y H:i') }}
                                                                    {{ strtoupper($selfServiceTransaction->timezone) }}
                                                                </dd>
                                                            </dl>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6><i class="fa fa-cogs"></i> Detail Pembayaran & Pelanggan</h6>
                                                            <hr class="mt-1 mb-2">
                                                            <dl class="row">
                                                                <dt class="col-sm-4">Tipe Pembayaran:</dt>
                                                                <dd class="col-sm-8">
                                                                    <span class="badge {{ $paymentMethodBadgeClass }}">
                                                                        {{ $paymentMethodDisplayName }}
                                                                    </span>
                                                                </dd>
                                                                <dt class="col-sm-4">Tipe Pelanggan:</dt>
                                                                <dd class="col-sm-8">Non-Member</dd>

                                                                @if ($payment && $payment->payment_method === 'non_member')
                                                                    @php
                                                                        $qrisDetail = $payment->qrisTransactionDetail;
                                                                    @endphp
                                                                    @if ($qrisDetail)
                                                                        <dt class="col-sm-4">URL Pembayaran:</dt>
                                                                        <dd class="col-sm-8">
                                                                            <a href="{{ $qrisDetail->payment_url }}" target="_blank">{{ Str::limit($qrisDetail->payment_url, 40) }}</a>
                                                                        </dd>
                                                                        <dt class="col-sm-4">QR Code:</dt>
                                                                        <dd class="col-sm-8">
                                                                            @if ($qrisDetail->qr_code_image)
                                                                                <img src="{{ asset('storage/' . $qrisDetail->qr_code_image) }}"
                                                                                    alt="QR Code" class="img-thumbnail"
                                                                                    style="max-width: 150px; height: auto;">
                                                                            @else
                                                                                Tidak Tersedia
                                                                            @endif
                                                                        </dd>
                                                                    @endif
                                                                @endif
                                                            </dl>
                                                        </div>
                                                    </div>

                                                    <h6 class="mt-4"><i class="fa fa-desktop"></i> Detail Aktivasi Perangkat</h6>
                                                    <hr class="mt-1 mb-2">
                                                @if ($selfServiceTransaction->deviceTransactions->isNotEmpty())
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-sm mt-3">
                                                            <thead>
                                                                <tr>
                                                                    <th>Kode Perangkat</th>
                                                                    <th>Tipe Layanan</th>
                                                                    <th>Waktu Aktif</th>
                                                                    <th>Status Jalankan</th>
                                                                    <th>Waktu Dijalankan</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($selfServiceTransaction->deviceTransactions as $deviceTrans)
                                                                    <tr>
                                                                        <td>{{ $deviceTrans->device->code ?? $deviceTrans->device_code }}</td>
                                                                        <td>{{ ucfirst($deviceTrans->service_type) }}</td>
                                                                        <td>
                                                                            @if ($deviceTrans->bypass_activation)
                                                                                {{ \Carbon\Carbon::parse($deviceTrans->bypass_activation)->setTimezone($selfServiceTransaction->timezone)->format('d-m-Y H:i') }}
                                                                            @else
                                                                                N/A
                                                                            @endif
                                                                        </td>
                                                                        <td>
                                                                            @if ($deviceTrans->status == 0)
                                                                                <span class="badge bg-success">Dijalankan</span>
                                                                            @else
                                                                                <span class="badge bg-warning text-dark">Belum Dijalankan</span>
                                                                            @endif
                                                                        </td>
                                                                        <td>
                                                                            @if ($deviceTrans->activated_at)
                                                                                {{ \Carbon\Carbon::parse($deviceTrans->activated_at)->setTimezone($selfServiceTransaction->timezone)->format('d-m-Y H:i') }}
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
                                                    <p class="text-muted">Tidak ada detail aktivasi perangkat untuk transaksi ini.</p>
                                                @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">Tidak ada transaksi self service ditemukan.</td>
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
