@props([
    'items' => ['Admin', 'Tipe Layanan', 'Tambah Tipe Layanan'],
    'title' => 'Tambah Tipe Layanan',
    'subtitle' => 'Tambah Tipe Layanan Baru'
])
@extends('layouts.dashboard.app')

@section('content')
<x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

<div class="panel panel-inverse">
    <div class="panel-heading">
        <h4 class="panel-title">{{ $title }}</h4>
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

        <form action="{{ route('admin.service_types.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="form-label">Nama Service <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Contoh: Laundry" required>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <label class="form-label mb-0">Konfigurasi Item (Max 4 untuk Device)</label>
                    <button type="button" class="btn btn-sm btn-success" onclick="addItem()">
                        <i class="fa fa-plus me-1"></i> Tambah Item
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Key (System)</th>
                                <th>Label (Display)</th>
                                <th class="text-center">Punya Durasi?</th>
                                <th class="text-center" style="width: 50px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="items-container">
                            <tr class="item-row">
                                <td>
                                    <input type="text" name="items[0][key]" class="form-control form-control-sm" placeholder="washer_a" required>
                                </td>
                                <td>
                                    <input type="text" name="items[0][label]" class="form-control form-control-sm" placeholder="Washer A" required>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input" type="checkbox" name="items[0][has_duration]" value="1">
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(this)">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Simpan Service Type</button>
                <a href="{{ route('admin.service_types.index') }}" class="btn btn-default">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let rowIdx = 1;

    function addItem() {
        const container = document.getElementById('items-container');
        const rows = document.querySelectorAll('.item-row');

        if (rows.length >= 4) {
            alert('Maksimal 4 item sesuai dengan slot device.');
            return;
        }

        const html = `
            <tr class="item-row">
                <td>
                    <input type="text" name="items[${rowIdx}][key]" class="form-control form-control-sm" placeholder="key" required>
                </td>
                <td>
                    <input type="text" name="items[${rowIdx}][label]" class="form-control form-control-sm" placeholder="Label" required>
                </td>
                <td class="text-center">
                    <div class="form-check form-switch d-inline-block">
                        <input class="form-check-input" type="checkbox" name="items[${rowIdx}][has_duration]" value="1">
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(this)">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        container.insertAdjacentHTML('beforeend', html);
        rowIdx++;
    }

    function removeItem(btn) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length > 1) {
            btn.closest('tr').remove();
        } else {
            alert('Minimal harus ada satu item.');
        }
    }
</script>
@endpush
