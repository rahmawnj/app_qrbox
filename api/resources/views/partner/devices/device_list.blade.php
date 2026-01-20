@extends('layouts.dashboard.app')
@php
    $feature = getData(); // Dapatkan instance DataFetcher sekali
@endphp

@props([
    'title' => 'List Device',
])

@push('styles')
    <link href="{{ asset('assets/plugins/switchery/dist/switchery.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/gritter/css/jquery.gritter.css') }}" rel="stylesheet" />
    <style>
        .device-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, .05);
            transition: all 0.2s ease-in-out;
        }

        .device-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, .1);
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            color: #fff;
            display: inline-block;
            min-width: 80px;
            text-align: center;
        }

        .status-off {
            background-color: #dc3545;
        }

        .status-active {
            background-color: #28a745;
        }

        .status-pending {
            background-color: #ffc107;
        }

        .status-unavailable {
            background-color: #6c757d;
        }

        .device-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .card.highlight {
            animation: highlightAnimation 2s ease-in-out;
        }

        @keyframes highlightAnimation {
            0% {
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
                transform: scale(1);
            }

            50% {
                box-shadow: 0 0 15px 5px rgba(40, 167, 69, 0.7);
                transform: scale(1.02);
            }

            100% {
                box-shadow: 0 2px 4px rgba(0, 0, 0, .05);
                transform: scale(1);
            }
        }
    </style>
    <link href="{{ asset('assets/plugins/select-picker/dist/picker.min.css') }}" rel="stylesheet" />
@endpush


@section('content')

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">{{ $title }}</h4>
            <div class="panel-heading-btn">

            </div>
        </div>

        <div class="panel-body">
            {{-- Search Input (Dropdown) --}}
            <div class="row mb-3 align-items-end">
                <div class="col-md-6">
                    <a href="javascript:;" class="btn btn-xs btn-primary" id="refresh-button">
                        <i class="fa fa-sync"></i> Refresh
                    </a>
                </div>
                <div class="col-md-6">
                    <label for="device-select" class="form-label">Pilih Device</label>
                    <select id="device-select" class="form-control selectpicker" data-live-search="true"
                        onchange="onDeviceChange(this)">
                        <option value="">-- Pilih Device --</option>
                        @foreach ($devices as $deviceFilter)
                            <option value="{{ $deviceFilter->id }}"> {{ $deviceFilter->code }} |
                                {{ $deviceFilter->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                @forelse ($devices as $device)
                    <div class="col-md-4 mb-4" id="device-{{ $device->id }}">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                                <h5 class="mb-0"><i class="fa fa-tablet-alt"></i> {{ $device->name }}</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Kode:</strong> {{ $device->code }}</p>
                                <p><strong>Outlet:</strong> {{ $device->outlet->outlet_name ?? 'N/A' }}</p>
                                <hr>
                                <h6 class="mb-2">Manajemen Status & Harga</h6>
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <select class="device-status-select form-control w-auto"
                                        data-device-id="{{ $device->id }}"
                                        {{ !$feature->can('partner.device.update_status') ? 'disabled' : '' }}>
                                        <option value="off" {{ $device->device_status === 'off' ? 'selected' : '' }}>
                                            Off
                                        </option>
                                       @for ($i = 1; $i <= 4; $i++)
                                            @php
                                                $opt = $device->{"option_$i"};
                                                $data = is_string($opt) ? json_decode($opt, true) : $opt;

                                                // Debugging logic:
                                                // 1. Pastikan data ada
                                                // 2. Tipe bukan 'disabled'
                                                // 3. Status active bukan false, bukan "false", bukan 0, dan bukan "0"
                                                $shouldShow = $data &&
                                                            isset($data['type']) &&
                                                            $data['type'] !== 'disabled' &&
                                                            isset($data['active']) &&
                                                            ($data['active'] !== false && $data['active'] !== 'false' && $data['active'] !== 0 && $data['active'] !== '0');
                                            @endphp

                                            @if($shouldShow)
                                                <option value="{{ $data['type'] }}"
                                                    {{ $device->device_status == $data['type'] ? 'selected' : '' }}>
                                                    {{ $data['name'] .' - '. $data['type'] }}
                                                </option>
                                            @endif
                                        @endfor
                                    </select>

                                    <div class="d-flex gap-2">
{{-- Tombol Edit: Diarahkan ke halaman edit (Warna Biru Info) --}}
<a href="{{ route('partner.device.edit', $device->id) }}"
    class="btn btn-sm btn-info {{ !$feature->can('partner.device.update') ? 'disabled' : '' }}"
    title="Edit Konfigurasi Device"
    {{ !$feature->can('partner.device.update') ? 'aria-disabled="true" tabindex="-1"' : '' }}>
    <i class="fa fa-edit"></i> </a>

{{-- Tombol Atur Harga: Membuka Modal (Warna Biru Primary / Hijau Success) --}}
<a href="#price-modal-{{ $device->id }}"
    class="btn btn-sm btn-primary {{ !$feature->can('partner.device.service_types.update') ? 'disabled' : '' }}"
    data-bs-toggle="modal"
    title="Set Prices"
    {{ !$feature->can('partner.device.service_types.update') ? 'aria-disabled="true" tabindex="-1"' : '' }}>
    <i class="fa fa-coins"></i> </a>
                                        <button type="button" class="btn btn-sm btn-danger delete-device-btn"
                                            data-device-id="{{ $device->id }}" data-device-name="{{ $device->name }}"
                                            title="Hapus Device"
                                            {{ !$feature->can('partner.device.destroy') ? 'disabled' : '' }}>
                                            <i class="fa fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>

                                 <div class="modal fade" id="price-modal-{{ $device->id }}">
    <div class="modal-dialog modal-xl"> <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h4 class="modal-title">
                    <i class="fa fa-info-circle me-2"></i> Detail Perangkat: {{ $device->name }}
                </h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-hidden="true"></button>
            </div>

            <div class="modal-body bg-light">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body text-center">
                                <div class="mb-3 mt-2">
                                    <img src="{{ asset($device->outlet->owner->brand_logo ?? 'assets/img/default-user.png') }}"
                                         class="img-thumbnail rounded-circle shadow-sm"
                                         style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #f8f9fa;">
                                </div>
                                <h3 class="fw-bold mb-1">{{ $device->name }}</h3>
                                <span class="badge bg-secondary mb-3">{{ $device->code }}</span>

                                <hr class="opacity-10">

                                <div class="text-start px-3">
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Brand</small>
                                        <span class="fw-bold">{{ $device->outlet->owner->brand_name ?? '-' }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Outlet</small>
                                        <span class="fw-bold">{{ $device->outlet->outlet_name ?? '-' }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Tipe Service</small>
                                        <span class="badge bg-info text-dark">{{ $device->serviceType->name ?? '-' }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Status Saat Ini</small>
                                        @if($device->device_status === 'off')
                                            <span class="badge bg-success"><i class="fa fa-check-circle me-1"></i> NORMAL (OFF)</span>
                                        @else
                                            <span class="badge bg-danger"><i class="fa fa-exclamation-triangle me-1"></i> BYPASS: {{ strtoupper($device->device_status) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3">
                                <h6 class="mb-0 fw-bold"><i class="fa fa-qrcode me-2 text-primary"></i>Konfigurasi Menu & Harga (QRIS)</h6>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" width="80">Opt</th>
                                            <th>Nama Menu</th>
                                            <th>Type (Slug)</th>
                                            <th>Harga (Gross)</th>
                                            <th>Deskripsi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @for ($i = 1; $i <= 4; $i++)
                                            @php
                                                $opt = $device->{"option_$i"};
                                                $data = is_string($opt) ? json_decode($opt, true) : $opt;
                                            @endphp
                                            <tr>
                                                <td class="text-center fw-bold text-primary">{{ $i }}</td>
                                                @if($data)
                                                    <td><span class="fw-bold">{{ $data['name'] ?? '-' }}</span></td>
                                                    <td><code class="text-danger">{{ $data['type'] ?? '-' }}</code></td>
                                                    <td class="fw-bold text-dark">Rp {{ number_format($data['price'] ?? 0, 0, ',', '.') }}</td>
                                                    <td class="small text-muted">{{ $data['description'] ?? '-' }}</td>
                                                @else
                                                    <td colspan="4" class="text-center text-muted fst-italic py-3">Tidak dikonfigurasi</td>
                                                @endif
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h6 class="mb-0 fw-bold"><i class="fa fa-history me-2 text-warning"></i>Informasi Bypass Terakhir</h6>
                            </div>
                            <div class="card-body">
                                @if($device->bypass_activation)
                                    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fa fa-info-circle fa-2x"></i>
                                        </div>
                                        <div>
                                            <p class="mb-1"><strong>Waktu Aktivasi:</strong> {{ \Carbon\Carbon::parse($device->bypass_activation)->format('d M Y H:i') }}</p>
                                            <p class="mb-0 text-dark"><strong>Catatan:</strong> {{ $device->bypass_note ?? 'Tidak ada catatan' }}</p>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center py-3">
                                        <i class="fa fa-check-circle fa-2x text-light mb-2"></i>
                                        <p class="text-muted mb-0">Belum ada riwayat bypass pada perangkat ini.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-white shadow-sm">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
                                <form action="{{ route('partner.device.update', $device->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <div class="modal fade" id="edit-device-modal-{{ $device->id }}">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">Edit Device - {{ $device->name }}</h4>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-hidden="true"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group mb-3">
                                                        <label for="device-name-{{ $device->id }}">Nama
                                                            Device</label>
                                                        <input type="text" name="name"
                                                            id="device-name-{{ $device->id }}" class="form-control"
                                                            value="{{ $device->name }}" required>
                                                    </div>
                                                    <div class="form-group mb-3">
                                                        <label for="device-outlet-{{ $device->id }}">Outlet</label>
                                                        <select name="outlet_id" id="device-outlet-{{ $device->id }}"
                                                            class="form-control" required>
                                                            <option value="">Pilih Outlet</option>
                                                            @foreach ($outlets as $outlet)
                                                                <option value="{{ $outlet->id }}"
                                                                    {{ $device->outlet_id == $outlet->id ? 'selected' : '' }}>
                                                                    {{ $outlet->outlet_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="javascript:;" class="btn btn-white"
                                                        data-bs-dismiss="modal">Tutup</a>
                                                    <button type="submit" class="btn btn-success">Simpan
                                                        Perubahan</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-warning text-center">
                            <strong>Belum ada device yang terdaftar.</strong>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>


@endsection

@push('scripts')
    <script src="{{ asset('assets/plugins/switchery/dist/switchery.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/gritter/js/jquery.gritter.js') }}"></script>
    <script src="{{ asset('assets/plugins/select-picker/dist/picker.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/sweetalert/dist/sweetalert.min.js') }}"></script>

    <script>
        function onDeviceChange(el) {
            const selectedDeviceId = el.value;
            console.log('Device selected via onchange:', selectedDeviceId);

            // Remove highlight from all previous cards
            $('.card').removeClass('highlight');

            if (selectedDeviceId) {
                const deviceContainer = $('#device-' + selectedDeviceId);
                if (deviceContainer.length) {
                    $('html, body').animate({
                        scrollTop: deviceContainer.offset().top - 120
                    }, 500, function() {
                        const targetCard = deviceContainer.find('.card');
                        targetCard.addClass('highlight');
                        setTimeout(() => targetCard.removeClass('highlight'), 5000);
                    });
                }
            }
        }
    </script>

    <script>
        $(document).ready(function() {
            $('.device-status-select').each(function() {
                $(this).data('original', $(this).val());
            });

            $('.device-status-select').on('change', function() {
                var newStatus = $(this).val();
                var deviceId = $(this).data('device-id');
                var originalStatus = $(this).data('original');

                // Tampilkan konfirmasi dan minta alasan bypass jika status BUKAN 'off'
                if (newStatus !== 'off') {
                    swal({
                        title: "Bypass Device?",
                        text: "Apakah Anda yakin ingin mengubah status device ke " + newStatus.toUpperCase() + "? Jika ya, berikan alasan bypass.",
                        icon: "warning",
                        content: "input",
                        buttons: {
                            cancel: "Batal",
                            confirm: {
                                text: "Ya, Ubah!",
                                value: true,
                                className: "btn-success"
                            }
                        },
                        dangerMode: false,
                    })
                    .then((inputValue) => {
                        if (inputValue === false) {
                            // User klik Batal
                            $(this).val(originalStatus);
                            return;
                        }
                        if (inputValue === "" || inputValue === null) {
                            // User tidak memberikan alasan
                            swal("Gagal!", "Alasan bypass tidak boleh kosong.", "error");
                            $(this).val(originalStatus);
                            return;
                        }

                        // Lanjutkan proses update
                        sendUpdateStatus(deviceId, newStatus, inputValue, this);
                    });
                } else {
                    // Jika status diubah kembali ke 'off', tidak perlu alasan
                    var confirmation = confirm("Apakah Anda yakin ingin mengubah status device ke OFF?");
                    if (!confirmation) {
                        $(this).val(originalStatus);
                        return;
                    }
                    // Lanjutkan proses update tanpa note
                    sendUpdateStatus(deviceId, newStatus, null, this);
                }
            });

            function sendUpdateStatus(deviceId, newStatus, bypassNote, selectElement) {
                var formData = new FormData();
                formData.append('device_status', newStatus);
                if (bypassNote) {
                    formData.append('bypass_note', bypassNote);
                }

                fetch('/api/devices/' + deviceId + '/update-status', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        $.gritter.removeAll();
                        if (data.status === 'success') {
                            $.gritter.add({
                                title: 'Success',
                                text: data.message,
                                sticky: false,
                                time: 3000,
                                class_name: 'my-sticky-class gritter-light'
                            });
                            $(selectElement).data('original', newStatus);
                        } else {
                            $.gritter.add({
                                title: 'Error',
                                text: data.message || 'Terjadi kesalahan saat mengupdate status',
                                sticky: false,
                                time: 3000,
                                class_name: 'my-sticky-class gritter-light'
                            });
                            $(selectElement).val($(selectElement).data('original'));
                        }
                    })
                    .catch(error => {
                        $.gritter.removeAll();
                        $.gritter.add({
                            title: 'Error',
                            text: 'Terjadi kesalahan saat mengupdate status. Silakan coba lagi.',
                            sticky: false,
                            time: 3000,
                            class_name: 'my-sticky-class gritter-light'
                        });
                        console.log('Error:', error);
                        $(selectElement).val($(selectElement).data('original'));
                    });
            }

            $('#refresh-button').on('click', function() {
                location.reload();
            });

            $('.delete-device-btn').on('click', function() {
                var deviceId = $(this).data('device-id');
                var deviceName = $(this).data('device-name');

                swal({
                    title: "Apakah Anda yakin?",
                    text: "Setelah dihapus, device '" + deviceName + "' tidak dapat dikembalikan!",
                    icon: "warning",
                    buttons: {
                        cancel: "Batal",
                        confirm: {
                            text: "Ya, Hapus!",
                            value: true,
                            className: "btn-danger"
                        }
                    },
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        fetch('/partner/devices/' + deviceId, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    swal("Berhasil!", data.message, "success");
                                    $('#device-' + deviceId).remove();
                                } else {
                                    swal("Gagal!", data.message, "error");
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                swal("Error!", "Terjadi kesalahan saat menghapus device.", "error");
                            });
                    } else {
                        swal("Penghapusan dibatalkan!", {
                            icon: "info",
                            button: "OK",
                        });
                    }
                });
            });
        });
        @if (session('success'))
            swal({
                title: "Berhasil!",
                text: "{{ session('success') }}",
                icon: "success",
                button: "OK",
            });
        @endif
        @if (session('error'))
            swal({
                title: "Gagal!",
                text: "{{ session('error') }}",
                icon: "error",
                button: "Coba Lagi",
            });
        @endif
    </script>
@endpush
