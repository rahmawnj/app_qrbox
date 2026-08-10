@props([
    'items' => ['Admin', 'Manajemen Device', 'Buat/Edit Device'],
    'title' => 'Konfigurasi Device',
    'subtitle' => 'Konfigurasi unit dan aktifkan slot menu yang diperlukan',
])
@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('admin.devices.store') }}" method="POST" id="deviceForm">
        @csrf
        <div class="row">
            {{-- Panel Kiri --}}
            <div class="col-xl-4">
                <div class="panel panel-inverse">
                    <div class="panel-heading">
                        <h4 class="panel-title">Informasi Utama</h4>
                    </div>
                    <div class="panel-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Nama Device <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Outlet <span class="text-danger">*</span></label>
                            <select name="outlet_id" class="form-control default-select2" required>
                                <option value="">-- Pilih Outlet --</option>
                                @foreach ($outlets as $outlet)
                                    <option value="{{ $outlet->id }}">{{ $outlet->outlet_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Service Type <span class="text-danger">*</span></label>
                            <select name="service_type_id" id="service_type_id" class="form-control default-select2" required>
                                <option value="">-- Pilih Tipe --</option>
                                @foreach ($serviceTypes as $st)
                                    <option value="{{ $st->id }}" data-items='@json($st->items)'>
                                        {{ strtoupper($st->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <hr>
                        <button type="submit" class="btn btn-primary w-100 shadow-sm">
                            <i class="fa fa-save me-1"></i> Simpan Device
                        </button>
                    </div>
                </div>
            </div>

            {{-- Panel Kanan --}}
            <div class="col-xl-8">
                <div class="panel panel-inverse">
                    <div class="panel-heading">
                        <h4 class="panel-title">Konfigurasi Slot Menu (Tombol 1-4)</h4>
                    </div>
                    <div class="panel-body">
                        <div class="row" id="menu-slots-row">
                            @for ($i = 0; $i < 4; $i++)
                                <div class="col-md-6 mb-4">
                                    <div class="card border shadow-none slot-card opacity-50" id="slot-card-{{ $i }}" style="pointer-events: none;">
                                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                                            <h6 class="mb-0 fw-bold text-dark">Tombol Layar {{ $i + 1 }}</h6>
                                            <span class="badge bg-secondary status-badge" id="badge-{{ $i }}">Disabled</span>
                                        </div>

                                        <div class="card-body p-3">
                                            <input type="hidden" name="menu[{{ $i }}][status]" id="status-{{ $i }}" value="false">

                                            <div class="mb-2">
                                                <label class="small fw-bold">Nama Menu</label>
                                                <input type="text" name="menu[{{ $i }}][name]" id="name-{{ $i }}" class="form-control form-control-sm slot-input-{{ $i }}" readonly>
                                            </div>

                                            <div class="row mb-2">
                                                <div class="col-6">
                                                    <label class="small fw-bold">Type</label>
                                                    <input type="text" name="menu[{{ $i }}][type]" id="type-{{ $i }}" class="form-control form-control-sm bg-light fw-bold" readonly>
                                                </div>
                                                <div class="col-6">
                                                    <label class="small fw-bold">Harga (Rp)</label>
                                                    <input type="number" name="menu[{{ $i }}][price]" id="price-{{ $i }}" class="form-control form-control-sm slot-input-{{ $i }}" value="0" readonly>
                                                </div>
                                            </div>

                                            <div class="mb-2">
                                                <label class="small fw-bold">Durasi (Detik)</label>
                                                <input type="number" name="menu[{{ $i }}][duration]" id="duration-{{ $i }}"
                                                       class="form-control form-control-sm slot-input-{{ $i }} duration-input"
                                                       data-id="{{ $i }}" value="0" readonly>

                                                {{-- Area info konversi menit dan No Timer --}}
                                                <div class="mt-1">
                                                    <small class="text-primary fw-bold d-none" id="duration-convert-{{ $i }}">
                                                        <i class="fa fa-info-circle"></i> Setara: <span class="minute-text">0</span> m <span class="second-text">0</span> s
                                                    </small>
                                                    <small class="text-danger fw-bold d-none" id="duration-info-{{ $i }}">
                                                        <i class="fa fa-ban"></i> No Timer (Trigger Only)
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="mb-0">
                                                <label class="small fw-bold">Deskripsi</label>
                                                <textarea name="menu[{{ $i }}][description]" id="desc-{{ $i }}" class="form-control form-control-sm slot-input-{{ $i }}" rows="2" readonly></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('styles')
    <link href="{{ asset('assets/plugins/select2/dist/css/select2.min.css') }}" rel="stylesheet" />
    <style>
        .slot-card { transition: all 0.3s ease; border-radius: 8px; border: 1px solid #e2e8f0; }
        .slot-active { opacity: 1 !important; pointer-events: all !important; border-color: #348fe2 !important; box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('assets/plugins/select2/dist/js/select2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $(".default-select2").select2();

            // 1. Event Change Service Type
            $('#service_type_id').on('change', function() {
                const items = $(this).find(':selected').data('items') || [];
                resetAllSlots();
                items.forEach((item, index) => {
                    if (index < 4) activateSlot(index, item);
                });
            });

            // 2. Real-time Conversion Logic (Detik -> Menit)
            $(document).on('input', '.duration-input', function() {
                const index = $(this).data('id');
                const totalSeconds = parseInt($(this).val()) || 0;

                updateDurationDisplay(index, totalSeconds);
            });

            function updateDurationDisplay(index, totalSeconds) {
                const container = $(`#duration-convert-${index}`);

                if (totalSeconds > 0) {
                    const minutes = Math.floor(totalSeconds / 60);
                    const seconds = totalSeconds % 60;

                    container.find('.minute-text').text(minutes);
                    container.find('.second-text').text(seconds);
                    container.removeClass('d-none');
                } else {
                    container.addClass('d-none');
                }
            }

            function activateSlot(index, item) {
                const card = $(`#slot-card-${index}`);
                const badge = $(`#badge-${index}`);
                const statusInput = $(`#status-${index}`);
                const typeInput = $(`#type-${index}`);
                const durationInput = $(`#duration-${index}`);
                const durationInfo = $(`#duration-info-${index}`);
                const durationConvert = $(`#duration-convert-${index}`);

                // Visual & Status
                card.addClass('slot-active').removeClass('opacity-50').css('pointer-events', 'all');
                badge.text('Aktif').removeClass('bg-secondary').addClass('bg-primary');
                statusInput.val('true');

                // Unlock Inputs
                card.find(`.slot-input-${index}`).prop('readonly', false);
                typeInput.val(item.key);

                // Timer Logic
                if (item.has_duration === false) {
                    durationInput.val(0).prop('readonly', true).addClass('bg-light');
                    durationInfo.removeClass('d-none');
                    durationConvert.addClass('d-none');
                } else {
                    durationInput.removeClass('bg-light');
                    durationInfo.addClass('d-none');
                    // Tampilkan konverter jika ada nilai awal (misal saat edit nantinya)
                    updateDurationDisplay(index, durationInput.val());
                }
            }

            function resetAllSlots() {
                for (let i = 0; i < 4; i++) {
                    const card = $(`#slot-card-${i}`);
                    const badge = $(`#badge-${i}`);
                    const statusInput = $(`#status-${i}`);
                    const durationInfo = $(`#duration-info-${i}`);
                    const durationConvert = $(`#duration-convert-${i}`);

                    card.removeClass('slot-active').addClass('opacity-50').css('pointer-events', 'none');
                    badge.text('Disabled').removeClass('bg-primary').addClass('bg-secondary');
                    statusInput.val('false');

                    const inputs = card.find(`.slot-input-${i}`);
                    inputs.prop('readonly', true).val('');

                    $(`#type-${i}`).val('');
                    $(`#price-${i}`).val(0);
                    $(`#duration-${i}`).val(0);

                    durationInfo.addClass('d-none');
                    durationConvert.addClass('d-none');
                }
            }
        });
    </script>
@endpush
