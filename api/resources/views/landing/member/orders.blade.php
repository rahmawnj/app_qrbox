@extends('layouts.landingpage.dashboard-app')

@section('title', 'Daftar Pesanan Saya')
@section('header_title', 'Pesanan Laundry Anda')

@push('styles')
    <style>
        .dashboard-card {
            border-radius: 1rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            border: none;
            overflow: hidden;
            background-color: #ffffff;
        }
        
        .order-card {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15) !important;
        }

        .order-progress-bar {
            height: 8px;
            border-radius: 4px;
        }

        .progress-container {
            margin-top: 15px;
        }

        .card-title-custom {
            font-weight: bold;
            color: #344767;
        }
        
        .text-color-dark {
            color: #344767;
        }

        /* Styling untuk badge status */
        .badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.5em 0.8em;
            border-radius: 50rem;
        }

        .badge.bg-warning {
            background-color: #ffc107 !important;
            color: #000000 !important;
        }
        
        .badge.bg-info {
            background-color: #17a2b8 !important;
            color: #ffffff !important;
        }
        
        .badge.bg-secondary {
            background-color: #6c757d !important;
            color: #ffffff !important;
        }

        /* Styling untuk modal */
        .modal-header {
            border-bottom: none;
        }

        .modal-footer {
            border-top: none;
        }

        .list-unstyled li {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .list-unstyled li i {
            width: 20px;
        }

        .empty-state-card {
            border: 2px dashed #e9ecef;
            background-color: #f8f9fa;
        }

    </style>
@endpush

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="card-title-custom mb-3"><i class="fas fa-tasks me-2"></i> Pesanan yang Sedang Berlangsung</h3>
            <p class="text-muted">Lihat status dan estimasi selesai untuk setiap pesanan Anda.</p>
        </div>
    </div>

    @if($orders->isNotEmpty())
        <div class="row">
            @foreach($orders as $order)
                <div class="col-12 col-md-6 mb-4">
                    <div class="card dashboard-card order-card" data-bs-toggle="modal" data-bs-target="#orderDetailModal{{ $order->id }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="fw-bold text-color-dark mb-0">
                                    {{ $order->owner->brand_name ?? 'Outlet Tidak Dikenal' }}
                                </h5>
                                <span class="badge bg-secondary text-uppercase">{{ str_replace('_', ' ', $order->channel_type) }}</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <p class="text-muted mb-1">Status:</p>
                                    <span class="badge bg-warning">{{ ucfirst($order->status) }}</span>
                                </div>
                                <div class="text-end">
                                    <p class="text-muted mb-1">Total:</p>
                                    <h5 class="fw-bold text-color-dark mb-0">Rp {{ number_format($order->amount, 0, ',', '.') }}</h5>
                                </div>
                            </div>
                            
                            <p class="text-muted mb-2 small">
                                <i class="fas fa-barcode me-1"></i>
                                Pesanan ID: #{{ $order->order_id }}
                            </p>

                            @if($order->channel_type === 'drop_off' && $order->dropOffTransaction)
                                <div class="progress-container">
                                    <p class="text-muted mb-2 small d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-spinner fa-spin me-1"></i> Proses: <span class="fw-bold text-color-dark">{{ ucfirst($order->dropOffTransaction->progress) }}</span></span>
                                        <span>Estimasi: {{ \Carbon\Carbon::parse($order->dropOffTransaction->estimated_completion_at)->format('d F Y') }}</span>
                                    </p>
                                    @php
                                        // Contoh sederhana untuk progress bar, bisa disesuaikan
                                        $progressPercentage = 0;
                                        switch($order->dropOffTransaction->progress) {
                                            case 'received': $progressPercentage = 25; break;
                                            case 'washing': $progressPercentage = 50; break;
                                            case 'done': $progressPercentage = 75; break;
                                            case 'picked_up': $progressPercentage = 100; break;
                                            default: $progressPercentage = 0;
                                        }
                                    @endphp
                                    <div class="progress order-progress-bar">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $progressPercentage }}%" aria-valuenow="{{ $progressPercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="orderDetailModal{{ $order->id }}" tabindex="-1" aria-labelledby="orderDetailModalLabel{{ $order->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content dashboard-card">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold" id="orderDetailModalLabel{{ $order->id }}">Detail Pesanan #{{ $order->order_id }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <ul class="list-unstyled">
                                    <li class="d-flex justify-content-between mb-2">
                                        <span class="fw-bold"><i class="fas fa-store me-2"></i>Outlet:</span>
                                        <span>{{ $order->owner->brand_name ?? 'N/A' }}</span>
                                    </li>
                                    <li class="d-flex justify-content-between mb-2">
                                        <span class="fw-bold"><i class="fas fa-tag me-2"></i>Tipe Layanan:</span>
                                        <span><span class="badge bg-secondary text-uppercase">{{ str_replace('_', ' ', $order->channel_type) }}</span></span>
                                    </li>
                                    <li class="d-flex justify-content-between mb-2">
                                        <span class="fw-bold"><i class="fas fa-info-circle me-2"></i>Status Pesanan:</span>
                                        <span><span class="badge bg-warning">{{ ucfirst($order->status) }}</span></span>
                                    </li>
                                    <li class="d-flex justify-content-between mb-2">
                                        <span class="fw-bold"><i class="fas fa-money-bill-wave me-2"></i>Total Pembayaran:</span>
                                        <span class="fw-bold text-success">Rp {{ number_format($order->amount, 0, ',', '.') }}</span>
                                    </li>
                                </ul>
                                
                                <hr>
                                
                                @if($order->channel_type === 'drop_off' && $order->dropOffTransaction)
                                    <h6 class="fw-bold text-color-dark mb-3"><i class="fas fa-truck-loading me-2"></i>Detail Drop-Off</h6>
                                    <ul class="list-unstyled">
                                        <li class="d-flex justify-content-between mb-1">
                                            <span class="text-muted"><i class="fas fa-user me-2"></i>Nama Pelanggan:</span>
                                            <span>{{ $order->dropOffTransaction->customer_name }}</span>
                                        </li>
                                        <li class="d-flex justify-content-between mb-1">
                                            <span class="text-muted"><i class="fas fa-calendar-alt me-2"></i>Perkiraan Selesai:</span>
                                            <span>{{ \Carbon\Carbon::parse($order->dropOffTransaction->estimated_completion_at)->format('d F Y') }}</span>
                                        </li>
                                        <li class="d-flex justify-content-between mb-1">
                                            <span class="text-muted"><i class="fas fa-stream me-2"></i>Proses Saat Ini:</span>
                                            <span><span class="badge bg-info">{{ ucfirst($order->dropOffTransaction->progress) }}</span></span>
                                        </li>
                                    </ul>
                                    @if($order->dropOffTransaction->addons)
                                        <p class="fw-bold mt-3 mb-1"><i class="fas fa-plus-circle me-2"></i>Add-ons:</p>
                                        <ul class="list-unstyled ps-4">
                                            @foreach(json_decode($order->dropOffTransaction->addons) as $addon)
                                                <li><i class="fas fa-check-circle text-success me-1"></i>{{ $addon->name }} - <span class="fw-bold">Rp {{ number_format($addon->price, 0, ',', '.') }}</span></li>
                                            @endforeach
                                        </ul>
                                    @endif
                                @elseif($order->channel_type === 'self_service' && $order->selfServiceTransaction)
                                    <h6 class="fw-bold text-color-dark mb-3"><i class="fas fa-cogs me-2"></i>Detail Self-Service</h6>
                                    <ul class="list-unstyled">
                                        <li class="d-flex justify-content-between mb-1">
                                            <span class="text-muted"><i class="fas fa-tablet-alt me-2"></i>Kode Perangkat:</span>
                                            <span>{{ $order->selfServiceTransaction->device_code }}</span>
                                        </li>
                                        <li class="d-flex justify-content-between mb-1">
                                            <span class="text-muted"><i class="fas fa-user-check me-2"></i>Pilihan Pengguna:</span>
                                            <span>{{ $order->selfServiceTransaction->user_choice_identifier }}</span>
                                        </li>
                                    </ul>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="row">
            <div class="col-12">
                <div class="dashboard-card empty-state-card text-center py-5">
                    <i class="fas fa-box-open fa-4x text-muted mb-4"></i>
                    <h4 class="fw-bold text-color-dark mb-3">Tidak Ada Pesanan yang Sedang Berlangsung</h4>
                    <p class="text-muted mb-4">
                        Sepertinya Anda belum membuat pesanan baru atau semua pesanan Anda sudah selesai.
                    </p>
                    <a href="{{ url('/new-order') }}" class="btn btn-primary rounded-pill px-4">Buat Pesanan Baru</a>
                </div>
            </div>
        </div>
    @endif
@endsection