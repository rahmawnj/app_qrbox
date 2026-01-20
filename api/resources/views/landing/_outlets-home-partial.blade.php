<h2 class="text-center mb-5 fw-bold" id="outlets-section-title">
    {{ $outletSectionTitle }}
</h2>
<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="outlet-list-container">
    @forelse($outlets as $outlet)
        <div class="col outlet-card-col">
            <div class="card outlet-card h-100">
                <div class="outlet-image-container">
                    <img src="{{ $outlet->owner->brand_logo ? asset( $outlet->owner->brand_logo) : asset('assets/img/default-brand-logo.png') }}"
                        alt="{{ $outlet->owner->brand_name }} Logo">
                    @if ($outlet->owner)
                        <span class="brand-badge">Brand: {{ $outlet->owner->brand_name }}</span>
                    @endif
                </div>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title fw-bold">{{ $outlet->outlet_name }}</h5>
                    <p class="card-text text-muted">{{ $outlet->address }}</p>
                    <div class="outlet-info">
                        <i class="ri-map-pin-line"></i> <span class="outlet-city">{{ $outlet->city_name ?? 'Tidak diketahui' }}</span>
                    </div>
                    <div class="outlet-info">
                        <i class="ri-direction-fill"></i> Jarak:
                        @if (isset($outlet->distance))
                            <span class="outlet-distance">{{ number_format($outlet->distance, 2) }} km</span>
                        @else
                            <span class="outlet-distance">N/A</span>
                        @endif
                    </div>
                    <div class="outlet-info">
                        <i class="ri-star-fill"></i> {{ number_format(4.5, 1) }} (Dari 120 ulasan)
                    </div>
                    <div class="outlet-info mb-3">
                        <i class="ri-time-line"></i> Buka <span class="badge bg-success ms-2">Sekarang</span>
                    </div>
                    <div class="mt-auto">
                        <a href="#" class="btn btn-primary btn-sm mt-3 w-100">Lihat Detail & Layanan</a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center" id="no-outlets-message">
            <p class="text-muted">Tidak ada outlet ditemukan di area ini.</p>
        </div>
    @endforelse
</div>
<div class="text-center mt-5" id="view-all-outlets-btn-container">
    <a href="#" class="btn btn-outline-primary btn-lg px-4">Lihat Semua Outlet</a>
</div>
