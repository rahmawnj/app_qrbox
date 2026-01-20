@extends('layouts.dashboard.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
            <div>
                <h2 class="mb-1">Daftar Outlet</h2>
                <p class="text-muted">Kelola lokasi bisnis dan pengaturan operasional Anda.</p>
            </div>
          
        </div>

        <div class="row g-4" id="outletList">
            @forelse($outlets as $outlet)
                <div class="col-xl-6 col-md-12">
                    <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden card-outlet">
                        <div class="card-body p-0 d-flex flex-column flex-md-row">

                            <div class="bg-primary bg-opacity-10 p-4 d-flex flex-column justify-content-center align-items-center text-center col-md-4 border-end border-light">
                                <div class="bg-white rounded-circle d-flex justify-content-center align-items-center mb-3 shadow-sm" style="width: 90px; height: 90px;">
                                    @if($outlet->image)
                                        <img src="{{ asset('storage/'.$outlet->image) }}" class="rounded-circle w-100 h-100 object-fit-cover">
                                    @else
                                        <i class="fas fa-store fa-3x text-primary"></i>
                                    @endif
                                </div>
                                <h4 class="h5 fw-bold text-dark mb-1">{{ $outlet->outlet_name }}</h4>
                                <small class="badge bg-white text-primary border border-primary-subtle mb-2 px-3">#{{ $outlet->code }}</small>

                                <div class="mt-2">
                                    @if ($outlet->status)
                                        <span class="badge rounded-pill bg-success px-3">Aktif</span>
                                    @else
                                        <span class="badge rounded-pill bg-danger px-3">Non-Aktif</span>
                                    @endif
                                </div>
                            </div>

                            <div class="p-4 d-flex flex-column flex-grow-1 bg-white">
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <div class="p-2 border rounded-3 text-center">
                                            <small class="text-muted d-block">Mesin</small>
                                            <strong class="h6 mb-0">{{ $outlet->devices->count() }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-2 border rounded-3 text-center">
                                            <small class="text-muted d-block">Kota</small>
                                            <strong class="h6 mb-0 text-truncate d-block px-1">{{ $outlet->city_name ?? '-' }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="small">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted"><i class="fas fa-percentage me-2 text-primary"></i>Service Fee</span>
                                        <span class="fw-bold">{{ number_format($outlet->service_fee_percentage * 100, 1) }}%</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted"><i class="fas fa-clock me-2 text-primary"></i>Zona Waktu</span>
                                        <span class="fw-bold">{{ str_replace('Asia/', '', $outlet->timezone) }}</span>
                                    </div>
                                    <div class="d-flex align-items-start mt-3 pt-2 border-top">
                                        <i class="fas fa-map-marker-alt text-danger me-2 mt-1"></i>
                                        <p class="text-muted mb-0 small line-clamp-2">{{ $outlet->address }}</p>
                                    </div>
                                </div>

                                <div class="mt-4 d-flex gap-2">
                                    <a href="{{ route('partner.outlets.detail', $outlet->id) }}"
                                        class="btn btn-outline-primary rounded-pill flex-grow-1 shadow-sm">
                                        <i class="fas fa-search-plus me-1"></i> Detail
                                    </a>
                                    <button class="btn btn-light rounded-circle shadow-sm" title="Edit Cepat">
                                        <i class="fas fa-cog text-secondary"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <img src="{{ asset('assets/img/empty-state.svg') }}" style="width: 200px" class="mb-3 opacity-50">
                        <h5 class="text-muted">Belum ada outlet yang terdaftar.</h5>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <div class="modal fade" id="addOutletModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Tambah Outlet Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="addOutletForm" action="{{ route('partner.outlets.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nama Outlet <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="outlet_name" placeholder="Contoh: Laundry Express Cabang 1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nama Kota <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="city_name" placeholder="Contoh: Jakarta Selatan" required>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">Alamat Lengkap <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="address" rows="2" placeholder="Jalan raya, nomor gedung, dsb..." required></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Zona Waktu <span class="text-danger">*</span></label>
                                <select class="form-select border-primary-subtle" name="timezone" required>
                                    <option value="Asia/Jakarta">WIB (Waktu Indonesia Barat)</option>
                                    <option value="Asia/Makassar">WITA (Waktu Indonesia Tengah)</option>
                                    <option value="Asia/Jayapura">WIT (Waktu Indonesia Timur)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-muted">Service Fee (Default 10%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.001" name="service_fee_percentage" class="form-control" value="0.100">
                                    <span class="input-group-text bg-light">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-white px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">Simpan Outlet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

<style>
    .card-outlet { transition: transform 0.2s; }
    .card-outlet:hover { transform: translateY(-5px); }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
