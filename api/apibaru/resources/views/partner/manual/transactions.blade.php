@props([
    'items' => ['Partner', 'Transaksi', 'Transaksi manual'],
    'title' => 'Transaksi manual',
    'subtitle' => 'Kelola dan pantau transaksi pembayaran manual.',
])

@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    {{-- Card Summary - Diperbaiki tampilannya agar lebih mirip screenshot --}}
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="widget widget-stats bg-blue">
                <div class="stats-icon"><i class="fa fa-money-bill-alt"></i></div>
                <div class="stats-info">
                    <h4>Total Transaksi (manual)</h4>
                    {{-- CHANGED: Use totalFilteredTransactionsCount for this card --}}
                    <p>{{ $totalFilteredTransactionsCount }}</p>
                </div>
                <div class="stats-link">
                    <a href="javascript:;">Lihat Detail <i class="fa fa-arrow-alt-circle-right"></i></a>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="widget widget-stats bg-green">
                <div class="stats-icon"><i class="fa fa-wallet"></i></div>
                <div class="stats-info">
                    <h4>Total Jumlah Uang (manual)</h4>
                    {{-- CHANGED: Use totalFilteredTransactionsAmount for this card --}}
                    <p>Rp {{ number_format($totalFilteredTransactionsAmount, 0, ',', '.') }}</p>
                </div>
                <div class="stats-link">
                    <a href="javascript:;">Lihat Detail <i class="fa fa-arrow-alt-circle-right"></i></a>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="widget widget-stats bg-yellow">
                <div class="stats-icon"><i class="fa fa-check-circle"></i></div>
                <div class="stats-info">
                    <h4>Transaksi Selesai (manual)</h4>
                    <p>{{ $completedTransactionsCount }}</p>
                </div>
                <div class="stats-link">
                    <a href="javascript:;">Lihat Detail <i class="fa fa-arrow-alt-circle-right"></i></a>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="widget widget-stats bg-purple">
                <div class="stats-icon"><i class="fa fa-mobile-alt"></i></div>
                <div class="stats-info">
                    <h4>Perangkat Teraktivasi</h4>
                    <p>{{ $activatedDeviceTransactionsCount }}</p>
                </div>
                <div class="stats-link">
                    <a href="javascript:;">Lihat Detail <i class="fa fa-arrow-alt-circle-right"></i></a>
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
            <form method="GET" action="{{ route('partner.manual.transactions') }}" class="mb-3">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <div class="input-group input-group-sm" style="max-width: 280px;">
                        <input type="text" name="daterange" class="form-control daterange-picker"
                            value="{{ request('daterange') }}" placeholder="Pilih Rentang Tanggal">
                        <span class="input-group-append">
                            <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                        </span>
                    </div>
                    {{-- NEW: Payment Method Filter --}}
                    <div class="input-group input-group-sm" style="max-width: 200px;">
                        <select name="payment_method" class="form-control form-control-sm">
                            <option value="">Semua Metode Pembayaran</option>
                            <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Tunai</option>
                            <option value="non_cash" {{ request('payment_method') == 'non_cash' ? 'selected' : '' }}>Non-Tunai</option>
                        </select>
                    </div>
                    {{-- END NEW --}}
                    <div class="flex-fill">
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Cari (ID Pesanan, Owner, Jumlah)" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    {{-- Filter and Reset buttons on the left --}}
                    <div>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Filter</button>
                        <a href="{{ route('partner.manual.transactions') }}" class="btn btn-default btn-sm"><i
                                class="fa fa-times"></i> Reset</a>
                    </div>
                    {{-- Export button on the right --}}
                    <div class="ms-auto">
                        <a href="{{ route('export.partner-transactions', array_merge(request()->query(), ['type' => 'manual', 'status' => 'success'])) }}"
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
                            <th width="1">#</th>
                            <th>ID Pesanan</th>
                            <th>Owner & Outlet</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)

                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{ $transaction->order_id }}
                                </td>
                                <td>
                                    <strong>{{ $transaction->owner->brand_name ?? '-' }}</strong><br>
                                    <small class="text-muted">
                                        Outlet: {{ $transaction->outlet->address ?? 'N/A' }}<br>
                                        Device: {{ $transaction->device_code }}
                                    </small>
                                </td>
                                <td>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                <td><span class="badge bg-success">{{ ucfirst($transaction->status) }}</span></td>
                                <td>
                                    {{ $transaction->created_at->setTimezone($transaction->timezone)->format('d-m-Y H:i') }}
                                    {{ strtoupper($transaction->timezone) }}
                                </td>
                                <td>
                                    <x-print-invoice :transaction="$transaction" />

                                    @if ($transaction->manualTransaction)
                                        <a href="#modal-dialog-{{ $transaction->id }}" class="btn btn-info btn-sm"
                                            data-bs-toggle="modal">
                                            <i class="fa fa-info-circle"></i>
                                        </a>

                                        <div class="modal fade" id="modal-dialog-{{ $transaction->id }}" tabindex="-1"
                                            role="dialog" aria-labelledby="manualDetailModalLabel{{ $transaction->id }}"
                                            aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="manualDetailModalLabel{{ $transaction->id }}">Detail
                                                            Transaksi manual #{{ $transaction->order_id }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <h6><i class="fa fa-qrcode"></i> Detail manual</h6>
                                                                <hr class="mt-1 mb-2">
                                                                <dl class="row">
                                                                    <dt class="col-sm-4">Payment URL:</dt>
                                                                    <dd class="col-sm-8"><a
                                                                            href="{{ $transaction->manualTransaction->payment_url }}"
                                                                            target="_blank">{{ Str::limit($transaction->manualTransaction->payment_url, 40) }}</a>
                                                                    </dd>
                                                                </dl>
                                                                <div class="text-center mt-3">
                                                                    @if ($transaction->manualTransaction->qr_code_image)
                                                                        <img src="{{ asset( $transaction->manualTransaction->qr_code_image) }}"
                                                                            alt="QR Code" class="img-thumbnail"
                                                                            style="max-width: 200px; height: auto;">
                                                                    @else
                                                                        <p class="text-muted">Gambar QR Code tidak
                                                                            tersedia.</p>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6><i class="fa fa-info-circle"></i> Data Transaksi Utama
                                                                </h6>
                                                                <hr class="mt-1 mb-2">
                                                                <dl class="row">
                                                                    <dt class="col-sm-4">Order ID:</dt>
                                                                    <dd class="col-sm-8">
                                                                        {{ $transaction->order_id ?? 'Belum Lengkap' }}
                                                                    </dd>

                                                                    <dt class="col-sm-4">Jumlah:</dt>
                                                                    <dd class="col-sm-8">Rp
                                                                        {{ number_format($transaction->amount, 0) ?? 'Belum Lengkap' }}
                                                                    </dd>

                                                                    <dt class="col-sm-4">Owner:</dt>
                                                                    <dd class="col-sm-8">
                                                                        {{ $transaction->owner->brand_name ?? 'Belum Lengkap' }}
                                                                    </dd>

                                                                    <dt class="col-sm-4">Outlet:</dt>
                                                                    <dd class="col-sm-8">
                                                                        {{ $transaction->outlet->outlet_name ?? 'Belum Lengkap' }}
                                                                        ({{ $transaction->outlet->address ?? 'Belum Lengkap' }})
                                                                    </dd>

                                                                    <dt class="col-sm-4">Device Code:</dt>
                                                                    <dd class="col-sm-8">
                                                                        {{ $transaction->device_code ?? 'Belum Lengkap' }}
                                                                    </dd>

                                                                    <dt class="col-sm-4">Status Transaksi:</dt>
                                                                    <dd class="col-sm-8"><span
                                                                            class="badge bg-success">{{ ucfirst($transaction->status) ?? 'Belum Lengkap' }}</span>
                                                                    </dd>

                                                                    {{-- Display payment method --}}
                                                                    <dt class="col-sm-4">Metode Pembayaran:</dt>
                                                                    <dd class="col-sm-8">
                                                                        {{ ucfirst(str_replace('_', ' ', $transaction->manualTransaction->payment_method ?? 'N/A')) }}
                                                                    </dd>

                                                                    <dt class="col-sm-4">Tanggal Transaksi:</dt>
                                                                    <dd class="col-sm-8">
                                                                        {{ $transaction->created_at->setTimezone($tz)->format('d-m-Y H:i') }}
                                                                        {{ strtoupper($transaction->timezone) }}</dd>
                                                                </dl>
                                                            </div>
                                                        </div>

                                                        <h6 class="mt-4"><i class="fa fa-desktop"></i> Detail Transaksi
                                                            Perangkat</h6>
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
                                                            <p class="text-muted">Tidak ada detail transaksi perangkat
                                                                untuk transaksi ini.</p>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">Detail tidak tersedia</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada transaksi manual berhasil ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    {{-- FOOTER TABEL - Menggunakan totalFilteredTransactionsAmount dan totalFilteredTransactionsCount --}}
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total Jumlah</strong></td>
                            <td><strong>Rp {{ number_format($totalFilteredTransactionsAmount, 0, ',', '.') }}</strong></td>
                            <td colspan="4"></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Jumlah Transaksi</strong></td>
                            <td><strong>{{ $totalFilteredTransactionsCount }}</strong></td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                {{-- Text di bawah pagination tetap menunjukkan total record yang ditemukan --}}
                <div>Menampilkan {{ $transactions->firstItem() }} - {{ $transactions->lastItem() }} dari
                    {{ $transactions->total() }} transaksi ditemukan</div>
                <div>{{ $transactions->links() }}</div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}" />
    <script type="text/javascript" src="{{ asset('assets/plugins/moment/min/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.js') }}">
    </script>

    <script>
        $(function() {
            // Inisialisasi Date Range Picker
            $('.daterange-picker').daterangepicker({
                opens: 'left',
                locale: {
                    format: 'YYYY-MM-DD',
                    applyLabel: 'Terapkan',
                    cancelLabel: 'Batal',
                    fromLabel: 'Dari',
                    toLabel: 'Sampai',
                    customRangeLabel: 'Rentang Kustom',
                    daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                    monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
                        'September', 'Oktober', 'November', 'Desember'
                    ],
                    firstDay: 1
                },
                ranges: {
                    'Hari Ini': [moment(), moment()],
                    'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                    '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                    'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                    'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'Tahun Ini': [moment().startOf('year'), moment().endOf('year')],
                    'Tahun Lalu': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
                }
            });

            // Set nilai input saat tanggal dipilih
            $('.daterange-picker').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format(
                    'YYYY-MM-DD'));
            });

            // Hapus nilai input jika filter di-reset
            $('.daterange-picker').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });

            // Set nilai awal datepicker jika ada di URL
            var daterange = "{{ request('daterange') }}";
            if (daterange) {
                $('.daterange-picker').val(daterange);
            }
        });
    </script>
@endpush
