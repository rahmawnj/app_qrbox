@extends('layouts.landingpage.dashboard-app')

@section('title', 'Brands Anda')
@section('header_title', 'Brands yang Anda Langgan')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="page-section-title mb-0"><i class="fas fa-check-circle me-2 text-success"></i> Langganan Terverifikasi</h3>
        @if($pendingSubscriptions->count() > 0)
            <button type="button" class="btn btn-warning btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#pendingSubscriptionsModal">
                <i class="fas fa-hourglass-half me-1"></i> {{ $pendingSubscriptions->count() }} Langganan Pending
            </button>
        @endif
    </div>

    @if($verifiedSubscriptions->count() > 0)
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @foreach($verifiedSubscriptions as $subscription)
                @php
                    // Ambil data pivot subscription secara langsung
                    $subscriptionData = DB::table('subscription')
                                         ->where('member_id', Auth::user()->member->id)
                                         ->where('owner_id', $subscription->id)
                                         ->first();

                    $isCodeActive = false;
                    $code = null;
                    $codeGeneratedAt = null;

                    if ($subscriptionData && $subscriptionData->code && !$subscriptionData->is_used) {
                        $codeGeneratedAtCarbon = \Carbon\Carbon::parse($subscriptionData->pin_generated_time);
                        // Periksa apakah kode belum berusia 24 jam
                        if ($codeGeneratedAtCarbon->diffInHours(\Carbon\Carbon::now('WIB')) < 24) {
                            $isCodeActive = true;
                            $code = $subscriptionData->code;
                            $codeGeneratedAt = $codeGeneratedAtCarbon->format('d M Y, H:i:s');
                        }
                    }
                @endphp
                <div class="col">
                    <div class="brand-card h-100 d-flex flex-column shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-header-brand text-center pt-4 pb-3">
                            @if($subscription->brand_logo)
                                <img src="{{ asset( $subscription->brand_logo) }}" alt="{{ $subscription->brand_name }} Logo" class="brand-logo mb-3 mx-auto shadow-sm">
                            @else
                                <div class="brand-logo-placeholder mb-3 mx-auto shadow-sm">
                                    <i class="fas fa-store text-light"></i>
                                </div>
                            @endif
                            <h4 class="fw-bold text-white mb-1">{{ $subscription->brand_name }}</h4>
                            <p class="text-white-75 mb-0"><i class="fas fa-map-marker-alt me-1"></i> {{ $subscription->address ?? 'Alamat tidak tersedia' }}</p>
                        </div>
                        <div class="card-body-brand flex-grow-1 d-flex flex-column p-4">
                            <div class="balance-info mb-4 text-center">
                                <span class="fw-bold text-muted d-block mb-1">Saldo Anda:</span>
                                <p class="h3 fw-bold text-success mb-0">Rp {{ number_format($subscription->pivot->amount, 0, ',', '.') }}</p>
                            </div>

                            <div class="d-flex justify-content-center gap-2 mb-3">
                                <a href="{{ route('home.brand', $subscription->code) }}" class="btn btn-outline-primary flex-grow-1 rounded-pill btn-sm">
                                    <i class="fas fa-info-circle me-1"></i> Brand
                                </a>
                                <button type="button" class="btn btn-outline-secondary flex-grow-1 rounded-pill btn-sm" onclick="alert('Ini akan menampilkan transaksi untuk brand: {{ $subscription->brand_name }}')">
                                    <i class="fas fa-receipt me-1"></i> Transaksi
                                </button>
                            </div>

                            @if($isCodeActive)
                                {{-- Tombol untuk menampilkan kode yang aktif --}}
                                <button type="button" class="btn btn-success w-100 rounded-pill mt-auto view-code-btn animate__animated animate__fadeInUp"
                                    data-bs-toggle="modal" data-bs-target="#activeCodeModal"
                                    data-code="{{ $code }}"
                                    data-brand-name="{{ $subscription->brand_name }}"
                                    data-generated-at="{{ $codeGeneratedAt }}"
                                    data-owner-id="{{ $subscription->id }}">
                                    <i class="fas fa-qrcode me-1"></i> Lihat Kode Langganan Aktif
                                </button>
                            @else
                                {{-- Form untuk Generate Code Baru --}}
                                <form action="{{ route('home.member.generate-code') }}" method="POST" class="w-100 generate-code-form mt-auto">
                                    @csrf
                                    <input type="hidden" name="owner_id" value="{{ $subscription->id }}">
                                    <button type="submit" class="btn btn-info w-100 rounded-pill animate__animated animate__fadeInUp">
                                        <i class="fas fa-magic me-1"></i> Generate Kode Baru
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="row">
            <div class="col-12">
                <div class="dashboard-card empty-state-card text-center py-5 rounded-4 shadow-sm">
                    <i class="fas fa-box-open fa-4x text-muted mb-4"></i>
                    <h4 class="fw-bold text-color-light mb-3">Belum Ada Langganan Terverifikasi</h4>
                    <p class="text-muted mb-4">
                        Sepertinya Anda belum berlangganan ke brand manapun, atau langganan Anda masih dalam proses verifikasi.
                    </p>
                    @if($pendingSubscriptions->count() > 0)
                        <p class="text-muted mb-4">
                            Anda memiliki {{ $pendingSubscriptions->count() }} langganan yang sedang menunggu verifikasi.
                            <button type="button" class="btn btn-link p-0 align-baseline" data-bs-toggle="modal" data-bs-target="#pendingSubscriptionsModal">Lihat Langganan Pending</button>
                        </p>
                    @endif
                    <a href="{{ route('home.brands') }}" class="btn btn-primary btn-lg rounded-pill shadow-sm mt-3">
                        <i class="fas fa-plus-circle me-2"></i> Jelajahi Brands Sekarang!
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal untuk Langganan Pending --}}
    <div class="modal fade" id="pendingSubscriptionsModal" tabindex="-1" aria-labelledby="pendingSubscriptionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content dashboard-card rounded-4 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fs-4" id="pendingSubscriptionsModalLabel"><i class="fas fa-hourglass-half me-2 text-warning"></i> Langganan Pending Anda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-0">
                    @forelse($pendingSubscriptions as $pendingSub)
                        <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-3 border-bottom-0 card-hover-light rounded-3 mb-2 px-3">
                            <div>
                                <h6 class="mb-1 fw-bold text-primary">{{ $pendingSub->brand_name }}</h6>
                                <small class="text-muted">Diajukan pada: {{ $pendingSub->pivot->created_at->format('d M Y') }}</small>
                            </div>
                            <span class="badge bg-warning text-dark py-2 px-3 rounded-pill">Menunggu Verifikasi</span>
                        </div>
                    @empty
                        <div class="alert alert-success text-center mt-3 rounded-3 shadow-sm">
                            Tidak ada langganan yang sedang menunggu verifikasi.
                        </div>
                    @endforelse
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal untuk Menampilkan Kode yang Baru Digenerate --}}
    @if(session('success_code_generated'))
        <div class="modal fade" id="generatedCodeModal" tabindex="-1" aria-labelledby="generatedCodeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content dashboard-card rounded-4 shadow-lg">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title text-success fs-4" id="generatedCodeModalLabel"><i class="fas fa-check-circle me-2"></i> Kode Berhasil Digenerate!</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-0 text-center">
                        <p class="mb-3 text-muted">Gunakan kode ini untuk transaksi di <strong class="text-primary">{{ session('success_code_generated.brand_name') }}</strong>.</p>
                        <div class="generated-code-display mb-4 p-4 rounded-3 bg-light shadow-sm">
                            <span class="display-3 fw-bolder text-success">{{ session('success_code_generated.code') }}</span>
                        </div>
                        <small class="text-muted d-block mb-3">Kode digenerate pada: {{ \Carbon\Carbon::parse(session('success_code_generated.code_generated_at'))->format('d M Y, H:i:s') }} WIB</small>
                        <p class="text-muted small border-top pt-3 mt-3">
                            Catatan: Kode ini unik dan dapat digunakan sesuai kebijakan brand. Jika Anda membutuhkan kode baru, Anda bisa menggenerasinya lagi.
                            Menggenerasi kode baru akan **membatalkan** kode sebelumnya untuk brand ini.
                        </p>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-primary rounded-pill" data-bs-dismiss="modal">Oke, Saya Mengerti</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal untuk Menampilkan Kode yang Masih Aktif --}}
    <div class="modal fade" id="activeCodeModal" tabindex="-1" aria-labelledby="activeCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content dashboard-card rounded-4 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fs-4" id="activeCodeModalLabel"><i class="fas fa-qrcode me-2 text-primary"></i> Kode Langganan Anda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-0 text-center">
                    <p class="mb-3 text-muted">Ini adalah kode langganan aktif Anda untuk <strong class="text-primary" id="modalBrandName"></strong>.</p>
                    <div class="generated-code-display mb-4 p-4 rounded-3 bg-light shadow-sm">
                        <span class="display-3 fw-bolder text-primary" id="modalActiveCode"></span>
                    </div>
                    <small class="text-muted d-block mb-3">Kode digenerate pada: <span id="modalGeneratedAt"></span> WIB</small>
                    <p class="text-muted small border-top pt-3 mt-3">
                        Kode ini akan kadaluarsa setelah 24 jam atau setelah digunakan.
                    </p>
                </div>
                <div class="modal-footer border-top-0 pt-0 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Tutup</button>
                    <form action="{{ route('home.member.generate-code') }}" method="POST" class="generate-code-form m-0" id="requestNewCodeForm">
                        @csrf
                        <input type="hidden" name="owner_id" id="modalOwnerId">
                        <button type="submit" class="btn btn-info rounded-pill btn-sm">
                            <i class="fas fa-random me-1"></i> Request Kode Baru
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
    :root {
        --primary-color: #007bff;
        --secondary-color: #6c757d;
        --success-color: #28a745;
        --info-color: #17a2b8;
        --warning-color: #ffc107;
        --danger-color: #dc3545;

        --text-color: #343a40;
        --text-color-light: #495057;
        --muted-color: #6c757d;

        --bg-color: #f8f9fa;
        --panel-bg: #ffffff;
        --light-bg: #e9ecef;
        --border-color: #dee2e6;
    }

    body {
        background-color: var(--bg-color);
        color: var(--text-color);
    }

    .page-section-title {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--text-color-light);
    }

    .dashboard-card {
        background-color: var(--panel-bg);
        border-radius: 1rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        padding: 25px;
    }
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }

    .brand-card {
        background-color: var(--panel-bg);
        border-radius: 1.25rem;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 100%;
        transition: all 0.3s ease;
    }
    .brand-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
    }

    .card-header-brand {
        background-color: var(--primary-color); /* Warna solid */
        color: white;
        padding: 2.5rem 1.5rem 1.5rem;
        position: relative;
        overflow: hidden;
        border-bottom-left-radius: 1.25rem;
        border-bottom-right-radius: 1.25rem;
    }

    .brand-logo {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid rgba(255, 255, 255, 0.8);
        padding: 3px;
        background-color: var(--panel-bg);
        position: relative;
        z-index: 10;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }
    .brand-card:hover .brand-logo {
        transform: scale(1.05);
    }

    .brand-logo-placeholder {
        width: 100px;
        height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3.5rem;
        color: rgba(255, 255, 255, 0.7);
        border: 4px dashed rgba(255, 255, 255, 0.4);
        border-radius: 50%;
        background-color: rgba(0, 0, 0, 0.1);
        position: relative;
        z-index: 10;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .card-body-brand {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        justify-content: space-between;
    }

    .brand-card h4 {
        font-size: 1.6rem;
        margin-bottom: 0.25rem;
        color: white;
    }
    .brand-card p.text-muted {
        font-size: 0.95rem;
        color: var(--muted-color) !important;
    }
    .brand-card p.text-white-75 {
        font-size: 0.95rem;
    }

    .balance-info {
        background-color: var(--light-bg);
        padding: 15px 20px;
        border-radius: 0.75rem;
        width: 100%;
        margin-top: 1rem;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
    }
    .balance-info .h3 {
        font-size: 1.8rem;
        color: var(--success-color);
    }
    .balance-info span {
        font-size: 0.9rem;
        letter-spacing: 0.5px;
    }

    /* Buttons Styling */
    .btn {
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-outline-primary {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }
    .btn-outline-primary:hover {
        background-color: var(--primary-color);
        color: white;
    }
    .btn-outline-secondary {
        border-color: var(--secondary-color);
        color: var(--secondary-color);
    }
    .btn-outline-secondary:hover {
        background-color: var(--secondary-color);
        color: white;
    }
    .btn-success {
        background-color: var(--success-color);
        border: none;
        color: white;
        box-shadow: 0 4px 10px rgba(40, 167, 69, 0.25);
    }
    .btn-success:hover {
        opacity: 0.9;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(40, 167, 69, 0.35);
    }
    .btn-info {
        background-color: var(--info-color);
        border: none;
        color: white;
        box-shadow: 0 4px 10px rgba(23, 162, 184, 0.25);
    }
    .btn-info:hover {
        opacity: 0.9;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(23, 162, 184, 0.35);
    }
    .btn-warning {
        background-color: var(--warning-color);
        border-color: var(--warning-color);
        color: var(--text-color);
    }
    .btn-warning:hover {
        background-color: #e0a800;
        border-color: #e0a800;
        color: var(--text-color);
    }
    .btn-link {
        color: var(--primary-color);
        font-weight: 600;
    }
    .btn-link:hover {
        color: var(--primary-color);
        text-decoration: underline;
    }

    .empty-state-card {
        padding: 50px !important;
        border: 2px dashed var(--border-color);
        background-color: var(--light-bg);
        box-shadow: none;
        border-radius: 1rem;
    }
    .empty-state-card i {
        color: var(--muted-color);
        opacity: 0.6;
    }

    /* Modal Styling */
    .modal-content.dashboard-card {
        padding: 20px;
        background-color: var(--panel-bg);
        border-radius: 1.25rem;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
    }
    .modal-header .modal-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text-color-light);
    }
    .modal-body .list-group-item {
        background-color: var(--light-bg);
        border-radius: 0.75rem;
        margin-bottom: 0.75rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: all 0.2s ease;
    }
    .modal-body .list-group-item:hover {
        background-color: var(--border-color);
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }
    .modal-body .list-group-item:last-child {
        border-bottom: none !important;
    }
    .modal-body .list-group-item h6 {
        font-size: 1.15rem;
        color: var(--primary-color);
    }
    .modal-body .list-group-item small {
        font-size: 0.85rem;
        color: var(--muted-color);
    }

    #generatedCodeModal .modal-title {
        color: var(--success-color);
    }
    #activeCodeModal .modal-title {
        color: var(--primary-color);
    }
    .generated-code-display {
        background-color: var(--light-bg);
        padding: 2rem;
        border-radius: 1rem;
        border: 2px dashed var(--border-color);
        box-shadow: inset 0 1px 5px rgba(0,0,0,0.05);
    }
    .generated-code-display span {
        letter-spacing: 3px;
        font-family: 'Roboto Mono', monospace;
        text-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .modal-body small {
        font-size: 0.85rem;
    }
    .modal-body p.small {
        font-size: 0.8rem;
    }

    .animate__animated {
        animation-duration: 0.7s;
    }
</style>
@endpush

@push('scripts')
    {{-- Script untuk menampilkan modal hasil generate code --}}
    @if(session('success_code_generated'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var myModal = new bootstrap.Modal(document.getElementById('generatedCodeModal'));
                myModal.show();
            });
        </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Script untuk handle tombol "Lihat Kode Langganan"
            var activeCodeModal = new bootstrap.Modal(document.getElementById('activeCodeModal'));
            var modalActiveCode = document.getElementById('modalActiveCode');
            var modalBrandName = document.getElementById('modalBrandName');
            var modalGeneratedAt = document.getElementById('modalGeneratedAt');
            var modalOwnerIdInput = document.getElementById('modalOwnerId');

            document.querySelectorAll('.view-code-btn').forEach(button => {
                button.addEventListener('click', function() {
                    modalActiveCode.textContent = this.dataset.code;
                    modalBrandName.textContent = this.dataset.brandName;
                    modalGeneratedAt.textContent = this.dataset.generatedAt;
                    modalOwnerIdInput.value = this.dataset.ownerId;
                    activeCodeModal.show();
                });
            });

            // Script untuk konfirmasi sebelum submit form "Generate Kode Baru"
            document.querySelectorAll('.generate-code-form').forEach(form => {
                form.addEventListener('submit', function(event) {
                    if (!confirm('Apakah Anda yakin ingin menggenerasi kode baru? Kode sebelumnya (jika ada) akan dibatalkan.')) {
                        event.preventDefault();
                    }
                });
            });
        });
    </script>
@endpush
