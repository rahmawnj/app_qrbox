@props([
    'items' => ['Partner', 'Transaksi', 'Daftar Transaksi'],
    'title' => 'Transaksi',
    'subtitle' => 'Lihat dan kelola seluruh transaksi yang tercatat',
])

@push('styles')
    <link href="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet" />
    <style>
        /* Custom styles for better table and filter appearance */
        .panel-body .form-control-sm {
            height: calc(1.5em + .5rem + 2px);
            /* Adjust height for sm inputs */
            padding: .25rem .5rem;
            font-size: .875rem;
            line-height: 1.5;
            border-radius: .2rem;
        }

        .filter-group {
            display: flex;
            gap: 10px;
            /* Space between filter elements */
            flex-wrap: wrap;
            /* Allow wrapping on smaller screens */
        }

        .filter-group .input-group {
            flex: 1;
            /* Allow input groups to grow */
            min-width: 180px;
            /* Minimum width for input groups before wrapping */
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
            /* Prevent headers from wrapping too much */
        }

        .table tbody td {
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: #f5f5f5;
            /* Subtle hover effect */
        }

        .badge-status {
            padding: .4em .6em;
            border-radius: .25rem;
            font-size: 85%;
            font-weight: 600;
            display: inline-block;
            /* Ensure badge respects padding */
        }

        .badge-pending {
            background-color: #ffc107;
            color: #343a40;
        }

        /* Yellow for pending */
        .badge-success {
            background-color: #28a745;
            color: #fff;
        }

        /* Green for success */
        .badge-failed {
            background-color: #dc3545;
            color: #fff;
        }

        /* Red for failed */
        /* Updated badge styles for new types */
        .badge-drop-off {
            background-color: #6c757d;
            /* Menggunakan warna abu-abu seperti manual sebelumnya */
            color: #fff;
        }

        .badge-self-service {
            background-color: #0d6efd;
            /* Warna biru baru yang berbeda dari QRIS sebelumnya */
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
            /* Remove default bottom margin from Laravel pagination */
        }

        /* NEW STYLES FOR MODERN CARDS */
        .summary-card {
            background-color: #ffffff;
            /* White background */
            border: 1px solid #e0e0e0;
            /* Light border */
            border-radius: .5rem;
            /* Slightly larger border-radius */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            /* Modern shadow */
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            /* Space between icon and text */
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
            /* Prevent icon from shrinking */
        }

        /* Specific icon circle colors */
        .icon-circle.bg-primary {
            background-color: #007bff !important;
        }

        .icon-circle.bg-success {
            background-color: #28a745 !important;
        }

        .icon-circle.bg-info {
            background-color: #17a2b8 !important;
        }

        .icon-circle.bg-warning {
            background-color: #ffc107 !important;
        }

        .icon-circle.bg-danger {
            background-color: #dc3545 !important;
        }

        .summary-card .card-title {
            font-size: 1rem;
            /* Smaller title */
            color: #6c757d;
            /* Muted color for title */
            margin-bottom: 0.25rem;
        }

        .summary-card .card-text.h3 {
            font-size: 1.75rem;
            /* Slightly smaller h3 for balance */
            margin-bottom: 0.5rem;
            color: #343a40;
            /* Darker color for value */
        }

        .summary-card .card-text.small-desc {
            font-size: 0.875rem;
            /* Smaller description */
            color: #999;
            /* Lighter color for description */
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

            // Set initial value for daterange input if a value exists
            var initialDateRange = "{{ request('daterange') }}";
            if (initialDateRange) {
                $('#filter-daterange').val(initialDateRange);
            } else {
                // Optionally set a default range, e.g., "Hari Ini" if no range is selected
                // var today = moment().format('YYYY-MM-DD');
                // $('#filter-daterange').val(today + ' - ' + today);
            }


            // Event listener for when the 'Apply' button is clicked in the daterangepicker
            $('#filter-daterange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format(
                    'YYYY-MM-DD'));
                // You might want to submit the form here automatically if a range is applied
                // $(this).closest('form').submit();
            });

            // Event listener for when the 'Clear' button is clicked in the daterangepicker
            $('#filter-daterange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                // If you want to clear the filter and re-submit the form
                // $(this).closest('form').submit();
            });

            // Handle export button click
            $('#export-button').on('click', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                var exportUrl =
                    "{{ route('export.partner-transactions') }}"; // Perbarui ini jika rute berbeda
                var queryString = form.serialize(); // Get all form data as query string
                window.location.href = exportUrl + '?' + queryString;
            });
        });
    </script>
@endpush

@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />




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
            <form method="GET" action="{{ route('partner.transactions.index') }}" class="mb-4">
                <div class="row g-3"> {{-- Use row and g-3 for consistent spacing --}}
                    <div class="col-md-3 col-sm-6"> {{-- Ubah col-md-4 menjadi col-md-3 --}}
                        <label for="filter-status" class="form-label visually-hidden">Status</label>
                        <select name="status" id="filter-status" class="form-control form-control-sm">
                            <option value="">-- Semua Status --</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Sukses</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Gagal</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6"> {{-- Ubah col-md-4 menjadi col-md-3 --}}
                        <label for="filter-type" class="form-label visually-hidden">Tipe Transaksi</label>
                        <select name="type" id="filter-type" class="form-control form-control-sm">
                            <option value="">-- Semua Tipe Transaksi --</option>
                            <option value="drop_off" {{ request('type') == 'drop_off' ? 'selected' : '' }}>Drop-off
                            </option>
                            <option value="self_service" {{ request('type') == 'self_service' ? 'selected' : '' }}>
                                Self-service</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6"> {{-- Filter baru untuk "Is Member" atau "Bukan Member" --}}
                        <label for="filter-is-member" class="form-label visually-hidden">Tipe Pelanggan</label>
                        <select name="is_member" id="filter-is-member" class="form-control form-control-sm">
                            <option value="">-- Semua Pelanggan --</option>
                            <option value="yes" {{ request('is_member') == 'yes' ? 'selected' : '' }}>Member</option>
                            <option value="no" {{ request('is_member') == 'no' ? 'selected' : '' }}>Bukan Member
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6"> {{-- Ubah col-md-4 menjadi col-md-3 --}}
                        <label for="filter-daterange" class="form-label visually-hidden">Rentang Waktu</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                            <input type="text" name="daterange" class="form-control" id="filter-daterange"
                                value="{{ request('daterange') }}" placeholder="Pilih Rentang Waktu">
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6"> {{-- Ubah col-md-2 menjadi col-md-3 --}}
                        <label for="filter-search" class="form-label visually-hidden">Pencarian</label>
                        <input type="text" name="search" id="filter-search" class="form-control form-control-sm"
                            placeholder="Nama Outlet, ORDER ID, Jumlah" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="filter-actions">
                    <div class="filter-left">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa fa-filter me-1"></i> Terapkan Filter
                        </button>
                        <a href="{{ route('partner.transactions.index') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-redo me-1"></i> Reset
                        </a>
                    </div>

                    <div class="filter-right">
                        <button type="button" id="export-button" class="btn btn-success btn-sm">
                            <i class="fa fa-file-excel me-1"></i> Export ke Excel
                        </button>
                    </div>
                </div>

            </form>

              <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="1%">#</th>
                            <th>Order ID</th>
                            <th>Owner</th>
                            <th>Outlet</th>
                            <th class="text-end">Jumlah</th>
                            <th>Customer</th> {{-- Kolom Customer --}}
                            <th>Tipe Channel</th> {{-- Ubah header kolom --}}
                            <th>Status</th>
                            <th>Waktu</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            @php

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

                                // Logika baru untuk badge tipe transaksi dan display name berdasarkan channel_type
                                $typeDisplayName = '';
                                $typeBadgeClass = '';
                                switch ($transaction->channel_type) {
                                    // Menggunakan channel_type
                                    case 'drop_off':
                                        $typeDisplayName = 'Drop-off';
                                        $typeBadgeClass = 'badge-drop-off';
                                        break;
                                    case 'self_service':
                                        // Untuk self-service, cek apakah ada member_id untuk membedakan QRIS atau Member
                                        if ($transaction->member_id) {
                                            $typeDisplayName = 'Self-service (Member)';
                                        } else {
                                            $typeDisplayName = 'Self-service (QRIS)'; // Asumsi jika tidak ada member_id, itu QRIS
                                        }
                                        $typeBadgeClass = 'badge-self-service';
                                        break;
                                    default:
                                        $typeDisplayName = ucfirst($transaction->channel_type); // Fallback
                                        $typeBadgeClass = 'bg-info'; // Fallback badge
                                        break;
                                }
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $transaction->order_id }}</td>
                                <td>
                                    <strong>{{ $transaction->owner->brand_name ?? '-' }}</strong><br>
                                    <small class="text-muted">{{ $transaction->owner->user->name ?? '-' }}</small>
                                </td>
                                <td>
                                    <strong>{{ $transaction->outlet->outlet_name ?? '-' }}</strong><br>
                                    <small class="text-muted">
                                        @if ($transaction->channel_type === 'self_service' && $transaction->selfServiceTransaction)
                                            <i class="fa fa-cash-register me-1"></i> Device:
                                            {{ $transaction->selfServiceTransaction->device_code ?? '-' }}
                                        @else
                                            -
                                        @endif
                                    </small>
                                </td>
                                <td class="text-end">Rp{{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                <td>
                                    @if ($transaction->channel_type === 'drop_off' && $transaction->dropOffTransaction)
                                        <strong>{{ $transaction->dropOffTransaction->customer_name ?? '-' }}</strong><br>
                                        <small
                                            class="text-muted">{{ $transaction->dropOffTransaction->customer_phone_number ?? '-' }}</small>
                                    @elseif ($transaction->channel_type === 'self_service' && $transaction->member && $transaction->member->user)
                                        <strong>{{ $transaction->member->user->name ?? '-' }}</strong><br>
                                        <small class="text-muted">{{ $transaction->member->phone_number ?? '-' }}</small>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-status {{ $typeBadgeClass }}">
                                        {{ $typeDisplayName }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-status {{ $statusBadgeClass }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($transaction->created_at)->setTimezone($transaction->timezone)->format('d-m-Y H:i:s') }}
                                    <br><small class="text-muted">{{ strtoupper($transaction->timezone) }}</small>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                        data-bs-target="#transactionDetailModal{{ $transaction->id }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>

                            <x-transaction-detail-modal :transaction="$transaction" />

                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">Tidak ada transaksi ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="1">Total:</th>
                            <th colspan="2">{{ $totalTransactionsCount }} Transaksi</th>
                            <th colspan="7"></th> {{-- Menyesuaikan colspan karena ada kolom Customer baru --}}
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
    {{-- Tambahkan style jika ada untuk tampilan form owner, atau gunakan Bootstrap default --}}
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

        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('imagePreview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
@endpush
