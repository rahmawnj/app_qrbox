@if($outlets->count() > 0)
    <div class="row g-4">
        @foreach($outlets as $outlet)
            @php
                $location = json_decode($outlet->latlong);
                $distance = null;
                // Logika penghitungan jarak bisa diletakkan di sini jika ada
                // Pastikan $outlet->distance sudah tersedia dari controller
                if (isset($outlet->distance)) {
                    $distance = $outlet->distance;
                }
            @endphp
            <div class="col-12">
                <div class="card card-outlet-horizontal d-flex flex-md-row flex-column">
                    <div class="col-md-3 p-3 d-flex align-items-center justify-content-center">
                        <img src="{{ asset( $outlet->image) }}" class="img-fluid" alt="{{ $outlet->outlet_name }}">
                    </div>
                    <div class="col-md-9 d-flex flex-column justify-content-between p-3">
                        <div>
                            <p class="outlet-brand-info">{{ $outlet->owner->brand_name }}</p>
                            <h5 class="fw-bold">{{ $outlet->outlet_name }}</h5>
                            <p class="text-muted mb-2">{{ Str::limit($outlet->address, 100) }}</p>

                            <div class="d-flex align-items-center flex-wrap">
                                {{-- Menampilkan maksimal 3 layanan --}}
                                @if($outlet->services->isNotEmpty())
                                    @foreach($outlet->services->take(3) as $service)
                                        <span class="badge bg-primary-subtle text-secondary me-2 mb-1">
                                            {{ $service->name }}
                                        </span>
                                    @endforeach
                                    {{-- Menampilkan badge untuk sisa layanan jika lebih dari 3 --}}
                                    @if($outlet->services->count() > 3)
                                        <span class="badge bg-primary-subtle text-secondary me-2 mb-1">
                                            +{{ $outlet->services->count() - 3 }} lainnya
                                        </span>
                                    @endif
                                @endif
                            </div>

                            <div class="d-flex align-items-center mt-2">
                                @if(isset($outlet->distance))
                                    <span class="badge bg-secondary-subtle text-secondary me-2">
                                        <i class="ri-road-map-line me-1"></i>{{ round($outlet->distance, 2) }} km
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <a href="{{ route('home.brand', ['brand' => $outlet->owner->code, 'outlet' => $outlet->code]) }}" class="btn btn-primary rounded-pill">
                                <i class="ri-arrow-right-line"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="empty-state text-center">
        <i class="ri-search-2-line display-4 mb-3"></i>
        <h4 class="fw-bold">Tidak ada outlet yang ditemukan.</h4>
        <p>Coba ubah kriteria pencarian Anda atau gunakan lokasi saat ini.</p>
    </div>
@endif
