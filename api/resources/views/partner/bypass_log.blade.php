@props([
    'items' => ['Partner', 'Monitoring', 'Bypass Logs'],
    'title' => 'Bypass Logs',
    'subtitle' => 'Catatan bypass device dan outlet',
])

@extends('layouts.dashboard.app')
@section('title', $title ?? 'Bypass Logs')

@push('styles')
    {{-- Diperlukan untuk Date Range Picker --}}
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}" />

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
            <!-- Filter Form -->
            <form action="{{ route('partner.bypass.logs') }}" method="GET" class="filter-form mb-4">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label for="search-input" class="form-label">Cari (Outlet, Device, Status, Tipe)</label>
                        <input type="text" name="search" class="form-control form-control-sm" id="search-input"
                            value="{{ request('search') }}" placeholder="Ketik kata kunci...">
                    </div>
                    <div class="col-md-3">
                        <label for="type-select" class="form-label">Tipe Bypass</label>
                        <select name="type" class="form-control form-control-sm" id="type-select">
                            <option value="">Semua Tipe</option>
                            <option value="bypass" {{ request('type') == 'bypass' ? 'selected' : '' }}>Bypass Single
                            </option>
                            <option value="session" {{ request('type') == 'session' ? 'selected' : '' }}>Drop Off Session
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filter-daterange" class="form-label">Rentang Waktu Log</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="daterange" class="form-control" id="filter-daterange"
                                value="{{ request('daterange') }}" placeholder="Pilih Rentang Tanggal">
                            <button class="btn btn-outline-secondary" type="button"
                                onclick="$('#filter-daterange').val('');"><i class="fa fa-times"></i></button>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill"><i
                                class="fa fa-filter me-1"></i> Filter</button>
                        <a href="{{ route('partner.bypass.logs') }}" class="btn btn-secondary btn-sm"><i
                                class="fa fa-redo me-1"></i> Reset</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;" class="text-center">No</th>
                            <th>Info Perangkat</th>
                            <th class="text-center">Tipe</th>
                            <th class="text-center">Status Bypass</th>
                            <th class="text-center">Waktu Aktivasi</th>
                            <th class="text-center">Waktu Dibuat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $index => $log)
                            <tr>
                                <td class="text-center">{{ $logs->firstItem() + $index }}</td>
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
                                        $availableColors = [
                                            'bg-primary',
                                            'bg-success',
                                            'bg-danger',
                                            'bg-warning',
                                            'bg-info',
                                            'bg-secondary',
                                            'bg-dark',
                                        ];
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
                                <td colspan="6" class="text-center py-4 text-muted">
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
    <script type="text/javascript" src="{{ asset('assets/plugins/moment/min/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.js') }}"></script>

    <script>
        $(function() {
            // Inisialisasi Date Range Picker
            $('#filter-daterange').daterangepicker({
                opens: 'left',
                autoUpdateInput: false,
                locale: {
                    format: 'DD/MM/YYYY',
                    cancelLabel: 'Clear',
                    applyLabel: 'Apply',
                    daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                    monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
                        'September', 'Oktober', 'November', 'Desember'
                    ],
                    firstDay: 1
                }
            }, function(start, end, label) {
                $('#filter-daterange').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
            });

            $('#filter-daterange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });

            @if (request('daterange'))
                $('#filter-daterange').val('{{ request('daterange') }}');
            @else
                var drp = $('#filter-daterange').data('daterangepicker');
                if (drp) {
                    drp.setStartDate(moment());
                    drp.setEndDate(moment());
                }
            @endif
        });
    </script>
@endpush