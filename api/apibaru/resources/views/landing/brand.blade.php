@extends('layouts.landingpage.app')

@section('title', $brand->brand_name . ' Detail')

@push('styles')
    {{-- Leaflet CSS dengan integrity yang benar --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    <style>
        .brand-header {
            padding: 5rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 1.5rem 1.5rem;
            color: #fff;
            text-align: center;

        }
        .brand-logo-detail {
            width: 150px;
            height: 150px;
            object-fit: contain;
            margin-bottom: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        #brand-map {
            height: 500px;
            width: 100%;
            border-radius: 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .section-title-hr {
            width: 80px;
            height: 4px;
            background-color: var(--primary-color);
            border: none;
            margin: 0.5rem auto 2.5rem;
            border-radius: 4px;
        }
    </style>
@endpush

@section('content')
    <section class="brand-header bubble-overlay">
        <div class="container">
            @if ($brand->brand_logo)
            <img src="{{ asset( $brand->brand_logo) }}" class="brand-logo-detail" alt="{{ $brand->brand_name }} Logo">
            @endif
            <h1 class="fw-bold">{{ $brand->brand_name }}</h1>
            {{-- <p class="text-muted">{{ $brand->description }}</p> --}}
        </div>
    </section>

    <section class="py-5 bg-light-bg">
        <div class="container">
            @include('landing._brand-member-button', ['brand' => $brand])

            <h2 class="text-center mt-5 mb-0">Lokasi Outlet</h2>
            <hr class="section-title-hr">
            <div id="brand-map"></div>

            <h2 class="text-center mt-5 mb-0">Daftar Outlet</h2>
            <hr class="section-title-hr">
            @include('landing._brand-outlets-list', ['outlets' => $brand->outlets->where('status', 1)])
        </div>
    </section>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Inisialisasi peta
            let map = L.map('brand-map').setView([0, 0], 2); // Set view default
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            const outlets = @json($brand->outlets->where('status', 1));
            let outletMarkers = [];

            // Tambahkan marker untuk setiap outlet
            outlets.forEach(outlet => {
                // Mengurai string JSON latlong
                try {
                    const latlong = JSON.parse(outlet.latlong);
                    if (latlong && latlong.lat && latlong.lon) {
                        const marker = L.marker([latlong.lat, latlong.lon])
                            .addTo(map)
                            .bindPopup(`<b>${outlet.outlet_name}</b><br>${outlet.address}`);
                        outletMarkers.push(marker);
                    }
                } catch (e) {
                    console.error('Error parsing latlong for outlet:', outlet.outlet_name, e);
                }
            });

            // Fungsi untuk mendapatkan lokasi pengguna dan memperbarui peta & daftar outlet
            function getUserLocationAndData() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        const userLat = position.coords.latitude;
                        const userLon = position.coords.longitude;

                        // Tambahkan marker lokasi pengguna
                        const userIcon = L.icon({
                            iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                            iconSize: [25, 41],
                            iconAnchor: [12, 41],
                            popupAnchor: [1, -34],
                            shadowSize: [41, 41]
                        });
                        L.marker([userLat, userLon], {icon: userIcon})
                         .addTo(map)
                         .bindPopup("Lokasi Anda Saat Ini").openPopup();

                        // FitBounds untuk mencakup semua marker
                        const allMarkers = [...outletMarkers, L.marker([userLat, userLon])];
                        if (allMarkers.length > 0) {
                             const group = new L.featureGroup(allMarkers);
                             map.fitBounds(group.getBounds().pad(0.5));
                        }

                        // Panggil AJAX untuk mendapatkan data jarak dan perbarui daftar outlet
                        updateOutletsList(userLat, userLon);

                    }, function(error) {
                        console.error('Geolocation error:', error);
                        // Jika gagal, set view peta ke bounds semua outlet saja
                        if (outletMarkers.length > 0) {
                            const group = new L.featureGroup(outletMarkers);
                            map.fitBounds(group.getBounds().pad(0.5));
                        }
                    });
                } else {
                    console.log('Geolocation tidak didukung oleh browser ini.');
                    if (outletMarkers.length > 0) {
                        const group = new L.featureGroup(outletMarkers);
                        map.fitBounds(group.getBounds().pad(0.5));
                    }
                }
            }

            function updateOutletsList(userLat, userLon) {
                $.ajax({
                    url: '{{ route('home.brand', $brand->code) }}',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        ajax: true,
                        lat: userLat,
                        lon: userLon
                    },
                   success: function(response) {
    // Iterasi hasil AJAX untuk mengubah warna kartu
    response.forEach(outlet => {
        const outletCard = $(`#outlet-card-${outlet.id}`);
        if (outletCard.length && outlet.distance < 20) {
            // Mengubah gaya CSS secara langsung jika jarak kurang dari 20km
            outletCard.css({
                'background-color': '#fffbe6', // Warna kuning muda
                'border-color': '#ffc107'      // Border kuning solid
            });
        }
    });
},
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', error);
                    }
                });
            }

            // Jalankan fungsi saat halaman dimuat
            getUserLocationAndData();
        });
    </script>
@endpush
