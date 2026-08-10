@extends('layouts.landingpage.app')

@section('title', 'Home')

@push('styles')
    <style>
        /* CSS yang sudah ada, tidak perlu diubah */
        .hero-section {
            color: #fff;
            min-height: 70vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.2);
        }

        .hero-subtitle {
            font-size: 1.25rem;
            max-width: 600px;
            margin: 1rem auto;
            opacity: 0.9;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 2rem;
            position: relative;
        }

        .section-title::after {
            content: '';
            width: 80px;
            height: 4px;
            background-color: var(--secondary-color);
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }

        /* CSS untuk kartu outlet yang diperbarui */
        .card-outlet {
            border: none;
            border-radius: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            position: relative;
            transition: all 0.3s ease;
            background-color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .card-outlet:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 32px rgba(33, 150, 243, 0.2);
        }

        .card-outlet .card-img-top {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .card-outlet .outlet-brand-info {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #fff;
            padding: 8px 16px;
            border-radius: 16px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--primary-color);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }

        .card-outlet .card-body {
            padding: 1rem;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            position: relative;
            flex-grow: 1;
        }

        .card-outlet .card-body .btn-icon-detail {
            background-color: var(--primary-color);
            color: #fff;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(33, 150, 243, 0.3);
            transition: all 0.3s ease;
            margin-left: auto;
            margin-top: 1rem;
        }

        .card-outlet .btn-icon-detail:hover {
            transform: scale(1.1);
            background-color: var(--secondary-color);
            box-shadow: 0 6px 15px rgba(255, 193, 7, 0.4);
        }

        .card-outlet .btn-icon-detail i {
            font-size: 1.5rem;
        }

        .outlet-info-text {
            color: var(--text-color-dark);
        }

        .card-outlet .card-body .outlet-info-text {
            text-align: left;
        }
        .card-outlet .card-body .outlet-info-text h5,
        .card-outlet .card-body .outlet-info-text p {
            margin-bottom: 0.25rem;
        }

        .brand-card img {
            height: 120px;
            object-fit: contain;
            transition: all 0.3s ease;
        }

        .brand-card:hover img {
            transform: scale(1.05);
        }
    </style>
@endpush

@section('content')
    {{-- Hero Section (Bubble Overlay) --}}
    <section class="hero-section bubble-overlay">
        <div class="container position-relative z-1">
            <h1 class="hero-title">Temukan Layanan Laundry Terbaik</h1>
            <p class="hero-subtitle">Dari brand terkemuka hingga outlet terdekat, semua ada dalam satu aplikasi.</p>
            <a href="#" id="find-location-btn" class="btn btn-warning btn-lg mt-4 px-5 py-3 fw-bold rounded-pill shadow-sm">
                <i class="ri-search-2-line me-2"></i>Cari Outlet Terdekat
            </a>
        </div>
    </section>

    {{-- Section Keunggulan (Card Modern) --}}
    <section class="py-5 bg-white">
        <div class="container text-center">
            <h2 class="section-title">Mengapa Memilih Kami?</h2>
            <div class="row g-4 mt-5">
                <div class="col-md-4">
                    <div class="card-modern p-4 text-center h-100">
                        <i class="ri-truck-line text-primary-custom display-4 mb-3"></i>
                        <h5 class="fw-bold">Pickup & Delivery</h5>
                        <p class="text-muted">Layanan antar-jemput yang cepat dan tepat waktu.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-modern p-4 text-center h-100">
                        <i class="ri-sparkling-2-line text-primary-custom display-4 mb-3"></i>
                        <h5 class="fw-bold">Kualitas Premium</h5>
                        <p class="text-muted">Pakaian bersih, wangi, dan terawat dengan standar terbaik.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-modern p-4 text-center h-100">
                        <i class="ri-wallet-2-line text-primary-custom display-4 mb-3"></i>
                        <h5 class="fw-bold">Harga Transparan</h5>
                        <p class="text-muted">Tidak ada biaya tersembunyi, harga bersaing dan terjangkau.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Section Brand (Slider Carousel) --}}
    <section class="py-5">
        <div class="container text-center">
            <h2 class="section-title">Brand Kami</h2>
            <div id="brandCarousel" class="carousel slide carousel-dark mt-5" data-bs-ride="carousel" data-bs-interval="2000">
                <div class="carousel-inner">
                    @php $brandChunks = $brands->chunk(3); @endphp
                    @foreach ($brandChunks as $key => $chunk)
                        <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                            <div class="row g-4 justify-content-center">
                                @foreach ($chunk as $brand)
                                    <div class="col-4 d-flex justify-content-center align-items-center">
                                        <div class="brand-card card-modern p-3">
                                            <img src="{{ asset( $brand->brand_logo) }}" alt="{{ $brand->brand_name }}" class="img-fluid">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#brandCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#brandCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    </section>

    {{-- Section Outlet Terdekat --}}
    <section class="py-5 bg-light-bg">
        <div class="container text-center">
            <h2 class="section-title">Outlet Terdekat</h2>
            <div id="outlets-container" class="mt-5">
                {{-- Konten outlet akan dimuat di sini --}}
            </div>
            <a href="{{ route('home.outlets') }}" class="btn btn-primary btn-lg mt-5 rounded-pill px-5"><i class="ri-store-line me-2"></i>Lihat Semua Outlet</a>
        </div>
    </section>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        const outletsContainer = $('#outlets-container');
        const findLocationBtn = $('#find-location-btn');

        // Fungsi untuk menampilkan loader
        function showLoader() {
            outletsContainer.html('<div class="d-flex justify-content-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        }

        // Fungsi untuk memuat outlet
        function loadOutlets(lat = null, lon = null) {
            showLoader();
            let data = {};
            if (lat && lon) {
                data = { lat: lat, lon: lon };
            }

            $.ajax({
                url: '{{ route("home") }}',
                type: 'GET',
                data: data,
                success: function(response) {
                    outletsContainer.html(response);
                },
                error: function(xhr, status, error) {
                    outletsContainer.html('<div class="alert alert-danger" role="alert">Gagal memuat data outlet. Silakan coba lagi atau berikan izin lokasi.</div>');
                }
            });
        }

        // Event listener untuk tombol
        findLocationBtn.on('click', function(e) {
            e.preventDefault();
            if (navigator.geolocation) {
                findLocationBtn.text('Mencari lokasi...');
                findLocationBtn.prop('disabled', true);
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    loadOutlets(lat, lon);
                    findLocationBtn.text('Cari Outlet Terdekat');
                    findLocationBtn.prop('disabled', false);
                }, function(error) {
                    console.error('Geolocation error:', error);
                    alert('Gagal mendapatkan lokasi. Memuat outlet secara acak.');
                    loadOutlets(); // Memuat outlet acak jika gagal
                    findLocationBtn.text('Cari Outlet Terdekat');
                    findLocationBtn.prop('disabled', false);
                });
            } else {
                alert('Geolocation tidak didukung di browser ini. Memuat outlet secara acak.');
                loadOutlets(); // Memuat outlet acak jika geolocation tidak didukung
            }
        });

        // Muat outlet secara acak saat halaman pertama kali dimuat
        loadOutlets();
    });
</script>
@endpush
