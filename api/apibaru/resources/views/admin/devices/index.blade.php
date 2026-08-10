@props([
    'items' => ['Admin', 'Manajemen Device', 'Daftar Device'],
    'title' => 'Daftar Device',
    'subtitle' => 'Kelola Device yang terdaftar',
])
@extends('layouts.dashboard.app')

@push('styles')
    <link href="{{ asset('assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/switchery/dist/switchery.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/gritter/css/jquery.gritter.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/sweetalert/dist/sweetalert.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">{{ $title }}</h4>
            <div class="panel-heading-btn">
                <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand"><i class="fa fa-expand"></i></a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload"><i class="fa fa-redo"></i></a>
                <a href="{{ route('admin.devices.create') }}" class="btn btn-xs btn-primary"><i class="fa fa-plus"></i> Tambah</a>
            </div>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table" id="data-table">
                    <thead>
                        <tr>
                            <th width="1">#</th>
                            <th>Brand / Outlet</th>
                            <th>Device Info</th>
                            <th>Service Type Name</th>
                            <th>Bypass Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($devices as $device)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <img src="{{ asset($device->outlet->owner->brand_logo ?? 'assets/img/default-user.png') }}"
                                             style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">
                                        <div>
                                            <div class="font-weight-bold">{{ $device->outlet->owner->brand_name ?? '-' }}</div>
                                            <div class="small text-muted">{{ $device->outlet->outlet_name ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="font-weight-bold">{{ $device->name }}</div>
                                    <code class="small">{{ $device->code }}</code>
                                </td>
                                <td>
                                    <span class="">
                                        {{ $device->serviceType->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <select class="device-status-select form-control form-control-sm"
                                            data-device-id="{{ $device->id }}"
                                            style="width: 150px;">
                                        <option value="off" {{ $device->device_status === 'off' ? 'selected' : '' }}>OFF (Normal)</option>

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

                                    @if($device->device_status !== 'off')
                                        <small class="text-danger d-block mt-1">
                                            <i class="fa fa-exclamation-triangle"></i> Aktif Bypass
                                        </small>
                                    @endif
                                </td>
                             <td>
                                <div class="btn-group">
                                    {{-- Tombol Detail Baru --}}
                                    <a href="{{ route('admin.devices.show', $device) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.devices.edit', $device) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.devices.destroy', $device) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus device?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- JS Plugins (Tetap sama) --}}
    <script src="{{ asset('assets/plugins/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/gritter/js/jquery.gritter.js') }}"></script>
    <script src="{{ asset('assets/plugins/sweetalert/dist/sweetalert.min.js') }}"></script>

<script>
    $(document).ready(function() {
        var table = $('#data-table').DataTable({
            responsive: true,
            dom: '<"row"<"col-sm-5"B><"col-sm-7"fr>>t<"row"<"col-sm-5"i><"col-sm-7"p>>',
            buttons: ['copy', 'excel', 'pdf', 'print'],
        });

        // Simpan status awal untuk setiap select
        function refreshOriginalStatus() {
            $('.device-status-select').each(function() {
                $(this).data('original', $(this).val());
            });
        }
        refreshOriginalStatus();

        $('#data-table').on('change', '.device-status-select', function() {
            var selectElement = $(this);
            var newStatus = selectElement.val();
            var deviceId = selectElement.data('device-id');
            var originalStatus = selectElement.data('original');

            if (newStatus !== 'off') {
                swal({
                    title: "Konfirmasi Bypass",
                    text: "Masukkan alasan bypass untuk mengaktifkan alat secara manual:",
                    content: {
                        element: "input",
                        attributes: {
                            placeholder: "Contoh: Tes teknisi / Komplain pelanggan",
                            type: "text",
                        },
                    },
                    buttons: {
                        cancel: "Batal",
                        confirm: {
                            text: "Aktifkan Bypass",
                            closeModal: false,
                        }
                    },
                    dangerMode: true,
                }).then(note => {
                    if (!note) {
                        selectElement.val(originalStatus); // Reset jika batal
                        return;
                    }
                    sendUpdateRequest(deviceId, newStatus, note, selectElement);
                });
            } else {
                // Jika mematikan bypass (kembali ke off)
                sendUpdateRequest(deviceId, 'off', 'Bypass dimatikan', selectElement);
            }
        });

        function sendUpdateRequest(deviceId, newStatus, note, selectElement) {
            fetch(`/api/devices/${deviceId}/update-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    device_status: newStatus,
                    bypass_note: note
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    swal("Berhasil!", data.message, "success");
                    selectElement.data('original', newStatus); // Update state original

                    // Notifikasi kecil
                    $.gritter.add({
                        title: 'Device Updated',
                        text: `Device status changed to ${newStatus}`,
                        class_name: 'gritter-light'
                    });
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                swal("Gagal!", error.message, "error");
                selectElement.val(selectElement.data('original')); // Rollback
            });
        }
    });
</script>
@endpush
