@props([
    'items' => ['Partner', 'Keuangan', 'Riwayat Penarikan'],
    'title' => 'Riwayat Penarikan Dana',
    'subtitle' => 'Lihat histori penarikan dana Anda di sini',
])
@php
    $feature = getData();
@endphp

@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    <div class="row g-4 mb-4">
        {{-- Kartu Besar: Saldo Tersedia untuk Penarikan --}}
        <div class="col-12 col-xl-5 d-flex">
            <div class="card card-lg flex-fill bg-primary border-0 shadow-sm rounded-3">
                <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4">
                    <div class="bg-white text-info rounded-circle d-flex justify-content-center align-items-center mb-3 shadow-sm" style="width: 70px; height: 70px; font-size: 2.5rem;">
                        <i class="fa fa-wallet"></i>
                    </div>
                    <h5 class="card-title text-white opacity-75 mb-1">Saldo Tersedia untuk Penarikan</h5>
                    <p class="card-text h3 text-white fw-bold mb-1">Rp {{ number_format($availableBalance, 0, ',', '.') }}</p>
                    <p class="card-text text-white opacity-75 small-desc">Dana bersih yang siap Anda tarik ke rekening bank Anda.</p>
                </div>
            </div>
        </div>

        {{-- Grid untuk Kartu-kartu Kecil --}}
        <div class="col-12 col-xl-7">
            <div class="row g-3">
                <div class="col-12 col-md-6 d-flex">
                    <div class="card flex-fill border-0 shadow-sm rounded-3">
                        <div class="card-body d-flex align-items-center p-3">
                            <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 50px; height: 50px; font-size: 1.5rem;">
                                <i class="fa fa-clipboard-list"></i>
                            </div>
                            <div>
                                <h5 class="card-title text-muted mb-1 fs-6">Total Permintaan Penarikan</h5>
                                <p class="card-text h4 fw-bold mb-0 text-dark">{{ $totalWithdrawalsCount }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 d-flex">
                    <div class="card flex-fill border-0 shadow-sm rounded-3">
                        <div class="card-body d-flex align-items-center p-3">
                            <div class="bg-success text-white rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 50px; height: 50px; font-size: 1.5rem;">
                                <i class="fa fa-check-circle"></i>
                            </div>
                            <div>
                                <h5 class="card-title text-muted mb-1 fs-6">Jumlah Disetujui</h5>
                                <p class="card-text h4 fw-bold mb-0 text-success">Rp {{ number_format($approvedWithdrawalsAmount, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 d-flex">
                    <div class="card flex-fill border-0 shadow-sm rounded-3">
                        <div class="card-body d-flex align-items-center p-3">
                            <div class="bg-danger text-white rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 50px; height: 50px; font-size: 1.5rem;">
                                <i class="fa fa-times-circle"></i>
                            </div>
                            <div>
                                <h5 class="card-title text-muted mb-1 fs-6">Jumlah Ditolak</h5>
                                <p class="card-text h4 fw-bold mb-0 text-danger">Rp {{ number_format($rejectedWithdrawalsAmount, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 d-flex">
                    <div class="card flex-fill border-0 shadow-sm rounded-3">
                        <div class="card-body d-flex align-items-center p-3">
                            <div class="bg-warning text-dark rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 50px; height: 50px; font-size: 1.5rem;">
                                <i class="fa fa-hourglass-half"></i>
                            </div>
                            <div>
                                <h5 class="card-title text-muted mb-1 fs-6">Permintaan Pending</h5>
                                <p class="card-text h4 fw-bold mb-0 text-warning">{{ $pendingWithdrawalsCount }}</p>
                            </div>
                        </div>
                    </div>
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
            <div class="row align-items-center mb-3">
                <div class="col-md-6">
                    <a href="{{ route('partner.withdrawal.request') }}"
                        class="btn btn-success @disabled(!$feature->can('withdrawal.request'))">
                        <i class="fa fa-hand-holding-usd me-2"></i> AJUKAN PENARIKAN BARU
                    </a>
                </div>
                <div class="col-md-6 text-end">
                    @if ($pendingWithdrawalsCount > 0)
                        <h5 class="mb-0 text-warning">
                            <i class="fa fa-exclamation-circle me-1"></i> **{{ $pendingWithdrawalsCount }} Permintaan Pending**
                            <span class="d-block text-muted small">(Total Rp
                                {{ number_format($totalPendingWithdrawalsAmount, 0, ',', '.') }} sedang diproses)</span>
                        </h5>
                    @endif
                </div>
            </div>

            <form method="GET" action="{{ route('partner.withdrawal.histories') }}" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="filter-status" class="form-label">Filter Status</label>
                        <select name="status" id="filter-status" class="form-select form-select-sm">
                            <option value="">-- Semua Status --</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="filter-daterange" class="form-label">Rentang Waktu</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                            <input type="text" name="daterange" class="form-control" id="filter-daterange"
                                value="{{ request('daterange') }}" placeholder="Pilih Rentang Waktu">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary btn-sm me-2">
                            <i class="fa fa-filter me-1"></i> Terapkan Filter
                        </button>
                        <a href="{{ route('partner.withdrawal.histories') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-redo me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="1%">#</th>
                            <th>Jumlah Penarikan</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th>Tanggal Diajukan</th>
                            <th>Tanggal Disetujui</th>
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
                                <td>{{ $loop->iteration }}</td>
                                <td>Rp {{ number_format($withdrawal->requested_amount, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $statusBadgeClass }}">
                                        {{ ucfirst($withdrawal->status) }}
                                    </span>
                                </td>
                                <td>{{ $withdrawal->notes ?? '-' }}</td>
                                <td>
                                    {{ $withdrawal->created_at->setTimezone($withdrawal->timezone)->format('d-m-Y H:i') }}
                                    <small class="text-muted">{{ strtoupper($withdrawal->timezone ?? 'WIB') }}</small>
                                </td>
                                <td>
                                    @if ($withdrawal->approved_at)
                                        {{ \Carbon\Carbon::parse($withdrawal->approved_at)->setTimezone($withdrawal->timezone)->format('d-m-Y H:i') }}
                                        <small class="text-muted">{{ strtoupper($withdrawal->timezone ?? 'WIB') }}</small>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#withdrawalDetailModal{{ $withdrawal->id }}">
                                        <i class="fa fa-info-circle"></i> Detail
                                    </button>
                                </td>
                            </tr>

                            {{-- Modal Detail Penarikan (Inline for each withdrawal) --}}
                            <div class="modal fade" id="withdrawalDetailModal{{ $withdrawal->id }}" tabindex="-1"
                                role="dialog" aria-labelledby="withdrawalDetailModalLabel{{ $withdrawal->id }}"
                                aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="withdrawalDetailModalLabel{{ $withdrawal->id }}">
                                                Detail Penarikan #{{ $withdrawal->id }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6><i class="fa fa-info-circle"></i> Informasi Penarikan</h6>
                                                    <hr class="mt-1 mb-2">
                                                    <dl class="row">
                                                        <dt class="col-sm-5">Jumlah Diminta:</dt>
                                                        <dd class="col-sm-7">Rp {{ number_format($withdrawal->requested_amount, 0, ',', '.') }}</dd>

                                                        <dt class="col-sm-5">Biaya Penarikan:</dt>
                                                        <dd class="col-sm-7">Rp {{ number_format($withdrawal->withdrawal_fee, 0, ',', '.') }}</dd>

                                                        <dt class="col-sm-5">Total Dipotong Saldo:</dt>
                                                        <dd class="col-sm-7">Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</dd>

                                                        <dt class="col-sm-5">Jumlah Ditransfer (Bersih):</dt>
                                                        <dd class="col-sm-7">Rp {{ number_format($withdrawal->net_amount_transferred, 0, ',', '.') }}</dd>

                                                        <dt class="col-sm-5">Status:</dt>
                                                        <dd class="col-sm-7">
                                                            <span class="badge {{ $statusBadgeClass }}">
                                                                {{ ucfirst($withdrawal->status) }}
                                                            </span>
                                                        </dd>

                                                        <dt class="col-sm-5">Catatan:</dt>
                                                        <dd class="col-sm-7">{{ $withdrawal->notes ?? '-' }}</dd>

                                                        <dt class="col-sm-5">Diajukan Pada:</dt>
                                                        <dd class="col-sm-7">
                                                            {{ $withdrawal->created_at->setTimezone($withdrawal->timezone)->format('d-m-Y H:i') }}
                                                            ({{ strtoupper($withdrawal->timezone ?? 'WIB') }})
                                                        </dd>

                                                        <dt class="col-sm-5">Disetujui Pada:</dt>
                                                        <dd class="col-sm-7">
                                                            @if ($withdrawal->approved_at)
                                                                {{ \Carbon\Carbon::parse($withdrawal->approved_at)->setTimezone($withdrawal->timezone)->format('d-m-Y H:i') }}
                                                                ({{ strtoupper($withdrawal->timezone ?? 'WIB') }})
                                                            @else
                                                                -
                                                            @endif
                                                        </dd>
                                                    </dl>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6><i class="fa fa-bank"></i> Detail Rekening Bank</h6>
                                                    <hr class="mt-1 mb-2">
                                                    <dl class="row">
                                                        <dt class="col-sm-5">Nama Bank:</dt>
                                                        <dd class="col-sm-7">{{ $withdrawal->bank_name ?? '-' }}</dd>

                                                        <dt class="col-sm-5">Nomor Rekening:</dt>
                                                        <dd class="col-sm-7">{{ $withdrawal->bank_account_number ?? '-' }}</dd>

                                                        <dt class="col-sm-5">Nama Pemilik Rekening:</dt>
                                                        <dd class="col-sm-7">{{ $withdrawal->bank_account_holder_name ?? '-' }}</dd>
                                                    </dl>

                                                    <h6 class="mt-4"><i class="fa fa-balance-scale"></i> Saldo Terkait</h6>
                                                    <hr class="mt-1 mb-2">
                                                    <dl class="row">
                                                        <dt class="col-sm-5">Saldo Sebelum Penarikan:</dt>
                                                        <dd class="col-sm-7">Rp {{ number_format($withdrawal->amount_before_fee, 0, ',', '.') }}</dd>

                                                        <dt class="col-sm-5">Saldo Setelah Penarikan:</dt>
                                                        <dd class="col-sm-7">Rp {{ number_format($withdrawal->amount_after_fee, 0, ',', '.') }}</dd>
                                                    </dl>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Belum ada riwayat penarikan dana yang
                                    sesuai dengan filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="1">Total yang Ditampilkan:</th>
                            <th>Rp {{ number_format($totalWithdrawalsAmountInTable, 0, ',', '.') }}</th>
                            <th colspan="2"></th>
                            <th>Total Permintaan (Filter):</th>
                            <th colspan="2">{{ $withdrawalHistories->total() }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>Menampilkan {{ $withdrawalHistories->firstItem() }} hingga {{ $withdrawalHistories->lastItem() }}
                    dari {{ $withdrawalHistories->total() }} riwayat penarikan</div>
                <div>{{ $withdrawalHistories->appends(request()->query())->links() }}</div>
            </div>
        </div>
    </div>
@endsection

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
                $(this).closest('form').submit();
            });

            $('#filter-daterange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $(this).closest('form').submit();
            });
        });
    </script>
@endpush
