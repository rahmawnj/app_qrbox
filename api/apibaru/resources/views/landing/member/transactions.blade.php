@extends('layouts.landingpage.dashboard-app')

@section('title', 'Riwayat Transaksi')
@section('header_title', 'Riwayat Transaksi Anda')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="dashboard-card">
                <h3 class="card-title-custom mb-4"><i class="fas fa-history me-2"></i> Semua Transaksi</h3>
                <div class="table-responsive">
                    <table class="table table-hover transaction-table align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Tanggal</th>
                                <th scope="col">Order ID</th>
                                <th scope="col">Tipe Layanan</th>
                                <th scope="col">Brand / Outlet</th>
                                <th scope="col">Jumlah</th>
                                <th scope="col">Metode Bayar</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('d M Y, H:i') }}</td>
                                    <td>
                                        <span class="fw-bold text-primary">{{ $transaction->order_id ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        @if ($transaction->channel_type == 'dropoff')
                                            <span class="badge bg-success-subtle text-success fw-bold">Drop-off</span>
                                        @elseif($transaction->channel_type == 'self_service')
                                            <span class="badge bg-info-subtle text-info fw-bold">Self-Service</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary fw-bold">Lainnya</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($transaction->owner)
                                            <p class="mb-0 fw-bold">{{ $transaction->owner->brand_name }}</p>
                                        @endif
                                        @if ($transaction->outlet)
                                            <small class="text-muted">{{ $transaction->outlet->outlet_name }}</small>
                                        @endif
                                    </td>
                                    <td><span class="fw-bold text-success">Rp
                                            {{ number_format($transaction->amount, 0, ',', '.') }}</span></td>
                                    <td>
                                        @if ($transaction->payment)
                                            <span class="badge bg-primary-subtle text-primary fw-bold">
                                                {{ ucfirst(str_replace('_', ' ', $transaction->payment->payment_method)) }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#transactionDetailModal-{{ $transaction->id }}">
                                            Detail
                                        </button>
                                    </td>
                                </tr>

                                {{-- Transaction Detail Modal --}}
                                <div class="modal fade" id="transactionDetailModal-{{ $transaction->id }}" tabindex="-1"
                                    aria-labelledby="transactionDetailModalLabel-{{ $transaction->id }}"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content dashboard-card">
                                            <div class="modal-header border-bottom-dashed pb-3">
                                                <h5 class="modal-title fw-bold text-color-light"
                                                    id="transactionDetailModalLabel-{{ $transaction->id }}">Detail
                                                    Transaksi #{{ $transaction->order_id ?? 'N/A' }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6 class="text-primary mb-3"><i
                                                                class="fas fa-info-circle me-2"></i>Informasi Umum</h6>
                                                        <ul class="list-group list-group-flush mb-4">
                                                            <li
                                                                class="list-group-item bg-transparent px-0 py-2 border-bottom-dashed">
                                                                <strong>Order ID:</strong> <span
                                                                    class="fw-bold text-primary">{{ $transaction->order_id ?? 'N/A' }}</span>
                                                            </li>
                                                            <li
                                                                class="list-group-item bg-transparent px-0 py-2 border-bottom-dashed">
                                                                <strong>Tipe Transaksi:</strong> <span
                                                                    class="badge bg-info-subtle text-info fw-bold">{{ ucfirst(str_replace('_', ' ', $transaction->type)) }}</span>
                                                            </li>
                                                            <li
                                                                class="list-group-item bg-transparent px-0 py-2 border-bottom-dashed">
                                                                <strong>Channel:</strong>
                                                                @if ($transaction->channel_type == 'dropoff')
                                                                    <span class="badge bg-success-subtle text-success">Drop
                                                                        Off</span>
                                                                @elseif($transaction->channel_type == 'self_service')
                                                                    <span class="badge bg-info-subtle text-info">Self
                                                                        Service</span>
                                                                @else
                                                                    <span
                                                                        class="badge bg-secondary-subtle text-secondary">N/A</span>
                                                                @endif
                                                            </li>
                                                            <li
                                                                class="list-group-item bg-transparent px-0 py-2 border-bottom-dashed">
                                                                <strong>Tanggal Dibuat:</strong>
                                                                {{ $transaction->created_at->format('d F Y, H:i') }} WIB
                                                            </li>
                                                            <li class="list-group-item bg-transparent px-0 py-2">
                                                                <strong>Terakhir Diperbarui:</strong>
                                                                {{ $transaction->updated_at->format('d F Y, H:i') }} WIB
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6 class="text-primary mb-3"><i
                                                                class="fas fa-store me-2"></i>Detail Outlet</h6>
                                                        <ul class="list-group list-group-flush mb-4">
                                                            <li
                                                                class="list-group-item bg-transparent px-0 py-2 border-bottom-dashed">
                                                                <strong>Brand:</strong>
                                                                {{ $transaction->owner->brand_name ?? 'N/A' }}
                                                            </li>
                                                            <li
                                                                class="list-group-item bg-transparent px-0 py-2 border-bottom-dashed">
                                                                <strong>Outlet:</strong>
                                                                {{ $transaction->outlet->outlet_name ?? 'N/A' }}
                                                            </li>
                                                            <li class="list-group-item bg-transparent px-0 py-2">
                                                                <strong>Alamat Outlet:</strong>
                                                                {{ $transaction->outlet->address ?? 'N/A' }}
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>

                                                <div class="row mt-3">
                                                    <div class="col-12">
                                                        <h6 class="text-primary mb-3"><i
                                                                class="fas fa-money-bill-wave me-2"></i>Ringkasan Pembayaran
                                                        </h6>
                                                        <ul class="list-group list-group-flush mb-4">
                                                            <li
                                                                class="list-group-item bg-transparent px-0 py-2 border-bottom-dashed">
                                                                <strong>Total Pembayaran:</strong> <span
                                                                    class="fw-bold text-success">Rp
                                                                    {{ number_format($transaction->amount, 0, ',', '.') }}</span>
                                                            </li>
                                                            @php
                                                                $latestPayment = $transaction
                                                                    ->payments()
                                                                    ->latest()
                                                                    ->first();
                                                            @endphp

                                                            @if ($latestPayment)
                                                                <li
                                                                    class="list-group-item bg-transparent px-0 py-2 border-bottom-dashed">
                                                                    <strong>Metode Pembayaran:</strong>
                                                                    <span
                                                                        class="badge bg-primary-subtle text-primary fw-bold">
                                                                        {{ ucfirst(str_replace('_', ' ', $latestPayment->payment_method)) }}
                                                                    </span>
                                                                </li>
                                                                <li class="list-group-item bg-transparent px-0 py-2">
                                                                    <strong>Tanggal Pembayaran:</strong>
                                                                    {{ \Carbon\Carbon::parse($latestPayment->payment_time)->format('d F Y, H:i') }}
                                                                    WIB
                                                                </li>
                                                                @if ($latestPayment->notes)
                                                                    <li class="list-group-item bg-transparent px-0 py-2">
                                                                        <strong>Catatan Pembayaran:</strong>
                                                                        <p class="alert alert-secondary mt-2 p-2">
                                                                            {{ $latestPayment->notes }}</p>
                                                                    </li>
                                                                @endif
                                                            @else
                                                                <li class="list-group-item bg-transparent px-0 py-2">
                                                                    <strong>Metode Pembayaran:</strong>
                                                                    <span
                                                                        class="badge bg-secondary-subtle text-secondary">N/A</span>
                                                                </li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                </div>

                                                {{-- Add service-specific details if needed, similar to the orders page --}}
                                                @if ($transaction->channel_type == 'dropoff' && $transaction->dropOffTransaction)
                                                    <div class="row mt-3">
                                                        <div class="col-12">
                                                            <h6 class="text-primary mb-3"><i
                                                                    class="fas fa-tshirt me-2"></i>Detail Layanan Drop-off
                                                            </h6>
                                                            <ul class="list-group list-group-flush mb-4">
                                                                <li
                                                                    class="list-group-item bg-transparent px-0 py-2 border-bottom-dashed">
                                                                    <strong>Layanan:</strong>
                                                                    {{ $transaction->dropOffTransaction->service->name ?? 'N/A' }}
                                                                </li>
                                                                <li
                                                                    class="list-group-item bg-transparent px-0 py-2 border-bottom-dashed">
                                                                    <strong>Harga Layanan:</strong> Rp
                                                                    {{ number_format($transaction->dropOffTransaction->service_price ?? 0, 0, ',', '.') }}
                                                                </li>
                                                                @if ($transaction->dropOffTransaction->addons)
                                                                    <li
                                                                        class="list-group-item bg-transparent px-0 py-2 border-bottom-dashed">
                                                                        <strong>Add-ons:</strong>
                                                                        @php $addons = json_decode($transaction->dropOffTransaction->addons, true); @endphp
                                                                        @if (!empty($addons))
                                                                            <ul class="list-unstyled mb-0 ms-3">
                                                                                @foreach ($addons as $addon)
                                                                                    <li>- {{ $addon['name'] ?? 'N/A' }} (Rp
                                                                                        {{ number_format($addon['price'] ?? 0, 0, ',', '.') }})
                                                                                    </li>
                                                                                @endforeach
                                                                            </ul>
                                                                        @else
                                                                            Tidak ada add-ons.
                                                                        @endif
                                                                    </li>
                                                                @endif
                                                                <li
                                                                    class="list-group-item bg-transparent px-0 py-2 border-bottom-dashed">
                                                                    <strong>Metode Pengambilan:</strong> <span
                                                                        class="badge bg-primary-subtle text-primary">{{ ucfirst(str_replace('_', ' ', $transaction->dropOffTransaction->pickup_method ?? 'N/A')) }}</span>
                                                                </li>
                                                                <li class="list-group-item bg-transparent px-0 py-2">
                                                                    <strong>Progress:</strong> <span
                                                                        class="badge bg-info-subtle text-info">{{ App\Constants\OrderStatus::label($transaction->dropOffTransaction->progress ?? 'unknown') }}</span>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                @elseif($transaction->channel_type == 'self_service' && $transaction->selfServiceTransaction)
                                                    <div class="row mt-3">
                                                        <div class="col-12">
                                                            <h6 class="text-primary mb-3"><i
                                                                    class="fas fa-washing-machine me-2"></i>Detail Layanan
                                                                Self-Service</h6>
                                                            <ul class="list-group list-group-flush mb-4">
                                                                <li
                                                                    class="list-group-item bg-transparent px-0 py-2 border-bottom-dashed">
                                                                    <strong>Kode Mesin:</strong>
                                                                    {{ $transaction->selfServiceTransaction->device_code ?? 'N/A' }}
                                                                </li>
                                                                {{-- Add more self-service specific details if available --}}
                                                            </ul>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if ($transaction->notes)
                                                    <div class="row mt-3">
                                                        <div class="col-12">
                                                            <h6 class="text-primary mb-3"><i
                                                                    class="fas fa-sticky-note me-2"></i>Catatan Transaksi
                                                            </h6>
                                                            <p class="alert alert-secondary p-3">{{ $transaction->notes }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endif

                                            </div>
                                            <div class="modal-footer bg-transparent border-top-dashed pt-3">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-info-circle me-2"></i> Belum ada riwayat transaksi.
                                        <p class="mt-2">Mulai bertransaksi sekarang untuk melihat riwayat Anda di sini!
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --panel-bg: #ffffff;
            /* Default light background */
            --border-color: #e0e0e0;
            /* Light border */
            --text-color-light: #343a40;
            /* Darker text for light mode */
            --light-bg: #f8f9fa;
            /* Lighter background for table headers, empty state */
            --primary-color: #0d6efd;
            /* Bootstrap primary */
            --info-color: #0dcaf0;
            /* Bootstrap info */
            --success-color: #198754;
            /* Bootstrap success */
            --warning-color: #ffc107;
            /* Bootstrap warning */
            --danger-color: #dc3545;
            /* Bootstrap danger */
            --secondary-color: #6c757d;
            /* Bootstrap secondary */
        }

        /* Dark mode adjustments */
        [data-bs-theme="dark"] {
            --panel-bg: #2b2d30;
            --border-color: #4a4d52;
            --text-color-light: #e2e6ea;
            --light-bg: #3c3f43;
        }

        .dashboard-card {
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            background-color: var(--panel-bg);
            border: 1px solid var(--border-color);
        }

        .card-title-custom {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-color-light);
            margin-bottom: 25px;
        }

        /* Table Specific Styles */
        .transaction-table {
            margin-bottom: 0;
            border-collapse: separate;
            /* Required for border-radius on cells in some browsers */
            border-spacing: 0;
            width: 100%;
        }

        .transaction-table th {
            background-color: var(--light-bg);
            color: var(--text-color-light);
            /* Adjusted for theme compatibility */
            font-weight: 600;
            border-bottom: 1px solid var(--border-color);
            padding: 15px;
            text-align: left;
        }

        .transaction-table td {
            vertical-align: middle;
            padding: 15px;
            border-top: 1px solid var(--border-color);
            /* Use consistent border color */
            color: var(--text-color-light);
        }

        .transaction-table tbody tr:hover {
            background-color: var(--light-bg);
            cursor: pointer;
            /* Indicate rows are clickable if you add row click functionality */
        }

        .transaction-table tbody tr:first-child td {
            border-top: none;
            /* No top border for the first row */
        }

        /* Badge styles for a modern look */
        .badge {
            padding: 0.6em 0.9em;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.85em;
            display: inline-flex;
            /* Use flex to better align text and potentially icons */
            align-items: center;
            justify-content: center;
            line-height: 1;
            /* Normalize line-height */
        }

        /* Define subtle badge backgrounds and text colors */
        .bg-primary-subtle {
            background-color: rgba(13, 110, 253, 0.15) !important;
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .bg-success-subtle {
            background-color: rgba(25, 135, 84, 0.15) !important;
        }

        .text-success {
            color: var(--success-color) !important;
        }

        .bg-info-subtle {
            background-color: rgba(13, 202, 240, 0.15) !important;
        }

        .text-info {
            color: var(--info-color) !important;
        }

        .bg-warning-subtle {
            background-color: rgba(255, 193, 7, 0.15) !important;
        }

        .text-warning {
            color: var(--warning-color) !important;
        }

        .bg-danger-subtle {
            background-color: rgba(220, 53, 69, 0.15) !important;
        }

        .text-danger {
            color: var(--danger-color) !important;
        }

        .bg-secondary-subtle {
            background-color: rgba(108, 117, 125, 0.15) !important;
        }

        .text-secondary {
            color: var(--secondary-color) !important;
        }


        /* Modal Specific Styles - Reusing dashboard-card styles where possible */
        .modal-content {
            border-radius: 12px;
            background-color: var(--panel-bg);
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            border-bottom: 1px dashed var(--border-color) !important;
            padding: 1.5rem;
        }

        .modal-title {
            font-size: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px dashed var(--border-color) !important;
            padding: 1.5rem;
        }

        .modal-body ul.list-group-flush li {
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
        }

        .modal-body h6 {
            font-size: 1.1rem;
        }

        .alert-secondary {
            background-color: var(--light-bg);
            border-color: var(--border-color);
            color: var(--text-color-light);
            border-radius: 8px;
        }
    </style>
@endpush

@push('scripts')
    {{-- No custom JavaScript needed here as Bootstrap's JS handles modals via data attributes --}}
@endpush
