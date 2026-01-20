@props([
    'items' => ['Admin', 'Tipe Layanan', 'Ubah Tipe Layanan'],
    'title' => 'Ubah Tipe Layanan',
    'subtitle' => 'Perbarui Tipe Layanan'
])
@extends('layouts.dashboard.app')
@section('title', $title ?? '')

@section('content')
<x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

<div class="panel panel-inverse">
    <div class="panel-heading">
        <h4 class="panel-title">{{ $title ?? '' }}</h4>
        <div class="panel-heading-btn">
            <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand"><i class="fa fa-expand"></i></a>
            <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload"><i class="fa fa-redo"></i></a>
        </div>
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

        <form action="{{ route('admin.service_types.update', $serviceType->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="form-label">Nama Service <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" value="{{ old('name', $serviceType->name) }}" required>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <label class="form-label mb-0">Konfigurasi Item (Sub-Service)</label>
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
                            @php
                                // Ambil data items dari model (sudah otomatis array karena $casts)
                                $currentItems = old('items', $serviceType->items ?? []);
                            @endphp

                            @foreach($currentItems as $index => $item)
                            <tr class="item-row">
                                <td>
                                    <input type="text" name="items[{{ $index }}][key]"
                                           class="form-control form-control-sm"
                                           value="{{ $item['key'] ?? '' }}" required>
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $index }}][label]"
                                           class="form-control form-control-sm"
                                           value="{{ $item['label'] ?? '' }}" required>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input" type="checkbox"
                                               name="items[{{ $index }}][has_duration]" value="1"
                                               {{ (isset($item['has_duration']) && $item['has_duration']) ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(this)">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4">Update Service Type</button>
                <a href="{{ route('admin.service_types.index') }}" class="btn btn-default px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Inisialisasi index berdasarkan jumlah item yang ada
    let rowIdx = {{ count($currentItems) }};

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
