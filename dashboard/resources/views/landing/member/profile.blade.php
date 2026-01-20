@extends('layouts.landingpage.dashboard-app')

@section('title', 'Profil Saya')
@section('header_title', 'Kelola Profil Anda')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="dashboard-card">
                <h3 class="card-title-custom mb-4"><i class="fas fa-user-circle me-2"></i> Informasi Pribadi</h3>
                <form action="#" method="POST" id="profileUpdateForm" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    {{-- Image Profile Upload Section --}}
                    <div class="d-flex flex-column align-items-center mb-4">
                        <img src="{{ Auth::user()->avatar_url ?? asset('assets/img/default-user.png') }}" alt="User Avatar"
                            class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;"
                            id="avatar-preview">
                        <label for="avatar_url" class="btn btn-outline-primary">
                            <i class="fas fa-camera me-2"></i> Unggah Foto Profil
                            <input type="file" id="avatar_url" name="avatar_url" class="d-none" accept="image/*">
                        </label>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="name" name="name"
                            value="{{ $user->name ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Nomor Telepon</label>
                        <input type="tel" class="form-control" id="phone_number" name="phone_number"
                            value="{{ $member->phone_number ?? '' }}">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Alamat</label>
                        <textarea class="form-control" id="address" name="address" rows="3">{{ $member->address ?? '' }}</textarea>
                    </div>

                    {{-- Hidden fields for latitude and longitude --}}
                    <input type="hidden" id="latitude" name="latitude" value="{{ $member->latitude ?? '' }}">
                    <input type="hidden" id="longitude" name="longitude" value="{{ $member->longitude ?? '' }}">

                    <div class="mb-3">
                        <label class="form-label">Tentukan Lokasi Anda di Peta</label>
                        <div id="map" style="height: 400px; border-radius: 8px;"></div>
                        <small class="form-text text-muted mt-2">Geser penanda peta ke lokasi yang tepat jika
                            diperlukan. Data alamat tidak akan diubah secara otomatis.</small>
                        <button type="button" id="locateMeBtn" class="btn btn-sm btn-outline-secondary mt-2"><i
                                class="fas fa-location-arrow me-1"></i> Deteksi Lokasi Saya</button>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <div class="dashboard-card">
                <h3 class="card-title-custom mb-4"><i class="fas fa-lock me-2"></i> Ubah Kata Sandi</h3>
                <form action="#" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Kata Sandi Saat Ini</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Kata Sandi Baru</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" class="form-control" id="new_password_confirmation"
                            name="new_password_confirmation" required>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-warning btn-lg">Ubah Kata Sandi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        .dashboard-card {
            background-color: var(--panel-bg);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
        }

        .card-title-custom {
            color: var(--text-color-light);
            font-size: 1.6rem;
            margin-bottom: 25px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            padding-bottom: 15px;
        }

        .form-label {
            color: var(--text-color-muted-dark);
            font-size: 0.9rem;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-control,
        .form-select {
            background-color: var(--white-bg);
            border: 1px solid var(--border-color);
            color: var(--text-dark);
            border-radius: 8px;
            padding: 10px 15px;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: var(--white-bg);
            color: var(--text-dark);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(43, 108, 176, 0.25);
        }

        .form-text {
            font-size: 0.85rem;
        }

        .btn-lg {
            padding: 12px 25px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 8px;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let initialLat = {{ $member->latitude ?? -6.2088 }};
            let initialLng = {{ $member->longitude ?? 106.8456 }};
            let mapInitialized = false;
            let map;
            let marker;
            const addressInput = document.getElementById('address');
            const latitudeInput = document.getElementById('latitude');
            const longitudeInput = document.getElementById('longitude');
            const locateMeBtn = document.getElementById('locateMeBtn');

            // Image Preview Handler
            document.getElementById('avatar_url').addEventListener('change', function(event) {
                const [file] = event.target.files;
                if (file) {
                    document.getElementById('avatar-preview').src = URL.createObjectURL(file);
                }
            });

            // Function to initialize the map
            function initMap(lat, lng) {
                if (mapInitialized) {
                    map.setView([lat, lng], 16);
                    marker.setLatLng([lat, lng]);
                    return;
                }

                map = L.map('map').setView([lat, lng], 16);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                marker = L.marker([lat, lng], {
                    draggable: true
                }).addTo(map);

                marker.on('dragend', function(e) {
                    const latlng = marker.getLatLng();
                    latitudeInput.value = latlng.lat;
                    longitudeInput.value = latlng.lng;
                    // Reverse geocoding for address is intentionally commented out here
                    // to prevent it from overwriting the user's manual input.
                });

                mapInitialized = true;
            }

            // Function for forward geocoding (address to coordinates)
            async function forwardGeocode(address) {
                if (!address.trim()) {
                    return;
                }

                try {
                    const response = await fetch(
                        `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`
                    );
                    const data = await response.json();
                    if (data && data.length > 0) {
                        const result = data[0];
                        const newLat = parseFloat(result.lat);
                        const newLng = parseFloat(result.lon);

                        marker.setLatLng([newLat, newLng]);
                        map.setView([newLat, newLng], 16);

                        latitudeInput.value = newLat;
                        longitudeInput.value = newLng;
                    } else {
                        console.warn('Alamat tidak ditemukan oleh geocoding:', address);
                    }
                } catch (error) {
                    console.error('Error during forward geocoding:', error);
                }
            }

            // Event listener for the "Deteksi Lokasi Saya" button
            locateMeBtn.addEventListener('click', function() {
                if (navigator.geolocation) {
                    locateMeBtn.textContent = 'Mendeteksi...';
                    locateMeBtn.disabled = true;
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            const userLat = position.coords.latitude;
                            const userLng = position.coords.longitude;
                            initMap(userLat, userLng);
                            latitudeInput.value = userLat;
                            longitudeInput.value = userLng;
                            // **We intentionally do NOT call reverseGeocode here**
                            // to keep the address input field unchanged.
                            locateMeBtn.textContent = 'Deteksi Lokasi Saya';
                            locateMeBtn.disabled = false;
                        },
                        function(error) {
                            console.error('Failed to get location:', error);
                            alert(
                                'Gagal mendeteksi lokasi Anda. Pastikan Anda telah mengizinkan akses lokasi.');
                            locateMeBtn.textContent = 'Deteksi Lokasi Saya';
                            locateMeBtn.disabled = false;
                        }, {
                            enableHighAccuracy: true,
                            timeout: 5000,
                            maximumAge: 0
                        }
                    );
                } else {
                    alert('Browser Anda tidak mendukung deteksi lokasi.');
                }
            });

            // Initial Map Load Logic
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const userLat = position.coords.latitude;
                        const userLng = position.coords.longitude;
                        initMap(userLat, userLng);
                    },
                    function(error) {
                        console.warn(`Geolocation error (${error.code}): ${error.message}`);
                        initMap(initialLat, initialLng);
                    }, {
                        enableHighAccuracy: true,
                        timeout: 5000,
                        maximumAge: 0
                    }
                );
            } else {
                initMap(initialLat, initialLng);
            }

            // Event listener for address input blur to perform forward geocoding
            addressInput.addEventListener('blur', function() {
                forwardGeocode(addressInput.value);
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = '{{ session('success') }}';
            const errorMessage = '{{ session('error') }}';

            if (successMessage) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: successMessage,
                    showConfirmButton: false,
                    timer: 3000
                });
            }

            if (errorMessage) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: errorMessage,
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        });
    </script>
    {{-- Script lainnya (map, dll) --}}
    {{-- ... --}}
@endpush
