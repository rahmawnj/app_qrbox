<div class="row g-4 mt-5">
    @foreach ($outlets as $outlet)
        <div class="col-md-6 col-lg-4">
            <div class="card-outlet">
                <div class="outlet-brand-info">{{ $outlet->owner->brand_name }}</div>
                <img src="{{ asset( $outlet->image) }}" class="card-img-top" alt="{{ $outlet->outlet_name }}">
                <div class="card-body">
                    <div class="outlet-info-text">
                        <h5 class="fw-bold text-primary-custom">{{ $outlet->outlet_name }}</h5>
                        <p class="text-muted"><i class="ri-map-pin-line me-1"></i>{{ $outlet->address }}</p>
                    </div>
                    <a href="{{ route('home.brand', ['brand' => $outlet->owner->code, 'outlet' => $outlet->code]) }}" class="btn-icon-detail">
                        <i class="ri-arrow-right-line"></i>
                    </a>
                </div>
            </div>
        </div>
    @endforeach
</div>
