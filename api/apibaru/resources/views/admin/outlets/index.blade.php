@props([
    'items' => ['Admin', 'Outlet Management', 'Outlet List'],
    'title' => 'Outlet List',
    'subtitle' => 'Manage registered Outlet here',
])
@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">{{ $title ?? '' }}</h4>
            <div class="panel-heading-btn">
                <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand"><i
                        class="fa fa-expand"></i></a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload"><i
                        class="fa fa-redo"></i></a>

                <a href="{{ route('admin.outlets.create') }}" class="btn btn-xs btn-primary"> <i
                        class="fa fa-plus"></i> Tambah</a>
            </div>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
               <table class="table table-hover align-middle" id="data-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Gambar</th>
            <th>Outlet & Kode</th>
            <th>Brand Owner</th>
            <th>Lokasi (Kota)</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($outlets as $outlet)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    <img src="{{ $outlet->image ? asset($outlet->image) : asset('assets/img/default-img.png') }}"
                         alt="{{ $outlet->outlet_name }}"
                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
                </td>
                <td>
                    <div class="fw-bold text-dark">{{ $outlet->outlet_name }}</div>
                    <code class="small">{{ $outlet->code }}</code>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <img src="{{ $outlet->owner->brand_logo ? asset($outlet->owner->brand_logo) : asset('assets/img/default-user.png') }}"
                             style="width: 35px; height: 35px; object-fit: cover; border-radius: 50%; margin-right: 10px;">
                        <div>
                            <div class="small fw-bold">{{ $outlet->owner->brand_name }}</div>
                            <div class="text-muted" style="font-size: 0.8rem;">{{ $outlet->owner->brand_email }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="small"><i class="fas fa-map-marker-alt text-danger me-1"></i> {{ $outlet->city_name ?? '-' }}</div>
                    <div class="text-muted small text-truncate" style="max-width: 200px;">{{ $outlet->address }}</div>
                </td>
                <td>
                    <span class="badge {{ $outlet->status ? 'bg-success' : 'bg-danger' }}">
                        {{ $outlet->status ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td>
                    <div class="btn-group">
                        <a href="{{ route('admin.outlets.show', $outlet) }}" class="btn btn-info btn-sm text-white" title="Detail">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('admin.outlets.edit', $outlet) }}" class="btn btn-primary btn-sm" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.outlets.destroy', $outlet) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus"
                                    onclick="return confirm('Hapus outlet ini?')">
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

@push('styles')
    <link href="{{ asset('assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}"
        rel="stylesheet" />
    <link href="{{ asset('assets/plugins/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}"
        rel="stylesheet" />
@endpush

@push('scripts')
    <script src="{{ asset('assets/plugins/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-buttons/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-buttons/js/buttons.flash.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/build/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/build/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/plugins/jszip/dist/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/sweetalert/dist/sweetalert.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi DataTable di sini
            const table = $('#data-table').DataTable({
                responsive: true,
                dom: '<"row"<"col-sm-5"B><"col-sm-7"fr>>t<"row"<"col-sm-5"i><"col-sm-7"p>>',
                buttons: [{
                    extend: 'copy',
                    className: 'btn-sm'
                }, {
                    extend: 'csv',
                    className: 'btn-sm'
                }, {
                    extend: 'excel',
                    className: 'btn-sm'
                }, {
                    extend: 'pdf',
                    className: 'btn-sm'
                }, {
                    extend: 'print',
                    className: 'btn-sm'
                }],
            });
        });
    </script>
    <script>
        @if (session('success'))
            swal({
                title: 'Success',
                text: '{{ session('success') }}',
                icon: 'success',
                button: {
                    text: 'OK',
                    className: 'btn btn-primary',
                    closeModal: true
                }
            });
        @endif

        @if (session('error'))
            swal({
                title: 'Error',
                text: '{{ session('error') }}',
                icon: 'error',
                button: {
                    text: 'OK',
                    className: 'btn btn-danger',
                    closeModal: true
                }
            });
        @endif
    </script>
@endpush
