<div class="modal fade" id="outletModal{{ $outlet->id }}" tabindex="-1"
    aria-labelledby="outletModalLabel{{ $outlet->id }}" data-code="{{ $outlet->code }}">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header rounded-top-3 border-bottom-0"
                style="background-color: var(--brand-primary-color); color: var(--brand-primary-text-color);">
                <h5 class="modal-title fw-bold" id="outletModalLabel{{ $outlet->id }}">
                    <i class="fas fa-store me-2"></i> {{ $outlet->outlet_name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                    style="filter: invert(1) grayscale(100%) brightness(200%);"></button>
            </div>

            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm rounded-3">
                            <div class="card-body">
                                <h6 class="card-title mb-3" style="color: var(--brand-primary-color);"><i
                                        class="fas fa-info-circle me-2"></i> Informasi Dasar</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2"><strong><i class="fas fa-map-marker-alt me-2 text-muted"></i>
                                            Alamat:</strong> {{ $outlet->address }}</li>
                                    @if ($outlet->city_name)
                                        <li class="mb-2"><strong><i class="fas fa-city me-2 text-muted"></i>
                                                Kota:</strong> {{ $outlet->city_name }}</li>
                                    @endif
                                    {{-- Menambahkan info WhatsApp jika nomor HP tersedia --}}
                                    @if ($outlet->phone_number)
                                        <li class="mb-2"><strong><i class="fab fa-whatsapp me-2 text-muted"></i>
                                                WhatsApp:</strong>
                                            <a href="https://wa.me/{{ $outlet->phone_number }}" target="_blank"
                                                class="text-decoration-none">{{ $outlet->phone_number }}</a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm rounded-3">
                            <div class="card-body">
                                <h6 class="card-title mb-3" style="color: var(--brand-primary-color);"><i
                                        class="fas fa-clock me-2"></i> Jam Operasional</h6>
                                @if ($outlet->operational_hours)
                                    @php
                                        // Decode JSON menjadi array objek
                                        $operationalHours = $outlet->operational_hours;

                                        // Ambil nama hari ini dalam bahasa Inggris dan lowercase
                                        $currentDay = strtolower(
                                            \Carbon\Carbon::now($outlet->timezone ?? 'Asia/Jakarta')->format('l'),
                                        );

                                        // Buat mapping untuk terjemahan hari
                                        $dayMapping = [
                                            'monday' => 'Senin',
                                            'tuesday' => 'Selasa',
                                            'wednesday' => 'Rabu',
                                            'thursday' => 'Kamis',
                                            'friday' => 'Jumat',
                                            'saturday' => 'Sabtu',
                                            'sunday' => 'Minggu',
                                        ];

                                        // Ambil hari ini dalam bahasa yang sesuai
                                        $currentDayTranslated = $dayMapping[$currentDay] ?? $currentDay;
                                    @endphp
                                    <ul class="list-unstyled mb-3">
                                        @foreach ($operationalHours as $dayData)
                                            @php
                                                $dayName = $dayData['day'];
                                                $isCurrentDay = $dayName == $currentDayTranslated;
                                                $className = $isCurrentDay ? 'fw-bold ' : 'text-muted';
                                            @endphp
                                            <li class="{{ $className }}">
                                                {{ $dayName }}:
                                                @if ($dayData['is_closed'])
                                                    <span class="text-danger">Tutup</span>
                                                @else
                                                    @if ($dayData['open'] && $dayData['close'])
                                                        {{ \Carbon\Carbon::parse($dayData['open'])->format('H:i') }} -
                                                        {{ \Carbon\Carbon::parse($dayData['close'])->format('H:i') }}
                                                    @else
                                                        -
                                                    @endif
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-muted">Jam operasional tidak tersedia.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if ($outlet->services && $outlet->services->count() > 0)
                    <h6 class="mt-4 mb-3 fw-bold" style="color: var(--brand-primary-color);"><i
                            class="fas fa-concierge-bell me-2"></i> Layanan Tersedia</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($outlet->services as $service)
                            <span class="badge py-2 px-3 rounded-pill"
                                style="background-color: var(--brand-secondary-color); color: var(--brand-secondary-text-color);">
                                {{ $service->name }}
                            </span>
                        @endforeach
                    </div>
                @endif

                <h6 class="mt-4 mb-3 fw-bold" style="color: var(--brand-primary-color);"><i
                        class="fas fa-map-marked-alt me-2"></i> Lokasi Peta</h6>
                <div id="outletMap{{ $outlet->id }}" class="map-leaflet border rounded-3 overflow-hidden shadow-sm"
                    style="height: 400px;"></div>

                {{-- Menambahkan kondisi untuk menampilkan bagian ini hanya jika ada latlong atau nomor HP --}}
                @if ($outlet->latlong || $outlet->phone_number)
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        {{-- Tautan Google Maps (hanya jika latlong tersedia) --}}
                        @if ($outlet->latlong && isset($outlet->latlong['latitude']) && isset($outlet->latlong['longitude']))
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $outlet->latlong['latitude'] }},{{ $outlet->latlong['longitude'] }}"
                                target="_blank" class="btn btn-transparent-icon"
                                style="color: var(--brand-primary-color);">
                                <i class="fas fa-map-marked-alt fa-2x"></i>
                                <span class="d-block mt-2 small">Google Maps</span>
                            </a>
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $outlet->latlong['latitude'] }},{{ $outlet->latlong['longitude'] }}"
                                target="_blank" class="btn btn-transparent-icon"
                                style="color: var(--brand-primary-color);">
                                <i class="fas fa-route fa-2x"></i>
                                <span class="d-block mt-2 small">Dapatkan Rute</span>
                            </a>
                        @endif

                        {{-- Tombol WhatsApp (hanya jika nomor HP tersedia) --}}
                        @if ($outlet->phone_number)
                            @php
                                // Menghilangkan semua karakter non-angka
                                $phoneNumber = preg_replace('/[^0-9]/', '', $outlet->phone_number);
                                // Memastikan dimulai dengan '62' dan bukan '0'
                                if (substr($phoneNumber, 0, 1) === '0') {
                                    $phoneNumber = '62' . substr($phoneNumber, 1);
                                }
                            @endphp
                            <a href="https://wa.me/{{ $phoneNumber }}" target="_blank"
                                class="btn btn-transparent-icon">
                                <i class="fab fa-whatsapp fa-2x"></i>
                                <span class="d-block mt-2 small">Hubungi Kami</span>
                            </a>
                        @endif
                    </div>
                @else
                    <div class="alert alert-info mt-4 text-center" role="alert">
                        Tidak ada tautan Google Maps atau WhatsApp yang tersedia.
                    </div>
                @endif
            </div>

            <div class="modal-footer border-top-0 d-flex justify-content-end">
                <button type="button" class="btn btn-secondary rounded-pill px-4"
                    data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modalId = 'outletModal{{ $outlet->id }}';
        var mapId = 'outletMap{{ $outlet->id }}';
        var outletLat = {{ $outlet->latlong['latitude'] ?? 'null' }};
        var outletLng = {{ $outlet->latlong['longitude'] ?? 'null' }};
        var outletModal = document.getElementById(modalId);
        var mapInstance = null; // Ubah menjadi null untuk referensi peta

        outletModal.addEventListener('shown.bs.modal', function() {
            if (outletLat !== null && outletLng !== null) {
                // Hancurkan instance peta yang lama jika ada
                if (mapInstance !== null) {
                    mapInstance.remove();
                }

                // Buat instance peta yang baru
                mapInstance = L.map(mapId).setView([outletLat, outletLng], 15);

                L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(mapInstance);

                L.marker([outletLat, outletLng]).addTo(mapInstance)
                    .bindPopup('<b>{{ $outlet->outlet_name }}</b><br>{{ $outlet->address }}')
                    .openPopup();

                // Memastikan peta di-render ulang
                setTimeout(function() {
                    mapInstance.invalidateSize();
                }, 100);
            }
        });

        // Menghapus instance peta saat modal ditutup untuk mencegah masalah
        outletModal.addEventListener('hidden.bs.modal', function() {
            if (mapInstance !== null) {
                mapInstance.remove();
                mapInstance = null;
            }
        });
    });
</script>

<style>
    /*
     * =============================================
     * Custom CSS untuk Modal Detail Outlet
     * =============================================
     */

    .modal-content {
        background-color: var(--card-background);
    }

    .modal-header .btn-close {
        /* Memastikan tombol tutup kontras dengan header */
        filter: invert(1) grayscale(100%) brightness(200%);
    }

    .card {
        border-radius: 0.75rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s ease-in-out;
    }

    .card:hover {
        transform: translateY(-2px);
    }

    .map-leaflet {
        border-radius: 0.75rem;
    }

    .rounded-pill {
        border-radius: 50rem !important;
    }

    .btn-transparent-icon {
        background-color: transparent;
        border: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-transparent-icon:hover {
        transform: translateY(-5px);
        opacity: 0.8;
    }

    .btn-transparent-icon i {
        font-size: 2.5rem;
        color: var(--brand-primary-color);
        transition: color 0.3s ease;
    }

    .btn-transparent-icon span {
        font-weight: 600;
        margin-top: 8px;
    }

    .leaflet-container {
        background: #fff;
    }
</style>
