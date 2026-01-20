@extends('layouts.dashboard.app')

@push('styles')
    <link href="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/select2/dist/css/select2.min.css') }}" rel="stylesheet" />
    <style>
        /* Memperbaiki tampilan select2 agar serasi dengan border-primary */
        .select2-container--default .select2-selection--multiple {
            border-color: #348fe2 !important;
        }
    </style>
@endpush

@section('content')
    <ol class="breadcrumb float-xl-end">
        <li class="breadcrumb-item"><a href="javascript:;">Home</a></li>
        <li class="breadcrumb-item active">Dashboard Analytics</li>
    </ol>
    <h1 class="page-header">Dashboard <small>statistik & analisis brand</small></h1>

  <div class="panel panel-inverse mb-4">
    <div class="panel-heading">
        <h4 class="panel-title">
            <i class="fa fa-filter me-2"></i>Filter Data
        </h4>
    </div>

    <div class="panel-body bg-light">
        <form action="" method="GET" id="filterForm">

            <div class="d-flex flex-wrap gap-4">

                <!-- DATE RANGE -->
                <div class="flex-fill" style="min-width:280px;">
                    <label class="form-label fw-bold">
                        <i class="fa fa-calendar-alt me-2 text-primary"></i>
                        Rentang Waktu Analisis
                    </label>

                    <div class="input-group">
                        <input
                            type="text"
                            name="daterange"
                            id="daterange"
                            class="form-control border-primary"
                            value="{{ $daterange }}"
                        >
                        <span class="input-group-text bg-primary text-white">
                            <i class="fa fa-search"></i>
                        </span>
                    </div>
                </div>

                <!-- OWNER SELECT -->
                <div class="flex-fill" style="min-width:280px;">
                    <label class="form-label fw-bold">
                        <i class="fa fa-briefcase me-2 text-primary"></i>
                        Pilih Owner / Brand
                    </label>

                    @if(Auth::guard('admin_config')->check())
                        <select
                            name="owner_ids[]"
                            class="form-control multiple-select2 border-primary"
                            multiple
                        >
                            @foreach(App\Models\Owner::all() as $owner)
                                <option value="{{ $owner->id }}"
                                    {{ is_array(request('owner_ids')) && in_array($owner->id, request('owner_ids')) ? 'selected' : '' }}>
                                    {{ $owner->brand_name }} ({{ $owner->code }})
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input type="hidden" name="owner_ids[]" value="{{ auth()->user()->owner->id }}">
                        <select class="form-control border-primary" disabled>
                            <option selected>
                                {{ auth()->user()->owner->brand_name }}
                            </option>
                        </select>
                        <small class="text-muted">
                            Akses terbatas pada brand Anda.
                        </small>
                    @endif
                </div>

            </div>

            <hr class="my-4">

            <!-- ACTIONS -->
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="text-muted small me-auto">
                    <i class="fa fa-info-circle"></i>
                    Gunakan Ctrl+Klik atau ketik untuk memilih banyak brand
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


    <div class="row mb-2">
        <div class="col-xl-3 col-md-6">
            <div class="widget widget-stats bg-blue shadow-sm">
                <div class="stats-icon"><i class="fa fa-store"></i></div>
                <div class="stats-info">
                    <h4>TOTAL OUTLET</h4>
                    <p>{{ number_format($totalOutlets) }}</p>
                </div>
                <div class="stats-link">
                    <a href="javascript:;">View Detail <i class="fa fa-arrow-alt-circle-right"></i></a>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="widget widget-stats bg-info shadow-sm">
                <div class="stats-icon"><i class="fa fa-microchip"></i></div>
                <div class="stats-info">
                    <h4>TOTAL DEVICE</h4>
                    <p>{{ number_format($totalDevices) }}</p>
                </div>
                <div class="stats-link">
                    <a href="javascript:;">View Detail <i class="fa fa-arrow-alt-circle-right"></i></a>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="widget widget-stats bg-teal shadow-sm">
                <div class="stats-icon"><i class="fa fa-wallet"></i></div>
                <div class="stats-info">
                    <h4>BALANCE OWNER</h4>
                    <p>Rp {{ number_format($totalOwnerBalance, 0, ',', '.') }}</p>
                </div>
                <div class="stats-link">
                    <a href="javascript:;">Lihat Riwayat <i class="fa fa-arrow-alt-circle-right"></i></a>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="widget widget-stats bg-purple shadow-sm">
                <div class="stats-icon"><i class="fa fa-vault"></i></div>
                <div class="stats-info">
                    <h4>TOTAL DEPOSIT</h4>
                    <p>Rp {{ number_format($totalDepositAmount ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="stats-link">
                    <a href="javascript:;">Audit Jaminan <i class="fa fa-arrow-alt-circle-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6 mb-4">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title"><i class="fa fa-chart-bar me-2"></i>Tren Volume Transaksi (Harian)</h4>
                </div>
                <div class="panel-body">
                    <div id="chart-bar-transaksi" class="height-sm"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 mb-4">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title"><i class="fa fa-chart-line me-2"></i>Tren Pendapatan Bruto</h4>
                </div>
                <div class="panel-body">
                    <div id="chart-line-money" class="height-sm"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 mb-4">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title"><i class="fa fa-clock me-2"></i>Traffic per 24 Jam</h4>
                </div>
                <div class="panel-body p-0">
                    <div id="chart-donut-hour" class="height-sm p-3"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 mb-4">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title"><i class="fa fa-server me-2"></i>Top 10 Perangkat Paling Aktif</h4>
                </div>
                <div class="panel-body p-0">
                    <div id="chart-donut-device" class="height-sm p-3"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="{{ asset('assets/plugins/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/dist/js/select2.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Init Select2 Multi-select
            $(".multiple-select2").select2({
                placeholder: " Pilih satu atau lebih Brand",
                allowClear: true
            });

            // Datepicker Init
            $('#daterange').daterangepicker({
                opens: 'right',
                format: 'YYYY/MM/DD',
                separator: ' to ',
                locale: {
                    applyLabel: 'Pilih',
                    cancelLabel: 'Batal',
                    fromLabel: 'Dari',
                    toLabel: 'Ke',
                    format: 'YYYY/MM/DD'
                }
            });
        });

        // --- APEX CHARTS LOGIC ---
        var chartOptions = {
            chart: { fontFamily: 'Open Sans, sans-serif', toolbar: { show: false } },
            dataLabels: { enabled: false }
        };

        // 1. BAR CHART
        new ApexCharts(document.querySelector("#chart-bar-transaksi"), {
            ...chartOptions,
            chart: { type: 'bar', height: 300 },
            series: [{ name: 'Qty Transaksi', data: {!! json_encode($dailyCount->pluck('total')) !!} }],
            xaxis: { categories: {!! json_encode($dates) !!} },
            colors: ['#348fe2'],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } }
        }).render();

        // 2. LINE CHART
        new ApexCharts(document.querySelector("#chart-line-money"), {
            ...chartOptions,
            chart: { type: 'line', height: 300 },
            stroke: { curve: 'smooth', width: 3 },
            series: {!! json_encode($multiLineSeries) !!},
            xaxis: { categories: {!! json_encode($dates) !!} },
            yaxis: { labels: { formatter: (val) => "Rp " + val.toLocaleString('id-ID') } },
            markers: { size: 4 },
            colors: ['#00ACAC', '#348fe2', '#f59c1a']
        }).render();

        // 3. DONUT HOUR
        new ApexCharts(document.querySelector("#chart-donut-hour"), {
            ...chartOptions,
            chart: { type: 'donut', height: 350 },
            labels: {!! json_encode($hourlyLabels) !!},
            series: {!! json_encode($hourlyValues) !!},
            legend: { position: 'bottom' },
            colors: ['#348fe2', '#00ACAC', '#727cb6', '#f59c1a', '#ff5b57']
        }).render();

        // 4. DONUT DEVICE
        new ApexCharts(document.querySelector("#chart-donut-device"), {
            ...chartOptions,
            chart: { type: 'donut', height: 350 },
            labels: {!! json_encode($deviceTrend->pluck('device_code')) !!},
            series: {!! json_encode($deviceTrend->pluck('total')) !!},
            legend: { position: 'bottom' }
        }).render();
    </script>
@endpush
