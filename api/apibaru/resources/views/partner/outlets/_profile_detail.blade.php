@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: 400px; border-radius: 12px; border: 1px solid #dee2e6; }
        .image-hover:hover { opacity: 0.8; cursor: pointer; }
    </style>
@endpush

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-bottom p-4">
        <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-edit me-2"></i>Edit Profil Outlet</h5>
    </div>
    <div class="card-body p-4 p-md-5">
        <form action="{{ route('partner.outlets.update', $outlet->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-4">
                {{-- Kiri: Informasi Dasar --}}
                <div class="col-lg-6">
                    <div class="mb-4">
                        <label for="outlet_name" class="form-label fw-semibold">Nama Outlet</label>
                        <input type="text" class="form-control @error('outlet_name') is-invalid @enderror"
                               id="outlet_name" name="outlet_name" value="{{ old('outlet_name', $outlet->outlet_name) }}" required>
                        @error('outlet_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>


                    <div class="mb-4">
                        <label for="timezone" class="form-label fw-semibold">Zona Waktu</label>
                        <select class="form-select" id="timezone" name="timezone" required>
                            <option value="Asia/Jakarta" {{ old('timezone', $outlet->timezone) == 'Asia/Jakarta' ? 'selected' : '' }}>WIB (Jakarta)</option>
                            <option value="Asia/Makassar" {{ old('timezone', $outlet->timezone) == 'Asia/Makassar' ? 'selected' : '' }}>WITA (Makassar)</option>
                            <option value="Asia/Jayapura" {{ old('timezone', $outlet->timezone) == 'Asia/Jayapura' ? 'selected' : '' }}>WIT (Jayapura)</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Koordinat & Lokasi</label>
                        <div id="map" class="mb-2 shadow-sm"></div>
                        <button type="button" id="locateMeBtn" class="btn btn-sm btn-outline-primary rounded-pill">
                            <i class="fas fa-location-crosshairs me-1"></i> Gunakan Lokasi Saat Ini
                        </button>
                    </div>

                    <div class="mb-3">
                        <label for="city_name" class="form-label fw-semibold">Kota</label>
                        <input type="text" class="form-control bg-light" id="city_name" name="city_name"
                               value="{{ old('city_name', $outlet->city_name) }}" readonly>
                        <small class="text-muted">Terisi otomatis saat pin peta digeser.</small>
                    </div>

                    <div class="mb-0">
                        <label for="address" class="form-label fw-semibold">Alamat Lengkap</label>
                        <textarea class="form-control" id="address" name="address" rows="3">{{ old('address', $outlet->address) }}</textarea>
                    </div>
                </div>
            </div>

            <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', $outlet->latlong['latitude'] ?? '') }}">
            <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', $outlet->latlong['longitude'] ?? '') }}">

            <hr class="my-4 opacity-50">

            <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill shadow-sm">
                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const API_KEY = 'd47c008e75994b8caf625c3f7f83b907';

            // Inisialisasi Koordinat (Default Jakarta jika kosong)
            let lat = parseFloat(document.getElementById('latitude').value) || -6.2088;
            let lng = parseFloat(document.getElementById('longitude').value) || 106.8456;

            const map = L.map('map').setView([lat, lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            let marker = L.marker([lat, lng], { draggable: true }).addTo(map);

            async function updateLocationInfo(lat, lng) {
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;

                try {
                    const res = await fetch(`https://api.opencagedata.com/geocode/v1/json?q=${lat}+${lng}&key=${API_KEY}&language=id`);
                    const data = await res.json();
                    if(data.results.length > 0) {
                        const comp = data.results[0].components;
                        document.getElementById('city_name').value = comp.city || comp.county || comp.state || 'Tidak diketahui';
                    }
                } catch (e) { console.error("Geocoding error", e); }
            }

            marker.on('dragend', function() {
                const pos = marker.getLatLng();
                updateLocationInfo(pos.lat, pos.lng);
            });

            document.getElementById('locateMeBtn').addEventListener('click', function() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(pos => {
                        const newPos = [pos.coords.latitude, pos.coords.longitude];
                        map.setView(newPos, 17);
                        marker.setLatLng(newPos);
                        updateLocationInfo(pos.coords.latitude, pos.coords.longitude);
                    });
                }
            });


        });
    </script>
@endpush
