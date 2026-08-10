@props([
    'items' => ['Admin', 'Brand Management', 'Brand List'],
    'title' => 'Brand List',
    'subtitle' => 'Manage registered Brand here',
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

                <a href="{{ route('admin.owners.create') }}" class="btn btn-xs btn-primary"><i class="fa fa-plus"></i>
                    Tambah</a>
            </div>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table" id="data-table">
                 <thead>
    <tr>
        <th>#</th>
        <th>Pemilik</th>
        <th>Brand</th>
        <th>Kontrak</th> <th>Outlet</th>
        <th>Balance</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>
</thead>
<tbody>
    @foreach ($owners as $owner)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>
                <div class="d-flex align-items-center">
                    <img src="{{ $owner->user->image ? asset($owner->user->image) : asset('assets/img/default-user.png') }}"
                         class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;">
                    <div>
                        <div class="fw-bold">{{ $owner->user->name }}</div>
                        <div class="small text-muted">{{ $owner->user->email }}</div>
                    </div>
                </div>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <img src="{{ $owner->brand_logo ? asset($owner->brand_logo) : asset('assets/img/default-img.png') }}"
                         class="rounded" style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;">
                    <div>
                        <div class="fw-bold">[{{ $owner->code }}] {{ $owner->brand_name }}</div>
                        <div class="small text-muted">{{ $owner->brand_phone ?? '-' }}</div>
                    </div>
                </div>
            </td>
            <td>
                @if($owner->contract_end)
                    <div class="small">No: {{ $owner->contract_number ?? '-' }}</div>
                    <div class="small text-primary">{{ \Carbon\Carbon::parse($owner->contract_start)->format('d/m/y') }} - {{ \Carbon\Carbon::parse($owner->contract_end)->format('d/m/y') }}</div>
                @else
                    <span class="text-muted small">Tanpa Kontrak</span>
                @endif
            </td>
            <td>
                <a href="#modal-dialog-{{ $owner->id }}" data-bs-toggle="modal" class="badge bg-info text-decoration-none">
                    {{ $owner->outlets->count() }} Outlet
                </a>
            </td>
            <td>
                <strong class="text-success">Rp {{ number_format($owner->balance, 0, ',', '.') }}</strong>
            </td>
            <td>
                <span class="badge {{ $owner->status ? 'bg-success' : 'bg-danger' }}">
                    {{ $owner->status ? 'Aktif' : 'Nonaktif' }}
                </span>
            </td>
            <td class="text-nowrap">
                <a href="{{ route('admin.owners.show', $owner->id) }}" class="btn btn-info btn-xs"><i class="fa fa-search"></i></a>
                <a href="{{ route('admin.owners.edit', $owner->id) }}" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i></a>
                <form action="{{ route('admin.owners.destroy', $owner) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Hapus data ini?')"><i class="fa fa-trash"></i></button>
                </form>
            </td>
        </tr>
        @endforeach
</tbody>
                </table>

            </div>
        </div>
    </div>
    <!-- END panel -->
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

    <script>
        $('#data-table').DataTable({
            responsive: true,
            dom: '<"row"<"col-sm-5"B><"col-sm-7"fr>>t<"row"<"col-sm-5"i><"col-sm-7"p>>',
            buttons: [{
                    extend: 'copy',
                    className: 'btn-sm'
                },
                {
                    extend: 'csv',
                    className: 'btn-sm'
                },
                {
                    extend: 'excel',
                    className: 'btn-sm'
                },
                {
                    extend: 'pdf',
                    className: 'btn-sm'
                },
                {
                    extend: 'print',
                    className: 'btn-sm'
                }
            ],
        });
    </script>
@endpush
