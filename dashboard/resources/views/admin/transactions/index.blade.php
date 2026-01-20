@props([
    'items' => ['admin', 'Transaksi', 'Daftar Transaksi'],
    'title' => 'Transaksi',
    'subtitle' => 'Lihat dan kelola seluruh transaksi yang tercatat',
])

@push('styles')
    <link href="{{ asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/select2/dist/css/select2.min.css') }}" rel="stylesheet" />
    <style>
        /* Memperbaiki tampilan select2 agar serasi dengan border-primary */
        .select2-container--default .select2-selection--multiple {
            border-color: #348fe2 !important;
        }
    </style>

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
    <script src="{{ asset('assets/plugins/select2/dist/js/select2.min.js') }}"></script>

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
 $(".multiple-select2").select2({
                placeholder: " Pilih satu atau lebih Brand",
                allowClear: true
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
                <div class="stats-icon stats-icon-lg"><i class="fa fa-shopping-cart fa-fw"></i></div>
                <div class="stats-content">
                    <div class="stats-title">Transaksi Berhasil</div>
                    <div class="stats-number">{{ number_format($totalTransactionsCount) }}</div>
                    <div class="stats-desc">Total periode terpilih</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="widget widget-stats bg-green mb-10px">
                <div class="stats-icon stats-icon-lg"><i class="fa fa-wallet fa-fw"></i></div>
                <div class="stats-content">
                    <div class="stats-title">Total Pemasukan</div>
                    <div class="stats-number">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
                    <div class="stats-desc">Hanya status success</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="widget widget-stats bg-red mb-10px">
                <div class="stats-icon stats-icon-lg"><i class="fa fa-arrow-up fa-fw"></i></div>
                <div class="stats-content">
                    <div class="stats-title">Total Penarikan</div>
                    <div class="stats-number">Rp {{ number_format($totalWithdrawal, 0, ',', '.') }}</div>
                    <div class="stats-desc">Hanya status success</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="widget widget-stats bg-purple mb-10px">
                <div class="stats-icon stats-icon-lg"><i class="fa fa-university fa-fw"></i></div>
                <div class="stats-content">
                    <div class="stats-title">Saldo Bersih</div>
                    <div class="stats-number">Rp {{ number_format($netBalance, 0, ',', '.') }}</div>
                    <div class="stats-desc">Pemasukan - Penarikan</div>
                </div>
            </div>
        </div>
    </div>

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
                                        <i class="fa fa-exchange-alt me-2 text-primary"></i>
                                        Tipe
                                    </label>
                                    <select name="type" class="form-select border-primary">
                                        <option value="">Semua Tipe</option>
                                        <option value="payment" {{ request('type') == 'payment' ? 'selected' : '' }}>Payment</option>
                                        <option value="withdrawal" {{ request('type') == 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                                    </select>
                                </div>
                                <div class="flex-fill">
                                    <label class="form-label fw-bold">
                                        <i class="fa fa-check-circle me-2 text-primary"></i>
                                        Status
                                    </label>
                                    <select name="status" class="form-select border-primary">
                                        <option value="">Semua Status</option>
                                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
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
        <h4 class="panel-title">
            <i class="fa fa-filter me-2"></i>Filter Data
        </h4>
    </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="1%">#</th>
                            <th>ID Pesanan</th>
                            <th>Tipe</th>
                            <th>Owner & Outlet</th>
                            <th>Jumlah (Net)</th>
                            <th>Fee</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $index => $transaction)
                            <tr>
                                <td>{{ $transactions->firstItem() + $index }}</td>
                                <td>{{ $transaction->order_id ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge {{ $transaction->type == 'payment' ? 'bg-success' : 'bg-danger' }}">
                                        {{ strtoupper($transaction->type) }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $transaction->owner->brand_name ?? '-' }}</strong><br>
                                </td>
                                <td class="{{ $transaction->type == 'withdrawal' ? 'text-danger' : '' }}">
                                    <strong>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</strong>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        Rp {{ number_format($transaction->service_fee_amount, 0, ',', '.') }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge {{ $transaction->status == 'success' ? 'bg-success' : 'bg-warning' }}">
                                        {{ $transaction->status }}
                                    </span>
                                </td>
                                <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-4">Data tidak ditemukan</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-light fw-bold" style="border-top: 2px solid #ccc;">
                        <tr>
                            <td colspan="4" class="text-end text-uppercase">Total Halaman Ini (Status Success Only):</td>
                            <td colspan="4">
                                <span class="text-success me-3">Pemasukan: Rp {{ number_format($pageIncome, 0, ',', '.') }}</span>
                                <span class="text-danger">Penarikan: Rp {{ number_format($pageWithdrawal, 0, ',', '.') }}</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Menampilkan {{ $transactions->firstItem() }} sampai {{ $transactions->lastItem() }}
                    dari {{ $transactions->total() }} data
                </div>
                <div>
                    {{ $transactions->appends(request()->query())->links() }}
                </div>
            </div>
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
