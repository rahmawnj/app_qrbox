@props([
    'items' => ['Partner', 'Monitoring', 'Bypass Logs'],
    'title' => 'Bypass Logs',
    'subtitle' => 'Catatan bypass device dan outlet',
])

@extends('layouts.dashboard.app')

@push('styles')
    {{-- Diperlukan untuk Date Range Picker --}}
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    {{-- Custom CSS untuk tampilan yang lebih rapi --}}
    <style>
        .table-responsive {
            overflow-x: auto;
        }

        .table th,
        .table td {
            vertical-align: middle;
            padding: 0.75rem;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            /* Light gray on hover */
        }

        .status-badge {
            font-size: 0.85em;
            padding: 0.4em 0.8em;
            border-radius: 0.25rem;
            display: inline-block;
            /* Agar bisa diatur paddingnya */
        }

        /* Warna untuk status bypass */
        .status-success {
            background-color: #28a745;
            color: #fff;
        }

        /* Misalnya, bypass successful */
        .status-warning {
            background-color: #ffc107;
            color: #212529;
        }

        /* Misalnya, bypass temporary */
        .status-danger {
            background-color: #dc3545;
            color: #fff;
        }

        /* Misalnya, bypass failed atau disabled */
        .status-info {
            background-color: #17a2b8;
            color: #fff;
        }

        /* Status lain */

        .filter-form .form-control-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.2rem;
        }

        .filter-form label {
            font-size: 0.9em;
            margin-bottom: 0.25rem;
        }

        .filter-form .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
        }

        .pagination-info {
            font-size: 0.9em;
            color: #6c757d;
        }
    </style>
@endpush

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />
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
                                    value="{{ $daterangeValue }}"
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
                        <div class="flex-fill" style="min-width:260px;">
                            <div class="d-flex gap-3">
                                <div class="flex-fill">
                                    <label class="form-label fw-bold">
                                        <i class="fa fa-check-circle me-2 text-primary"></i>
                                        Status
                                    </label>
                                    <select name="type" id="type" class="form-select form-select-sm">
                                        <option value="">-- Semua Tipe --</option>
                                        <option value="bypass" {{ request('type') == 'bypass' ? 'selected' : '' }}>Bypass</option>
                                        <option value="session" {{ request('type') == 'session' ? 'selected' : '' }}>Drop Off</option>
                                    </select>
                                </div>
                            </div>
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
            <h4 class="panel-title">{{ $title ?? '' }}</h4>
            <div class="panel-heading-btn">
                <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand" title="Perbesar">
                    <i class="fa fa-expand"></i>
                </a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload"
                    title="Refresh Data">
                    <i class="fa fa-redo"></i>
                </a>
            </div>
        </div>
        <div class="panel-body">

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="text-center" width="1%">#</th>
                            <th>Detail Outlet</th>
                            <th>Detail Perangkat</th>
                            <th class="text-center">Tipe Bypass</th> {{-- Menambahkan kolom Type --}}
                            <th class="text-center">Status Bypass</th>
                            <th class="text-center">Waktu Aktivasi Bypass</th> {{-- Ganti Tanggal Log menjadi Waktu Aktivasi Bypass --}}
                            <th class="text-center">Waktu Dibuat (Log)</th> {{-- Menambahkan Tanggal Log --}}
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="text-center">
                                    {{ $loop->iteration + ($logs->currentPage() - 1) * $logs->perPage() }}</td>
                                <td>
                                    <strong>{{ $log->outlet_name ?? ($log->outlet_code ?? 'N/A') }}</strong><br>
                                    <small class="text-muted"><i class="fa fa-map-marker-alt me-1"></i>
                                        {{ $log->outlet_address ?? 'Alamat tidak tersedia' }}</small>
                                </td>
                                <td>
                                    <strong>{{ $log->device_name ?? 'N/A' }}</strong><br>
                                    <small class="text-muted"><i class="fa fa-barcode me-1"></i>
                                        {{ $log->device_code ?? 'N/A' }}</small>
                                </td>
                                <td class="text-center">
                                    @php
                                        $type = $log->type ?? 'N/A';
                                        $displayText = $type === 'session' ? 'Drop Off' : ucfirst($type);
                                        $badgeClass = match ($type) {
                                            'session' => 'bg-primary',
                                            'bypass' => 'bg-danger',
                                            default => 'bg-secondary',
                                        };
                                    @endphp

                                    <span class="badge {{ $badgeClass }} status-badge">
                                        {{ $displayText }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    @php
                                        $status = strtolower($log->bypass_status ?? 'unknown');

                                        // Warna yang tersedia (bisa tambah sesuai selera)
                                        $availableColors = [
                                            'bg-primary',
                                            'bg-success',
                                            'bg-danger',
                                            'bg-warning',
                                            'bg-info',
                                            'bg-secondary',
                                            'bg-dark',
                                        ];

                                        // Buat warna tetap berdasarkan hash status
                                        $hash = crc32($status);
                                        $index = $hash % count($availableColors);
                                        $badgeClass = $availableColors[$index];
                                    @endphp

                                    <span class="badge {{ $badgeClass }} status-badge">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    @if ($log->bypass_activation)
                                        {{ \Carbon\Carbon::parse($log->bypass_activation)->format('d M Y, H:i:s') }}
                                        <br>
                                        <small
                                            class="text-muted">{{ \Carbon\Carbon::parse($log->bypass_activation)->diffForHumans() }}</small>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($log->created_at)
                                        {{ \Carbon\Carbon::parse($log->created_at)->format('d M Y, H:i:s') }}
                                        <br>
                                        <small
                                            class="text-muted">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</small>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted"> {{-- Update colspan to 7 --}}
                                    <i class="fa fa-exclamation-circle me-1"></i> Tidak ada data log bypass yang ditemukan
                                    untuk kriteria ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="pagination-info">
                    Menampilkan {{ $logs->firstItem() }} hingga {{ $logs->lastItem() }} dari
                    {{ $logs->total() }} total log.
                </div>
                <div>{{ $logs->appends(request()->query())->links() }}</div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- JQuery diperlukan untuk Date Range Picker --}}
    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    {{-- Moment.js diperlukan untuk Date Range Picker --}}
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    {{-- Date Range Picker JS --}}
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        $(function() {
            // Inisialisasi Date Range Picker
            $('#filter-daterange').daterangepicker({
                opens: 'left', // Posisi calendar
                autoUpdateInput: false, // Jangan update input otomatis
                locale: {
                    format: 'DD/MM/YYYY', // Format tampilan di input
                    cancelLabel: 'Clear',
                    applyLabel: 'Apply',
                    daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                    monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
                        'September', 'Oktober', 'November', 'Desember'
                    ],
                    firstDay: 1
                }
            }, function(start, end, label) {
                // Ketika tanggal dipilih, update nilai input
                $('#filter-daterange').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
            });

            // Handle tombol "Clear" pada Date Range Picker
            $('#filter-daterange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });

            // Set nilai input kembali jika ada di request sebelumnya
            if ($('#filter-daterange').val() == '') {
                $('#filter-daterange').data('daterangepicker').setStartDate(moment());
                $('#filter-daterange').data('daterangepicker').setEndDate(moment());
            }

            // Optional: Jika ingin menggunakan DataTables (sudah dikomentari sebelumnya)
            // $('#data-table').DataTable({
            //     "paging": false,
            //     "searching": false,
            //     "info": false,
            //     "responsive": true,
            //     "autoWidth": false,
            //     "order": [[ 5, "desc" ]] // Mengubah index order menjadi 5 (created_at)
            // });
        });
    </script>
@endpush
