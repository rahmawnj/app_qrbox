@props([
    'items' => ['Admin', 'Manajemen Device', 'Edit Device'],
    'title' => 'Edit Device',
    'subtitle' => 'Perbarui konfigurasi unit dan slot menu',
])
@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('admin.devices.update', $device->id) }}" method="POST" id="deviceForm">
        @csrf
        @method('PUT')

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
                            <input type="text" class="form-control" name="name" value="{{ old('name', $device->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Device Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="code" value="{{ old('code', $device->code) }}" required>
                            <small class="text-muted">Gunakan kode unik untuk identifikasi perangkat.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Outlet <span class="text-danger">*</span></label>
                            <select name="outlet_id" class="form-control default-select2" required>
                                @foreach ($outlets as $outlet)
                                    <option value="{{ $outlet->id }}" {{ old('outlet_id', $device->outlet_id) == $outlet->id ? 'selected' : '' }}>
                                        {{ $outlet->outlet_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Service Type <span class="text-danger">*</span></label>
                            <select name="service_type_id" id="service_type_id" class="form-control default-select2" required>
                                <option value="">-- Pilih Tipe --</option>
                                @foreach ($serviceTypes as $st)
                                    <option value="{{ $st->id }}" data-items='@json($st->items)'
                                        {{ old('service_type_id', $device->service_type_id) == $st->id ? 'selected' : '' }}>
                                        {{ strtoupper($st->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <hr>
                        <button type="submit" class="btn btn-primary w-100 shadow-sm">
                            <i class="fa fa-save me-1"></i> Perbarui Device
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
                            @php
                                $options = [$device->option_1, $device->option_2, $device->option_3, $device->option_4];
                            @endphp

                            @for ($i = 0; $i < 4; $i++)
                                @php
                                    $m = is_string($options[$i]) ? json_decode($options[$i], true) : $options[$i];
                                    $isActive = isset($m['type']) && $m['type'] !== 'disabled' && ($m['active'] ?? false);
                                @endphp

                                <div class="col-md-6 mb-4">
                                    <div class="card border shadow-none slot-card {{ $isActive ? 'slot-active' : 'opacity-50' }}"
                                         id="slot-card-{{ $i }}" style="{{ $isActive ? '' : 'pointer-events: none;' }}">

                                        <input type="hidden" name="menu[{{ $i }}][type]" id="type-{{ $i }}" value="{{ $m['type'] ?? 'disabled' }}">
                                        <input type="hidden" name="menu[{{ $i }}][active]" id="active-{{ $i }}" value="{{ $isActive ? 'true' : 'false' }}">

                                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                                            <h6 class="mb-0 fw-bold text-dark">Tombol Layar {{ $i + 1 }}</h6>
                                            <span class="badge {{ $isActive ? 'bg-primary' : 'bg-secondary' }} status-badge" id="badge-{{ $i }}">
                                                {{ $isActive ? 'Aktif' : 'Disabled' }}
                                            </span>
                                        </div>

                                        <div class="card-body p-3">
                                            <div class="mb-2">
                                                <label class="small fw-bold">Nama Menu</label>
                                                <input type="text" name="menu[{{ $i }}][name]" id="name-{{ $i }}"
                                                       value="{{ old("menu.$i.name", $m['name'] ?? '') }}"
                                                       class="form-control form-control-sm slot-field-{{ $i }}" {{ !$isActive ? 'disabled' : '' }}>
                                            </div>

                                            <div class="row mb-2">
                                                <div class="col-6">
                                                    <label class="small fw-bold">Tipe Key</label>
                                                    <input type="text" id="display-type-{{ $i }}"
                                                           value="{{ ($m['type'] ?? '') !== 'disabled' ? ($m['type'] ?? '-') : '-' }}"
                                                           class="form-control form-control-sm bg-light fw-bold" readonly>
                                                </div>
                                                <div class="col-6">
                                                    <label class="small fw-bold">Harga (Rp)</label>
                                                    <input type="number" name="menu[{{ $i }}][price]" id="price-{{ $i }}"
                                                           value="{{ old("menu.$i.price", $m['price'] ?? 0) }}"
                                                           class="form-control form-control-sm slot-field-{{ $i }}" {{ !$isActive ? 'disabled' : '' }}>
                                                </div>
                                            </div>

                                            <div class="mb-2">
                                                <label class="small fw-bold">Durasi (Detik)</label>
                                                <input type="number" name="menu[{{ $i }}][duration]" id="duration-{{ $i }}"
                                                       value="{{ old("menu.$i.duration", $m['duration'] ?? 0) }}"
                                                       data-id="{{ $i }}"
                                                       class="form-control form-control-sm slot-field-{{ $i }} duration-input" {{ !$isActive ? 'disabled' : '' }}>

                                                <div class="mt-1">
                                                    <small class="text-primary fw-bold d-none" id="duration-convert-{{ $i }}">
                                                        <i class="fa fa-info-circle"></i> Setara: <span class="minute-text">0</span> m <span class="second-text">0</span> s
                                                    </small>
                                                    <small class="text-danger fw-bold d-none" id="duration-info-{{ $i }}">
                                                        <i class="fa fa-clock"></i> No Timer
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="mb-0">
                                                <label class="small fw-bold">Deskripsi</label>
                                                <textarea name="menu[{{ $i }}][description]" id="desc-{{ $i }}"
                                                          class="form-control form-control-sm slot-field-{{ $i }}"
                                                          rows="2" {{ !$isActive ? 'disabled' : '' }}>{{ old("menu.$i.description", $m['description'] ?? '') }}</textarea>
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
        .slot-active { opacity: 1 !important; pointer-events: all !important; border-color: #348fe2 !important; box-shadow: 0 4px 10px rgba(52,143,226,0.15); }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('assets/plugins/select2/dist/js/select2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $(".default-select2").select2();

            // 1. Logic Hitung Menit
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

            // 2. Event Input Durasi
            $(document).on('input', '.duration-input', function() {
                const index = $(this).data('id');
                const val = parseInt($(this).val()) || 0;
                updateDurationDisplay(index, val);
            });

            // 3. Handle Perubahan Service Type
            $('#service_type_id').on('change', function() {
                const items = $(this).find(':selected').data('items') || [];
                resetAllSlots();
                items.forEach((item, index) => {
                    if (index < 4) activateSlot(index, item);
                });
            });

            function activateSlot(index, item) {
                const card = $(`#slot-card-${index}`);
                const badge = $(`#badge-${index}`);
                const typeHidden = $(`#type-${index}`);
                const activeHidden = $(`#active-${index}`);
                const typeDisplay = $(`#display-type-${index}`);
                const durationInput = $(`#duration-${index}`);
                const durationInfo = $(`#duration-info-${index}`);
                const durationConvert = $(`#duration-convert-${index}`);

                card.addClass('slot-active').removeClass('opacity-50').css('pointer-events', 'all');
                badge.text('Aktif').removeClass('bg-secondary').addClass('bg-primary');

                // Buka field (remove disabled)
                card.find(`.slot-field-${index}`).prop('disabled', false);

                typeHidden.val(item.key);
                typeDisplay.val(item.key);
                activeHidden.val('true');

                if (item.has_duration === false) {
                    durationInput.val(0).prop('readonly', true).addClass('bg-light');
                    durationInfo.removeClass('d-none');
                    durationConvert.addClass('d-none');
                } else {
                    durationInput.prop('readonly', false).removeClass('bg-light');
                    durationInfo.addClass('d-none');
                    updateDurationDisplay(index, durationInput.val());
                }
            }

            function resetAllSlots() {
                for (let i = 0; i < 4; i++) {
                    const card = $(`#slot-card-${i}`);
                    $(`#type-${i}`).val('disabled');
                    $(`#active-${i}`).val('false');
                    $(`#display-type-${i}`).val('-');
                    $(`#badge-${i}`).text('Disabled').removeClass('bg-primary').addClass('bg-secondary');

                    card.removeClass('slot-active').addClass('opacity-50').css('pointer-events', 'none');
                    card.find(`.slot-field-${i}`).prop('disabled', true).val('');
                    $(`#duration-info-${i}`).addClass('d-none');
                    $(`#duration-convert-${i}`).addClass('d-none');
                }
            }

            // 4. Inisialisasi Saat Load (Mode Edit)
            const currentServiceItems = $('#service_type_id').find(':selected').data('items') || [];
            if(currentServiceItems.length > 0) {
                currentServiceItems.forEach((item, index) => {
                    if (index < 4) {
                        const durationInput = $(`#duration-${index}`);
                        const durationInfo = $(`#duration-info-${index}`);

                        // Jalankan hitungan menit untuk data yang sudah ada
                        if (item.has_duration === false) {
                            durationInput.prop('readonly', true).addClass('bg-light');
                            durationInfo.removeClass('d-none');
                        } else {
                            updateDurationDisplay(index, durationInput.val());
                        }
                    }
                });
            }
        });
    </script>
@endpush
