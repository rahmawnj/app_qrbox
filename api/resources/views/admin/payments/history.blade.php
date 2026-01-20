@extends('layouts.dashboard.app')

@section('content')
<x-breadcrumb :items="['Admin', 'Pembayaran', 'Riwayat']" title="Riwayat Pembayaran" subtitle="Daftar seluruh uang masuk dari transaksi" />

<div class="row">
    <div class="col-md-3">
        <div class="widget widget-stats bg-blue mb-10px">
            <div class="stats-icon stats-icon-lg"><i class="fa fa-receipt fa-fw"></i></div>
            <div class="stats-content">
                <div class="stats-title">Total Pembayaran</div>
                <div class="stats-number">{{ number_format($totalPaymentsCount) }}</div>
                <div class="stats-desc">Transaksi Terfilter</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget widget-stats bg-green mb-10px">
            <div class="stats-icon stats-icon-lg"><i class="fa fa-coins fa-fw"></i></div>
            <div class="stats-content">
                <div class="stats-title">Gross Amount</div>
                <div class="stats-number">Rp {{ number_format($totalPaymentsAmount, 0, ',', '.') }}</div>
                <div class="stats-desc">Total Sebelum Potongan</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget widget-stats bg-orange mb-10px">
            <div class="stats-icon stats-icon-lg"><i class="fa fa-cut fa-fw"></i></div>
            <div class="stats-content">
                <div class="stats-title">Total Biaya Layanan</div>
                <div class="stats-number">Rp {{ number_format($totalServiceFees, 0, ',', '.') }}</div>
                <div class="stats-desc">Total Fee Sistem</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget widget-stats bg-purple mb-10px">
            <div class="stats-icon stats-icon-lg"><i class="fa fa-wallet fa-fw"></i></div>
            <div class="stats-content">
                <div class="stats-title">Net Amount</div>
                <div class="stats-number">Rp {{ number_format($netAmount, 0, ',', '.') }}</div>
                <div class="stats-desc">Total Masuk Ke Saldo</div>
            </div>
        </div>
    </div>
</div>

<div class="panel panel-inverse mb-4">
    <div class="panel-heading">
        <h4 class="panel-title"><i class="fa fa-filter me-2"></i>Filter Riwayat</h4>
    </div>
    <div class="panel-body bg-light">
        <form action="{{ url()->current() }}" method="GET">
            <div class="d-flex flex-wrap gap-4">
                <div class="flex-fill" style="min-width:260px;">
                    <label class="form-label fw-bold"><i class="fa fa-calendar-alt me-2 text-primary"></i>Rentang Waktu</label>
                    <div class="input-group">
                        <input type="text" name="daterange" id="filter-daterange" class="form-control border-primary" value="{{ $daterangeValue }}">
                        <span class="input-group-text bg-primary text-white"><i class="fa fa-calendar"></i></span>
                    </div>
                </div>

                <div class="flex-fill" style="min-width:260px;">
                    <label class="form-label fw-bold"><i class="fa fa-search me-2 text-primary"></i>Pencarian</label>
                    <input type="text" name="search" class="form-control border-primary" placeholder="ID Pesanan / Nama Brand..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="d-flex align-items-center mt-4 gap-2">
                <div class="text-muted small me-auto"><i class="fa fa-info-circle"></i> Menampilkan histori pembayaran berdasarkan filter.</div>
                <a href="{{ url()->current() }}" class="btn btn-white w-100px"><i class="fa fa-undo"></i> Reset</a>
                <button type="submit" class="btn btn-primary w-120px"><i class="fa fa-sync"></i> Filter Data</button>
            </div>
        </form>
    </div>
</div>

<div class="panel panel-inverse">
    <div class="panel-heading">
        <h4 class="panel-title"><i class="fa fa-list me-2"></i>Data Pembayaran</h4>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th width="1%">#</th>
                        <th>ID Pesanan</th>
                        <th>Brand & Outlet</th>
                        <th>Gross Amount</th>
                        <th>Fee</th>
                        <th>Net Masuk</th>
                        <th>Waktu Bayar</th>
                        <th width="1%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $index => $payment)
                        <tr>
                            <td>{{ $payments->firstItem() + $index }}</td>
                            <td><span class="fw-bold">{{ $payment->transaction->order_id ?? 'N/A' }}</span></td>
                            <td>
                                <strong>{{ $payment->owner->brand_name ?? '-' }}</strong><br>
                                <small class="text-muted">{{ $payment->outlet->outlet_name ?? '-' }}</small>
                            </td>

                            <td class="fw-bold">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                            <td class="text-danger small">- Rp {{ number_format($payment->service_fee_amount, 0, ',', '.') }}</td>
                            <td class="text-success fw-bold">Rp {{ number_format($payment->amount - $payment->service_fee_amount, 0, ',', '.') }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($payment->payment_time)->format('d/m/Y H:i') }}
                                <small class="text-muted d-block">{{ $payment->timezone }}</small>
                            </td>
                            <td>
                                <a href="#" class="btn btn-xs btn-primary"><i class="fa fa-search"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-5">Tidak ada riwayat pembayaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="small">Menampilkan {{ $payments->firstItem() }} - {{ $payments->lastItem() }} dari {{ $payments->total() }} data</div>
            <div>{{ $payments->appends(request()->query())->links() }}</div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        $(".multiple-select2").select2({ placeholder: " Semua Brand" });
        $('#filter-daterange').daterangepicker({
            autoUpdateInput: false,
            locale: { format: 'YYYY/MM/DD', cancelLabel: 'Clear' }
        });
        $('#filter-daterange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY/MM/DD') + ' - ' + picker.endDate.format('YYYY/MM/DD'));
        });
    });
</script>
@endpush
