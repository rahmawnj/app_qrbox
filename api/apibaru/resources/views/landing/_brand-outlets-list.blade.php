<div class="row g-4">
    @foreach($outlets as $outlet)
@php
    $latlong = json_decode($outlet->latlong);
@endphp
        <div class="col-lg-4 col-md-6">
          <div class="card h-100 outlet-card" id="outlet-card-{{ $outlet->id }}" data-latlong='{"lat": {{ $latlong->lat }}, "lon": {{ $latlong->lon }}}' data-code="{{ $outlet->code }}">
                <div class="outlet-image-container">
                    <img src="{{ $outlet->image ? asset( $outlet->image) : 'https://placehold.co/600x400/E0E0E0/888888?text=Image+Not+Found' }}"
                         class="card-img-top"
                         alt="{{ $outlet->outlet_name }}">
                </div>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title fw-bold">{{ $outlet->outlet_name }}</h5>
                    <p class="card-text text-muted mb-2"><i class="ri-map-pin-line me-1"></i>{{ $outlet->city_name ?? 'Kota Tidak Tersedia' }}</p>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <button type="button" class="btn btn-detail-icon" data-bs-toggle="modal" data-bs-target="#outletDetailModal{{ $outlet->id }}">
                        <i class="ri-arrow-right-line"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="modal fade" id="outletDetailModal{{ $outlet->id }}" tabindex="-1" aria-labelledby="outletDetailModalLabel{{ $outlet->id }}" aria-hidden="true">
            <div class="modal-dialog modal-xl **modal-fullscreen-md-down modal-dialog-centered modal-dialog-scrollable**">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="outletDetailModalLabel{{ $outlet->id }}">{{ $outlet->outlet_name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <img src="{{ $outlet->image ? asset($outlet->image) : 'https://placehold.co/600x400/E0E0E0/888888?text=Image+Not+Found' }}" class="img-fluid rounded" alt="{{ $outlet->outlet_name }}">
                                <div id="map-{{ $outlet->id }}" class="outlet-map mt-3 rounded"></div>
                            </div>
                            <div class="col-lg-6">
                                <h5><i class="ri-map-pin-line me-2 text-primary"></i>Alamat</h5>
                                <p>{{ $outlet->address }}</p>
                                <h5 class="mt-4"><i class="ri-phone-line me-2 text-primary"></i>Nomor Telepon</h5>
                                <p>{{ $outlet->phone_number ?? 'Tidak tersedia' }}</p>
                                <h5 class="mt-4"><i class="ri-time-line me-2 text-primary"></i>Jam Operasional</h5>
                                <ul class="list-unstyled">
@foreach(($outlet->operational_hours ?? []) as $hours)
                                        <li>
                                            <strong>{{ $hours['day'] }}:</strong>
                                            @if($hours['is_closed'])
                                                <span class="text-danger">Tutup</span>
                                            @else
                                                {{ $hours['open'] ?? 'N/A' }} - {{ $hours['close'] ?? 'N/A' }}
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                                <h5 class="mt-4"><i class="ri-service-line me-2 text-primary"></i>Layanan</h5>
                                <ul>
                                    @forelse($outlet->services as $service)
                                        <li>{{ $service->name }}</li>
                                    @empty
                                        <li>Tidak ada layanan yang terdaftar.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Tutup</button>
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $outlet->latitude }},{{ $outlet->longitude }}"
                           target="_blank"
                           class="btn btn-primary rounded-pill">
                            <i class="ri-road-map-line me-1"></i>
                            Buka di Google Maps
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    <style>
        .outlet-card {
            border: 1px solid #e0e0e0;
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            overflow: hidden;
            background-color: #fff;
        }

        .outlet-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(33, 150, 243, 0.1);
        }

        .outlet-image-container {
            position: relative;
        }

        .outlet-card .card-img-top {
            height: 200px;
            object-fit: cover;
        }

        .btn-detail-icon {
            background-color: #007bff;
            color: #fff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            transition: background-color 0.3s ease;
        }

        .btn-detail-icon:hover {
            background-color: #0056b3;
        }

        .outlet-map {
            height: 300px; /* Ukuran peta dalam modal */
            width: 100%;
        }
    </style>
@endpush

@push('scripts')


<script>
    document.addEventListener('DOMContentLoaded', function() {
    var maps = {};

    // Inisialisasi peta untuk setiap modal saat dibuka
    var modals = document.querySelectorAll('.modal');
    modals.forEach(function(modal) {
        modal.addEventListener('shown.bs.modal', function () {
            const outletId = modal.id.replace('outletDetailModal', '');
            const mapId = `map-${outletId}`;
            const mapElement = document.getElementById(mapId);

            if (mapElement && !maps[mapId]) {
                const outletCard = document.getElementById(`outlet-card-${outletId}`);
                const latlongData = JSON.parse(outletCard.dataset.latlong);

                if (latlongData && latlongData.lat && latlongData.lon) {
                    const lat = latlongData.lat;
                    const lon = latlongData.lon;

                    const map = L.map(mapId).setView([lat, lon], 15);
                    maps[mapId] = map;

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(map);

                    L.marker([lat, lon]).addTo(map)
                        .bindPopup(`<b>${outletCard.querySelector('.card-title').textContent}</b>`).openPopup();

                    setTimeout(() => { map.invalidateSize(); }, 300);
                }
            }
        });

        modal.addEventListener('hidden.bs.modal', function () {
            const outletId = modal.id.replace('outletDetailModal', '');
            const mapId = `map-${outletId}`;
            if (maps[mapId]) {
                maps[mapId].remove();
                delete maps[mapId];
            }
        });
    });

    // ===== AUTO OPEN MODAL BERDASARKAN URL PARAMETER =====
    const urlParams = new URLSearchParams(window.location.search);
    const outletCode = urlParams.get('outlet');

    if (outletCode) {
        // Cari card yang punya data-code
        const targetCard = document.querySelector(`[data-code="${outletCode}"]`);
        if (targetCard) {
            const outletId = targetCard.id.replace('outlet-card-', '');
            const modalElement = document.getElementById(`outletDetailModal${outletId}`);
            if (modalElement) {
                const modalInstance = new bootstrap.Modal(modalElement);
                modalInstance.show();
            }
        }
    }
});

</script>
@endpush
