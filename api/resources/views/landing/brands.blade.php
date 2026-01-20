@extends('layouts.landingpage.app')

@section('title', 'Daftar Brand')

@push('styles')
    <style>
        /* CSS untuk Banner */
        .brands-hero {
            padding: 5rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 1.5rem 1.5rem;
            color: #fff;
            text-align: center;
        }

        .brands-hero h1 {
            font-size: 2.5rem;
            font-weight: 700;
        }

        .brands-hero p {
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Tampilan dasar kartu brand */
        .brand-card {
            border: 1px solid #e0e0e0;
            background-color: #fff; /* Default warna putih */
            border-radius: 1.5rem;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            height: 100%;
            position: relative; /* Penting untuk posisi tombol */
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .brand-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(33, 150, 243, 0.1);
        }

        /* Styling untuk kartu brand yang terdekat */
        .brand-card.is-nearby {
            background-color: #fffbe6; /* Warna kuning muda */
            border-color: #ffc107; /* Border kuning solid */
        }

        .brand-logo-container {
            height: 100px;
            width: 100px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-logo {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        /* Tombol kecil di pojok kanan bawah */
        .btn-card-action {
            position: absolute;
            bottom: 1rem;
            right: 1rem;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary-color);
            color: #fff;
            border-radius: 50%;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .btn-card-action:hover {
            background-color: #0056b3;
            transform: scale(1.1);
        }

        .btn-card-action i {
            font-size: 1.2rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #888;
        }
    </style>
@endpush

@section('content')
    {{-- Banner Section --}}
    <section class="brands-hero bubble-overlay">
        <div class="container">
            <h1>Jelajahi Brand Laundry Favorit Anda</h1>
            <p>Temukan brand laundry dengan outlet terdekat di sekitar Anda.</p>
        </div>
    </section>

    <section class="py-5 bg-light-bg">
        <div class="container">
            <div class="row g-4">
                @foreach($brands as $brand)
                    <div class="col-lg-3 col-md-4 col-sm-6" id="brand-card-{{ $brand->id }}">
                        <div class="brand-card">
                            <a href="{{ route('home.outlets', ['brand_id' => $brand->id]) }}" class="text-decoration-none d-flex flex-column align-items-center w-100">
                                <div class="brand-logo-container">
                                    <img src="{{ $brand->brand_logo ? asset( path: $brand->brand_logo) : asset('assets/img/default-img.png') }}" class="brand-logo" alt="{{ $brand->brand_name }} Logo">
                                </div>
                                <h5 class="fw-bold text-dark mb-1">{{ $brand->brand_name }}</h5>
                                <p class="text-muted mb-0">{{ $brand->outlets->where('status', 1)->count() }} Outlet</p>
                            </a>
                            {{-- Tombol kecil di pojok kanan bawah --}}
                            <a href="{{ route('home.brand', $brand->code) }}" class="btn-card-action">
                                <i class="ri-arrow-right-line"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($brands->count() === 0)
                <div class="empty-state">
                    <i class="ri-search-2-line display-4 mb-3"></i>
                    <h4 class="fw-bold">Tidak ada brand yang tersedia.</h4>
                </div>
            @endif
        </div>
    </section>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Fungsi untuk mengambil lokasi pengguna
        function getUserLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const userLat = position.coords.latitude;
                    const userLon = position.coords.longitude;
                    fetchClosestOutlets(userLat, userLon);
                }, function(error) {
                    console.error('Geolocation error:', error);
                    // Tidak melakukan apa-apa jika gagal, kartu akan tetap standar
                });
            }
        }

        // Fungsi untuk memperbarui kartu brand dengan info terdekat
        function fetchClosestOutlets(userLat, userLon) {
            $.ajax({
                url: '{{ route('home.brands') }}',
                type: 'GET',
                dataType: 'json',
                data: {
                    ajax: true,
                    lat: userLat,
                    lon: userLon
                },
                success: function(response) {
                    response.forEach(function(brand) {
                        // Cek apakah jarak terdekat kurang dari 20 km
                        if (brand.closest_distance < 20) {
                            const brandCardContainer = $(`#brand-card-${brand.brand_id} .brand-card`);
                            if (brandCardContainer.length) {
                                // Tambahkan class 'is-nearby' untuk mengubah tampilan kartu
                                brandCardContainer.addClass('is-nearby');
                            }
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                }
            });
        }

        // Jalankan fungsi untuk mendapatkan lokasi saat halaman dimuat
        getUserLocation();
    });
</script>
@endpush
