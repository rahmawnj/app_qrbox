@extends('layouts.landingpage.app')

@section('title', 'Daftar Outlet')

@push('styles')
    <style>
        .hero-section {
            position: relative;
            min-height: 350px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-align: center;
            border-bottom-left-radius: 4rem;
            border-bottom-right-radius: 4rem;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 3rem;
            padding-top: 5rem;
        }

        .hero-section::after {
            content: '';
            position: absolute;
            bottom: -30px;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            height: 30px;
            background: rgba(0, 0, 0, 0.1);
            filter: blur(15px);
            border-radius: 50%;
            z-index: -1;
        }

        .hero-content {
            z-index: 10;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            font-weight: 400;
            opacity: 0.9;
        }

        /* --- CSS yang sudah ada, ditambahkan di sini --- */
        .filter-section {
            background-color: #fff;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .filter-section.sticky-top {
            top: 90px;
            z-index: 100;
        }
        .card-outlet-horizontal {
            /* display: flex;
            flex-direction: row;
            align-items: center;
            border: 1px solid #e0e0e0;
            border-radius: 1.5rem;
            margin-bottom: 1.5rem;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            cursor: pointer; */
        }
        .card-outlet-horizontal:hover {
            /* transf: translateY(-5px);
            box-shadow: 0 orm8px 24px rgba(33, 150, 243, 0.1); */
        }
        /* .card-outlet-horizontal .outlet-image {
            width: 250px;
            height: 250px;
            object-fit: cover;
        } */
        .card-outlet-horizontal .outlet-info {
            /* flex-grow: 1;
            paddin
            g: 1.5rem; */
        }
        .outlet-brand-info {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        .outlet-distance {
            font-size: 0.9rem;
            color: #777;
        }
        .btn-cari-lokasi {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .form-label {
            font-weight: 600;
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #888;
        }
    </style>
@endpush

@section('content')
    <section class="hero-section bubble-overlay">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Temukan Laundry Terbaik di Sekitar Anda</h1>
                <p class="hero-subtitle">Kami bantu temukan outlet laundry terdekat dengan kualitas terbaik.</p>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light-bg">
        <div class="container">
            <div class="row">
                {{-- Sidebar Filter --}}
                <div class="col-lg-4 mb-4">
                    <div class="filter-section sticky-top">
                        <h4 class="fw-bold mb-4">Filter Pencarian</h4>
                        <form id="filter-form">
                            <div class="mb-3">
                                <label for="search_query" class="form-label">Cari Nama Outlet / Brand</label>
                                <input type="text" class="form-control" id="search_query" name="search_query" placeholder="Contoh: 'Clean Express' atau 'Laundry Mawar'">
                            </div>
                            <div class="mb-3">
                                <label for="location_search" class="form-label">Cari Berdasarkan Lokasi</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="location_search" placeholder="Masukkan alamat atau kota...">
                                    <button class="btn btn-primary" type="button" id="search-location-btn">
                                        <i class="ri-search-line"></i>
                                    </button>
                                </div>
                                <div id="location-help" class="form-text">Atau gunakan lokasi saat ini.</div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-warning btn-cari-lokasi" id="find-location-btn">
                                    <i class="ri-map-pin-line me-2"></i>Gunakan Lokasi Saat Ini
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-filter-2-line me-2"></i>Terapkan Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- List Outlet --}}
                <div class="col-lg-8">
                    <div id="outlets-list-container">
                        {{-- Konten outlet akan dimuat di sini --}}
                    </div>
                    <div class="d-flex justify-content-center mt-4">
                        <button class="btn btn-outline-primary" id="load-more-btn" style="display:none;">Muat Lebih Banyak</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        const outletsListContainer = $('#outlets-list-container');
        const filterForm = $('#filter-form');
        const findLocationBtn = $('#find-location-btn');
        const loadMoreBtn = $('#load-more-btn');
        let currentPage = 1;
        let lastPage = 1;
        let userLat = null;
        let userLon = null;

        // Fungsi untuk menampilkan loader
        function showLoader() {
            outletsListContainer.html('<div class="d-flex justify-content-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        }

        // Fungsi untuk memuat outlet
        function loadOutlets(page = 1) {
            showLoader();

            const searchQuery = $('#search_query').val();
            const locationSearch = $('#location_search').val();

            const data = {
                page: page,
                search_query: searchQuery,
                location_search: locationSearch,
                lat: userLat,
                lon: userLon
            };

            $.ajax({
                url: '{{ route('home.outlets') }}',
                type: 'GET',
                data: data,
                success: function(response) {
                    console.log(response)
                    if (page === 1) {
                        outletsListContainer.empty();
                    }
                    outletsListContainer.append(response.html);

                    currentPage = response.current_page;
                    lastPage = response.last_page;

                    if (currentPage < lastPage) {
                        loadMoreBtn.show();
                    } else {
                        loadMoreBtn.hide();
                    }

                    if (response.total === 0) {
                        outletsListContainer.html('<div class="empty-state">Tidak ada outlet yang ditemukan dengan kriteria tersebut.</div>');
                    }
                },
                error: function(xhr, status, error) {
                    outletsListContainer.html('<div class="alert alert-danger" role="alert">Gagal memuat data outlet. Silakan coba lagi.</div>');
                }
            });
        }

        // Event listener untuk tombol "Gunakan Lokasi Saat Ini"
        findLocationBtn.on('click', function(e) {
            e.preventDefault();
            if (navigator.geolocation) {
                findLocationBtn.html('<i class="ri-loader-4-line me-2 spin"></i>Mencari lokasi...');
                findLocationBtn.prop('disabled', true);
                navigator.geolocation.getCurrentPosition(function(position) {
                    userLat = position.coords.latitude;
                    userLon = position.coords.longitude;
                    $('#location_search').val('Lokasi Anda Saat Ini');
                    loadOutlets();
                    findLocationBtn.html('<i class="ri-map-pin-line me-2"></i>Gunakan Lokasi Saat Ini');
                    findLocationBtn.prop('disabled', false);
                }, function(error) {
                    console.error('Geolocation error:', error);
                    alert('Gagal mendapatkan lokasi. Silakan masukkan lokasi secara manual.');
                    findLocationBtn.html('<i class="ri-map-pin-line me-2"></i>Gunakan Lokasi Saat Ini');
                    findLocationBtn.prop('disabled', false);
                    userLat = null;
                    userLon = null;
                });
            } else {
                alert('Geolocation tidak didukung di browser ini.');
            }
        });

        // Event listener untuk form filter
        filterForm.on('submit', function(e) {
            e.preventDefault();
            userLat = null; // Reset lat & lon jika menggunakan input teks
            userLon = null;
            loadOutlets();
        });

        // Event listener untuk tombol "Muat Lebih Banyak"
        loadMoreBtn.on('click', function(e) {
            e.preventDefault();
            if (currentPage < lastPage) {
                loadOutlets(currentPage + 1);
            }
        });

        // Muat outlet default saat halaman pertama kali dimuat
        loadOutlets();
    });
</script>
@endpush
