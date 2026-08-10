@props([
    'items' => ['Admin', 'Cashier Management', 'Cashier List'],
    'title' => 'Cashier List',
    'subtitle' => 'Manage registered Cashiers here'
])

@extends('layouts.dashboard.app')

@section('content')
<x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

<div class="panel panel-inverse">
    <div class="panel-heading">
        <h4 class="panel-title">{{ $title }}</h4>
        <div class="panel-heading-btn">
            <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand"><i class="fa fa-expand"></i></a>
            <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload"><i class="fa fa-redo"></i></a>
            <a href="{{ route('admin.cashiers.create') }}" class="btn btn-xs btn-primary"><i class="fa fa-plus"></i> Tambah</a>
        </div>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="data-table">
                <thead>
                    <tr>
                        <th width="1%">#</th>
                        <th>Nama Kasir</th>
                        <th>Outlet</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cashiers as $cashier)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $cashier->user->image ? asset($cashier->user->image) : asset('assets/img/default-user.png') }}"
                                         alt="Foto" class="rounded-circle me-2"
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $cashier->user->name }}</div>
                                        <small class="text-muted">ID: KSR-{{ str_pad($cashier->id, 4, '0', STR_PAD_LEFT) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($cashier->outlet)
                                    <span class="badge bg-info text-dark">{{ $cashier->outlet->outlet_name }}</span>
                                @else
                                    <span class="text-muted small"><i>Belum Ditugaskan</i></span>
                                @endif
                            </td>
                            <td>{{ $cashier->user->email }}</td>
                            <td>
                                @if ($cashier->status)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.cashiers.show', $cashier->id) }}" class="btn btn-info btn-xs">
                                    <i class="fas fa-search me-1"></i> Detail
                                </a>
                                <a href="{{ route('admin.cashiers.edit', $cashier->id) }}" class="btn btn-primary btn-xs">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                                <form action="{{ route('admin.cashiers.destroy', $cashier->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Yakin ingin menghapus kasir ini?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
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
    <link href="{{ asset('assets/plugins/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}" rel="stylesheet" />
@endpush

@push('scripts')
    <script src="{{ asset('assets/plugins/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/sweetalert/dist/sweetalert.min.js') }}"></script>

    <script>
        @if (session('success'))
            swal({ title: 'Berhasil', text: '{{ session('success') }}', icon: 'success', button: 'OK' });
        @endif
        @if (session('error'))
            swal({ title: 'Gagal', text: '{{ session('error') }}', icon: 'error', button: 'OK' });
        @endif

        $('#data-table').DataTable({
            responsive: true,
            dom: '<"row"<"col-sm-5"B><"col-sm-7"fr>>t<"row"<"col-sm-5"i><"col-sm-7"p>>',
            buttons: [
            ],
        });
    </script>
@endpush
