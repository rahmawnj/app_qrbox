@extends('layouts.landingpage.dashboard-app')

@section('title', 'Dashboard Member')
@section('header_title', 'Selamat Datang, ' . (Auth::user()->name ?? 'Member') . '!')

@section('content')
   @if ($profileProgressPercentage < 100)
    <div class="row mb-4">
        <div class="col-12">
            <div class="dashboard-card profile-completion-card">
                <h3 class="card-title-custom"><i class="fas fa-user-check me-2"></i> Lengkapi Profil Anda</h3>
                <div class="profile-progress">
                    <h4>Progress Profil: <span id="profileProgressValue">{{ $profileProgressPercentage }}%</span></h4>
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" style="width: {{ $profileProgressPercentage }}%;"></div>
                    </div>
                    <div class="profile-status-icons mb-3">
                        <div class="status-item @if ($profileCompletion['email_verified']) completed @else pending @endif"
                            id="emailStatus">
                            <i
                                class="fas @if ($profileCompletion['email_verified']) fa-check-circle @else fa-envelope @endif me-1"></i>
                            Email Terverifikasi
                        </div>
                        <div class="status-item @if ($profileCompletion['phone_number_added']) completed @else pending @endif"
                            id="phoneStatus">
                            <i
                                class="fas @if ($profileCompletion['phone_number_added']) fa-check-circle @else fa-phone-slash @endif me-1"></i>
                            Nomor Telepon
                        </div>
                        <div class="status-item @if ($profileCompletion['address_complete']) completed @else pending @endif"
                            id="addressStatus">
                            <i
                                class="fas @if ($profileCompletion['address_complete']) fa-check-circle @else fa-map-marked-alt @endif me-1"></i>
                            Alamat & Lokasi
                        </div>
                    </div>
                    <div class="text-center mt-auto">
                        <a href="{{ route('home.member.profile') }}" class="btn btn-warning btn-sm">Lengkapi Sekarang <i
                                class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

    {{-- Baris 2: Saldo & Jumlah Brand (Kolom 8 & 4) --}}
    <div class="row">
        <div class="col-lg-8 mb-4">
            <!-- Card: Total Saldo Anda -->
            <div class="dashboard-card wallet-card h-100">
                <h3 class="card-title-custom"><i class="fas fa-wallet me-2"></i> Total Saldo Anda</h3>
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                    <p class="h2 fw-bold text-primary mb-2 mb-md-0">Rp {{ number_format($totalBalance, 0, ',', '.') }}</p>
                    <div class="d-flex flex-wrap justify-content-center justify-content-md-end gap-2">
                        <button class="btn btn-primary btn-sm"><i class="fas fa-plus-circle me-1"></i> Top Up</button>
                    </div>
                </div>
                <p class="text-muted small text-center text-md-start">Saldo terakhir diperbarui:
                    {{ \Carbon\Carbon::now()->format('d M Y, H:i') }}</p>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <!-- Card: Brands Berlangganan -->
            <div class="dashboard-card brands-subscribed-card h-100">
                <h3 class="card-title-custom"><i class="fas fa-store me-2"></i> Brands Dilanggan</h3>
                <div class="text-center my-auto py-3">
                    <p class="h1 fw-bold text-primary">{{ $subscribedBrandsCount }}</p>
                    <p class="lead text-muted">Brand Favorit Langganan Anda</p>
                    @if ($pendingSubscriptionsCount > 0)
                        <p class="small text-warning mt-2 mb-0">
                            <i class="fas fa-exclamation-triangle me-1"></i> {{ $pendingSubscriptionsCount }} Langganan
                            Pending
                        </p>
                    @endif
                </div>
                <div class="text-center mt-auto">
                    <a href="{{ route('home.member.brands') }}" class="btn btn-outline-primary btn-sm">Lihat Daftar Brand <i
                            class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>

    {{-- Baris 3: Aktivitas Terbaru --}}
    <div class="row mt-4">
        <div class="col-12">
            <!-- Card: Aktivitas Terbaru -->
            <div class="dashboard-card recent-activity-card">
                <h3 class="card-title-custom"><i class="fas fa-receipt me-2"></i> Aktivitas Terbaru</h3>
                <div id="recentTransactionsList" class="list-group list-group-flush">
                    @forelse($recentTransactions as $transaction)
                        <div class="list-group-item bg-transparent">
                            <div>
                                <p class="mb-1">
                                    {{ $transaction->description ?? 'Transaksi #' . $transaction->id }}
                                    @if ($transaction->owner)
                                        - {{ $transaction->owner->brand_name }}
                                    @endif {{-- Menampilkan nama brand --}}
                                </p>
                                <small class="text-muted">{{ $transaction->created_at->format('d M Y, H:i') }}</small>
                            </div>
                            <div class="d-flex flex-column align-items-end">
                                <span class="amount">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
                                <span
                                    class="status @if ($transaction->status == 'success') success @elseif($transaction->status == 'pending') pending @else failed @endif">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item bg-transparent text-center text-muted py-4">
                            Belum ada aktivitas transaksi terbaru.
                        </div>
                    @endforelse
                </div>
                <div class="text-center mt-4">
                    <a href="{{ route('home.member.transactions') }}" class="btn btn-outline-primary">Lihat Semua Transaksi <i
                            class="fas fa-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('styles')
    <style>
        /* Custom styles for dashboard cards and elements */
        .dashboard-card {
            background-color: var(--panel-bg);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            /* Softer shadow */
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: flex;
            flex-direction: column;
        }

        .dashboard-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .card-title-custom {
            color: var(--text-color-light);
            font-size: 1.6rem;
            margin-bottom: 25px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            /* Lighter border */
            padding-bottom: 15px;
        }

        .wallet-card .h2 {
            font-size: 2.5rem;
            color: var(--primary-color);
        }

        .profile-progress {
            flex-grow: 1;
            /* Allow progress section to grow */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            /* Push button to bottom */
        }

        .profile-progress h4 {
            font-size: 1.2rem;
            color: var(--text-color-light);
            margin-bottom: 10px;
        }

        .progress-bar-container {
            width: 100%;
            background-color: #e2e8f0;
            border-radius: 5px;
            height: 8px;
            margin-bottom: 15px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background-color: var(--primary-color);
            border-radius: 5px;
            transition: width 0.5s ease-in-out;
        }

        .profile-status-icons {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .profile-status-icons .status-item {
            display: flex;
            align-items: center;
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text-color-muted-dark);
            /* Default to muted */
        }

        .profile-status-icons .status-item i {
            margin-right: 8px;
            font-size: 1.2rem;
        }

        .profile-status-icons .status-item.completed {
            color: var(--accent-green);
        }

        .profile-status-icons .status-item.pending {
            color: var(--text-color-muted-dark);
        }

        /* Recent Activity List */
        .recent-activity-card .list-group-item {
            border-color: rgba(0, 0, 0, 0.05);
            /* Lighter border */
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.2s ease;
        }

        .recent-activity-card .list-group-item:hover {
            background-color: var(--light-bg);
        }

        .recent-activity-card .list-group-item span {
            font-weight: 500;
        }

        .recent-activity-card .list-group-item .amount {
            color: var(--primary-color);
            font-weight: 600;
        }

        .recent-activity-card .list-group-item .status {
            font-size: 0.85rem;
            padding: 4px 8px;
            border-radius: 5px;
        }

        .recent-activity-card .list-group-item .status.success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--accent-green);
        }

        .recent-activity-card .list-group-item .status.pending {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--secondary-color);
        }

        .recent-activity-card .list-group-item .status.failed {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--accent-red);
        }

        /* Quick Actions */
        .action-item-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 25px 15px;
            background-color: var(--white-bg);
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            text-decoration: none;
            color: var(--text-dark);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            text-align: center;
            min-height: 120px;
            /* Ensure consistent height */
        }

        .action-item-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            color: var(--primary-color);
        }

        .action-item-card i {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .action-item-card p {
            font-weight: 600;
            font-size: 1rem;
        }

        /* Brands Subscribed Card */
        .brands-subscribed-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            /* Push link to bottom */
            align-items: center;
            text-align: center;
        }

        /* Responsive Adjustments for Dashboard */
        @media (max-width: 767.98px) {
            .wallet-card .h2 {
                font-size: 2rem;
            }

            .wallet-card .btn {
                width: 100%;
                margin-bottom: 10px;
            }

            .wallet-card .btn:last-child {
                margin-bottom: 0;
            }

            .wallet-card .text-muted.small {
                text-align: center;
            }

            .profile-status-icons {
                justify-content: center;
            }

            .action-item-card {
                padding: 20px 10px;
                min-height: 100px;
            }

            .action-item-card i {
                font-size: 2rem;
            }

            .action-item-card p {
                font-size: 0.9rem;
            }
        }
    </style>
@endpush

{{-- No JavaScript push scripts needed anymore, as data is handled by Blade --}}
