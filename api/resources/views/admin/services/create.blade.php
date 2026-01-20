@props([
    'items' => ['Admin', 'Layanan', 'Tambah Layanan'],
    'title' => 'Tambah Layanan',
    'subtitle' => 'Tambah Layanan Baru',
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
            <form action="{{ route('admin.services.store') }}" method="POST">
                @csrf
                <!-- Input Nama Layanan -->
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Layanan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                        name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Pilihan Satuan -->
                <div class="mb-3">
                    <label for="unit" class="form-label">Satuan <span class="text-danger">*</span></label>
                    <select class="form-control @error('unit') is-invalid @enderror" id="unit" name="unit"
                        required>
                        <option value="">Pilih Satuan</option>
                        <option value="kg" @if (old('unit') == 'kg') selected @endif>kg</option>
                        <option value="pcs" @if (old('unit') == 'pcs') selected @endif>pcs</option>
                        <option value="liter" @if (old('unit') == 'liter') selected @endif>liter</option>
                        <option value="hour" @if (old('unit') == 'hour') selected @endif>hour</option>
                        <option value="unit" @if (old('unit') == 'unit') selected @endif>unit</option>
                    </select>
                    @error('unit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Input Harga Member -->
                <div class="mb-3">
                    <label for="member_price" class="form-label">Harga Member <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('member_price') is-invalid @enderror" id="member_price"
                        name="member_price" value="{{ old('member_price') }}" required min="0">
                    @error('member_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Input Harga Non-Member -->
                <div class="mb-3">
                    <label for="non_member_price" class="form-label">Harga Non-Member <span
                            class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('non_member_price') is-invalid @enderror"
                        id="non_member_price" name="non_member_price" value="{{ old('non_member_price') }}" required
                        min="0">
                    @error('non_member_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Pilihan Outlet -->
                <div class="mb-3">
                    <label for="outlet_id" class="form-label">Outlet <span class="text-danger">*</span></label>
                    <select class="form-control @error('outlet_id') is-invalid @enderror" id="outlet_id" name="outlet_id"
                        required>
                        <option value="">Pilih Outlet</option>
                        @foreach ($outlets as $outlet)
                            <option value="{{ $outlet->id }}" @if (old('outlet_id') == $outlet->id) selected @endif>
                               {{ $outlet->owner->brand_name }} | {{ $outlet->outlet_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('outlet_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Pilihan Tipe Layanan (Multiple) -->
                <div class="mb-3">
                    <label class="form-label">Tipe Layanan</label>
                    <div class="form-control @error('service_type_ids') is-invalid @enderror p-2">
                        @foreach ($serviceTypes as $serviceType)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="service_type_ids[]"
                                    id="service-type-{{ $serviceType->id }}" value="{{ $serviceType->id }}"
                                    @if (is_array(old('service_type_ids')) && in_array($serviceType->id, old('service_type_ids'))) checked @endif>
                                <label class="form-check-label" for="service-type-{{ $serviceType->id }}">
                                    {{ $serviceType->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('service_type_ids')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('admin.services.index') }}" class="btn btn-default">Batal</a>
            </form>
        </div>
    </div>
@endsection
