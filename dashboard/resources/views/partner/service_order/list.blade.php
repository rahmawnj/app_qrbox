@extends('layouts.dashboard.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <h2 class="mb-0">Daftar Service Order Belum Selesai</h2>
        </div>

        <div class="row row-cols-1 row-cols-lg-2 g-4" id="serviceOrderList">
            {{-- Mengubah variabel dari $transactions menjadi $dropOffTransactions --}}
            @forelse($dropOffTransactions as $dropOffTransaction)
                <div class="col d-flex align-items-stretch">
                    <div class="card service-order-card shadow-sm">
                        <div class="card-body p-0 d-flex flex-column flex-md-row">

                            <div class="transaction-info-section text-center p-3">
                                {{-- Mengakses relasi transaction dan outlet di dalamnya --}}
                                <h6 class="outlet-name">{{ $dropOffTransaction->transaction->outlet->outlet_name ?? 'N/A' }}
                                </h6>

                                <span class="amount-display d-block mt-2">
                                    Total:
                                    <strong>Rp{{ number_format($dropOffTransaction->transaction->amount, 0, ',', '.') }}</strong>
                                </span>

                                {{-- Mengakses progress langsung dari dropOffTransaction --}}
                                <span class="badge bg-primary mt-2">{{ $dropOffTransaction->progress }}</span>
                            </div>

                            <div class="transaction-details-section p-3 d-flex flex-column">
                                <div class="detail-group mb-auto">
                                    <div class="row text-center">
                                        <b
                                            class="transaction-id text-center my-2">#{{ $dropOffTransaction->transaction->order_id }}</b>
                                    </div>

                                    {{-- Informasi Pelanggan --}}
                                    <div class="detail-item">
                                        <i class="fas fa-user me-2 text-primary"></i>
                                        <span>Customer:</span>
                                        <strong class="ms-auto">
                                            {{ $dropOffTransaction->customer_name ?? 'Pelanggan Walk-in' }}
                                        </strong>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-phone-alt me-2 text-primary"></i>
                                        <span>Telepon:</span>
                                        <strong class="ms-auto">
                                            {{ $dropOffTransaction->customer_phone_number ?? '-' }}
                                        </strong>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-info-circle me-2 text-primary"></i>
                                        <span>Nama Layanan:</span>
                                        <strong class="ms-auto">
                                            {{ ucfirst($dropOffTransaction->service->name ?? 'N/A') }}
                                        </strong>
                                    </div>

                                    <div class="detail-item">
                                        <i class="fas fa-clock me-2 text-primary"></i>
                                        <span>Dibuat Pada:</span>
                                        <strong class="ms-auto">
                                            {{ $dropOffTransaction->created_at->isoFormat('D MMM [pukul] HH:mm') }} WIB
                                        </strong>
                                    </div>

                                    <div class="detail-item">
                                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                        <span>Estimasi Selesai:</span>
                                        @if ($dropOffTransaction->estimated_completion_at)
                                            <strong class="ms-auto text-danger">
{{ \Carbon\Carbon::parse($dropOffTransaction->estimated_completion_at)->isoFormat('D MMMM YYYY') }} WIB
                                            </strong>
                                        @else
                                            <strong class="ms-auto text-muted">-</strong>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-3 d-grid gap-2">
                                    <button type="button" class="btn btn-info btn-action" data-bs-toggle="modal"
                                        data-bs-target="#deviceServiceModal-{{ $dropOffTransaction->transaction->id }}">
                                        <i class="fas fa-cogs me-1"></i> Lihat Layanan Perangkat
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal for Device Services --}}
                <div class="modal fade" id="deviceServiceModal-{{ $dropOffTransaction->transaction->id }}" tabindex="-1"
                    aria-labelledby="deviceServiceModalLabel-{{ $dropOffTransaction->transaction->id }}"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"
                                    id="deviceServiceModalLabel-{{ $dropOffTransaction->transaction->id }}">Detail
                                    Layanan Order #{{ $dropOffTransaction->transaction->order_id }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
                                {{-- Informasi Umum Transaksi di Modal --}}
                                <div class="row mb-4 border-bottom pb-3">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Customer:</strong> <span
                                                class="font-weight-bold">{{ $dropOffTransaction->customer_name ?? 'Pelanggan Walk-in' }}</span>
                                        </p>
                                        <p class="mb-1"><strong>Telepon:</strong>
                                            {{ $dropOffTransaction->customer_phone_number ?? '-' }}</p>
                                        <p class="mb-1"><strong>Kasir:</strong>
                                            {{ $dropOffTransaction->cashier_name ?? '-' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Metode Bayar:</strong>
                                            {{ ucfirst(str_replace('_', ' ', $dropOffTransaction->transaction->payments->first()->payment_method ?? 'N/A')) }}
                                            @if (
                                                $dropOffTransaction->transaction->payments->first() &&
                                                    $dropOffTransaction->transaction->payments->first()->payment_type)
                                                ({{ ucfirst(str_replace('_', ' ', $dropOffTransaction->transaction->payments->first()->payment_type)) }})
                                            @endif
                                        </p>
                                        <p class="mb-1"><strong>Nama Layanan Utama:</strong>
                                            {{ ucfirst($dropOffTransaction->service->name ?? 'N/A') }}
                                        </p>
                                        <p class="mb-1"><strong>Estimasi Selesai:</strong>
                                            @if ($dropOffTransaction->estimated_completion_at)
                                                <span class="text-danger font-weight-bold">
                                                    {{ \Carbon\Carbon::parse($dropOffTransaction->estimated_completion_at)->isoFormat('D MMMM YYYY') }}
                                                    WIB
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                {{-- Detail Tambahan (Addons) --}}
                                @if (!empty($dropOffTransaction->addons) && is_array($dropOffTransaction->addons))
                                    <h6 class="mb-2">Detail Tambahan:</h6>
                                    <ul class="list-unstyled mb-3 ms-3">
                                        @foreach ($dropOffTransaction->addons as $addon)
                                            <li class="d-flex justify-content-between">
                                                <span>- {{ $addon['name'] ?? 'N/A' }}</span>
                                                <span>Rp{{ number_format($addon['price'] ?? 0, 0, ',', '.') }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                @if ($dropOffTransaction->notes)
                                    <p class="mt-3 mb-0"><strong>Catatan:</strong></p>
                                    <p class="text-muted fst-italic">{{ $dropOffTransaction->notes }}
                                    </p>
                                @endif

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6>Status Progres Utama Order:</h6>
                                    <form
                                        action="{{ route('partner.service-orders.update-progress', $dropOffTransaction->id) }}"
                                        method="POST" class="d-inline-block"
                                        id="progressForm-{{ $dropOffTransaction->id }}">
                                        @csrf
                                        <select name="progress" class="form-select form-select-sm"
                                            onchange="confirmAndSubmitProgress(this, '{{ $dropOffTransaction->id }}')"
                                            {{ in_array($dropOffTransaction->progress, \App\Constants\OrderStatus::finalStatuses()) ? 'disabled' : '' }}>
                                            @foreach (\App\Constants\OrderStatus::STATUSES as $key => $statusData)
                                                <option value="{{ $key }}"
                                                    {{ $dropOffTransaction->progress == $key ? 'selected' : '' }}>
                                                    {{ $statusData['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                </div>

                                <hr class="my-4">

                                <h5 class="mb-3">Daftar Layanan Perangkat</h5>
                                {{-- Mengakses relasi deviceTransactions melalui transaction --}}
                                @if ($dropOffTransaction->transaction->deviceTransactions->isEmpty())
                                    <div class="alert alert-warning text-center">
                                        Tidak ada layanan perangkat yang terkait dengan transaksi ini.
                                    </div>
                                @else
                                    <div class="list-group">
                                        @foreach ($dropOffTransaction->transaction->deviceTransactions as $deviceService)
                                            @php
                                                $activatedAt = $deviceService->activated_at;
                                                $bypassActivation = $deviceService->bypass_activation;
                                                $isServiceActivated = !is_null($activatedAt);
                                                $diffHoursSinceActivated = null;

                                                $buttonText = 'Mulai Layanan';
                                                $buttonClass = 'btn-success';
                                                $buttonDisabled = '';
                                                $serviceStatusText = 'Belum Dimulai';
                                                $activatedInfo = '';

                                                $machineStatus = $deviceService->status
                                                    ? 'Mesin Sedang Berjalan (Konfirmasi IoT)'
                                                    : 'Mesin Telah Berhenti';

                                                if ($isServiceActivated) {
                                                    $activatedCarbon = Carbon\Carbon::parse($activatedAt);
                                                    $now = Carbon\Carbon::now();
                                                    $diffHoursSinceActivated = $now->diffInHours($activatedCarbon);

                                                    $activatedInfo =
                                                        'Dimulai: ' .
                                                        $activatedCarbon->isoFormat('D MMM [pukul] HH:mm') .
                                                        ' WIB';

                                                    if ($diffHoursSinceActivated >= 24) {
                                                        $buttonText = 'Layanan Selesai (Otomatis)';
                                                        $buttonClass = 'btn-secondary';
                                                        $serviceStatusText = 'Selesai';
                                                        $buttonDisabled = 'disabled';
                                                        $machineStatus = 'Mesin Telah Berhenti';
                                                    } else {
                                                        $serviceStatusText = 'Sedang Berjalan';

                                                        if ($deviceService->status) {
                                                            $buttonText = 'Layanan Sedang Berjalan';
                                                            $buttonClass = 'btn-warning';
                                                            $buttonDisabled = 'disabled';
                                                            $machineStatus = 'Mesin Sedang Berjalan (Konfirmasi IoT)';
                                                        } else {
                                                            $buttonText = 'Layanan Selesai (Trigger Ulang)';
                                                            $buttonClass = 'btn-info';
                                                            $buttonDisabled = '';
                                                            $machineStatus = 'Mesin Telah Berhenti';
                                                        }
                                                    }
                                                } else {
                                                    $buttonText = 'Mulai Layanan';
                                                    $buttonClass = 'btn-success';
                                                    $serviceStatusText = 'Belum Dimulai';
                                                    $buttonDisabled = '';
                                                    $machineStatus = 'Mesin Belum Dijalankan';
                                                }

                                                $bypassInfo = '';
                                                if ($bypassActivation) {
                                                    $bypassCarbon = Carbon\Carbon::parse($bypassActivation);
                                                    $bypassInfo =
                                                        'Bypass Aktif Sejak: ' .
                                                        $bypassCarbon->isoFormat('D MMM [pukul] HH:mm') .
                                                        ' WIB';
                                                }
                                            @endphp
                                            <div
                                                class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                                <div>
                                                    <h6 class="mb-1">Kode Perangkat:
                                                        <strong>{{ $deviceService->device_code }}</strong>
                                                    </h6>
                                                    <small class="text-muted">Tipe Layanan:
                                                        {{ ucfirst($deviceService->service_type ?? 'N/A') }}</small>
                                                    <br><small>Status Layanan:
                                                        <strong>{{ $serviceStatusText }}</strong></small>
                                                    <br><small>Status Mesin:
                                                        <strong>{{ $machineStatus }}</strong></small>
                                                    @if ($isServiceActivated)
                                                        <br><small class="text-info">{{ $activatedInfo }}</small>
                                                    @endif
                                                    @if ($bypassInfo)
                                                        <br><small class="text-warning">{{ $bypassInfo }}</small>
                                                    @endif
                                                </div>
                                                <form
                                                    action="{{ route('partner.service-orders.activate-device', $deviceService->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn {{ $buttonClass }} btn-sm mt-2 mt-md-0 {{ $buttonDisabled }}">
                                                        {{ $buttonText }}
                                                    </button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center" role="alert">
                        Belum ada service order yang belum selesai.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --primary-blue: #007bff;
            --light-gray: #f0f2f5;
            --border-gray: #e9ecef;
            --text-dark: #343a40;
            --text-muted: #6c757d;
            --success-green: #28a745;
            --card-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            /* Slightly less pronounced shadow */
            --card-hover-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
            /* Slightly less pronounced hover shadow */
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .header-section {
            margin-bottom: 30px;
        }

        .service-order-card {
            border-radius: 10px;
            /* Slightly smaller border-radius */
            border: none;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .service-order-card:hover {
            transform: translateY(-3px);
            /* Slightly less lift on hover */
            box-shadow: var(--card-hover-shadow);
        }

        .transaction-info-section {
            background-color: var(--light-gray);
            flex-basis: 30%;
            /* Reduced from 35% to give more space to details */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
            /* Reduced padding */
            border-right: 1px solid var(--border-gray);
        }

        .transaction-info-section .transaction-id {
            font-size: 0.75rem;
            /* Slightly smaller font */
            color: var(--text-muted);
            font-weight: 500;
            margin-bottom: 0.4rem;
            /* Reduced margin */
        }

        .transaction-info-section .outlet-name {
            font-size: 1.5rem;
            /* Reduced from 1.8rem */
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 0.6rem;
            /* Reduced margin */
        }

        .transaction-info-section .amount-display {
            font-size: 1rem;
            /* Reduced from 1.1rem */
            font-weight: 600;
            color: var(--success-green);
        }

        .transaction-details-section {
            flex-basis: 70%;
            /* Increased to 70% from 65% to compensate */
            padding: 1.25rem;
            /* Reduced padding */
            display: flex;
            flex-direction: column;
        }

        .detail-group .detail-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.6rem;
            /* Reduced margin */
            font-size: 0.9rem;
            /* Reduced from 0.95rem */
            color: var(--text-dark);
        }

        .detail-group .detail-item strong {
            margin-left: auto;
            color: var(--text-dark);
            font-size: 0.95rem;
            /* Slightly reduced font */
        }

        .detail-group .detail-item i {
            width: 20px;
            /* Slightly smaller icon area */
            text-align: center;
            font-size: 0.9rem;
        }

        .btn-action {
            border-radius: 6px;
            /* Slightly smaller border-radius */
            font-size: 0.95rem;
            /* Slightly smaller font */
            padding: 0.6rem 1.2rem;
            /* Reduced padding */
            font-weight: 600;
        }

        /* Modal specific styles */
        .modal-body .list-group-item {
            border-radius: 6px;
            /* Slightly smaller border-radius */
            margin-bottom: 8px;
            /* Reduced margin */
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
            /* Slightly less pronounced shadow */
            background-color: #fcfcfc;
            padding: 0.75rem 1rem;
            /* Reduced padding */
        }

        .modal-body .list-group-item h6 {
            font-size: 0.95rem;
            /* Reduced font size */
            margin-bottom: 0.3rem;
            /* Reduced margin */
            color: #333;
        }

        .modal-body .list-group-item small {
            font-size: 0.8rem;
            /* Reduced font size for small text */
        }


        /* Responsive adjustments */
        @media (max-width: 767.98px) {
            .service-order-card .card-body {
                flex-direction: column;
            }

            .transaction-info-section,
            .transaction-details-section {
                flex-basis: 100%;
                border-right: none;
                padding: 1rem;
                /* Even smaller padding on mobile */
            }

            .transaction-info-section {
                border-bottom: 1px solid var(--border-gray);
                border-radius: 10px 10px 0 0;
            }

            .transaction-details-section {
                border-radius: 0 0 10px 10px;
            }

            .transaction-info-section .outlet-name {
                font-size: 1.4rem;
                /* Adjusted for mobile */
            }

            .btn-action {
                padding: 0.6rem 1rem;
                /* Adjusted for mobile */
                font-size: 0.9rem;
            }
        }
    </style>
    <link href="{{ asset('assets/plugins/gritter/css/jquery.gritter.css') }}" rel="stylesheet" />
@endpush

@push('scripts')
    <script src="{{ asset('assets/plugins/gritter/js/jquery.gritter.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Make the PHP final statuses available in JavaScript
        window.finalOrderStatuses = @json(\App\Constants\OrderStatus::finalStatuses());

        function confirmAndSubmitProgress(selectElement, transactionId) {
            const selectedProgress = selectElement.value;
            const currentProgress = selectElement.getAttribute('data-current-progress');
            const form = document.getElementById('progressForm-' + transactionId);

            // Check if the selected progress is one of the final statuses
            if (window.finalOrderStatuses.includes(selectedProgress)) {
                Swal.fire({
                    title: 'Konfirmasi Perubahan Status',
                    text: 'Apakah Anda yakin ingin mengubah status progress menjadi "' + selectElement.options[
                        selectElement.selectedIndex].text + '"? Status ini bersifat final.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Ubah Status!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    } else {
                        // If user cancels, revert the select box to its original value
                        selectElement.value = currentProgress;
                    }
                });
            } else {
                // If it's not a final status, submit directly without confirmation
                form.submit();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize data-current-progress for all select elements on page load
            document.querySelectorAll('select[name="progress"]').forEach(function(select) {
                select.setAttribute('data-current-progress', select.value);
            });

            @if (session('success'))
                $.gritter.add({
                    title: 'Sukses',
                    text: '{{ session('success') }}',
                    class_name: 'gritter-success gritter-light',
                    sticky: false,
                    time: 3000
                });
            @endif

            @if (session('error'))
                $.gritter.add({
                    title: 'Error',
                    text: '{{ session('error') }}',
                    class_name: 'gritter-error gritter-light',
                    sticky: false,
                    time: 3000
                });
            @endif
        });
    </script>
@endpush
