@props([
    'items' => ['Admin', 'Brand Management', 'Detail Brand'],
    'title' => 'Detail Brand & Owner',
    'subtitle' => 'Informasi lengkap data pemilik dan kontrak',
])
@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    <div class="panel panel-inverse m-b-20">
        <div class="panel-heading">
            <h4 class="panel-title"><i class="fa fa-wallet m-r-5"></i> Ringkasan Keuangan</h4>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-profile m-b-0">
                        <tbody><tr><td class="field">Saldo Tersedia</td><td><strong class="text-success">Rp {{ number_format($owner->balance, 0, ',', '.') }}</strong></td></tr></tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-profile m-b-0">
                        <tbody><tr><td class="field">Deposit / Jaminan</td><td><strong class="text-info">Rp {{ number_format($owner->deposit_amount, 0, ',', '.') }}</strong></td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="panel panel-inverse">
                <div class="panel-heading"><h4 class="panel-title">Informasi Owner</h4></div>
                <div class="panel-body text-center">
                    <img src="{{ $owner->user->image ? asset($owner->user->image) : asset('assets/img/default-user.png') }}"
                         class="img-circle img-thumbnail m-b-15" style="width: 120px; height: 120px; object-fit: cover;">
                    <h3 class="m-t-5">{{ $owner->user->name }}</h3>
                    <p class="text-muted">{{ $owner->user->email }}</p>
                    <p><span class="label {{ $owner->status ? 'label-success' : 'label-danger' }}">{{ $owner->status ? 'Aktif' : 'Nonaktif' }}</span></p>
                </div>
            </div>

            <div class="panel panel-inverse">
                <div class="panel-heading"><h4 class="panel-title"><i class="fa fa-university m-r-5"></i> Informasi Rekening</h4></div>
                <div class="panel-body">
                    <table class="table table-profile m-b-0">
                        <tbody>
                            <tr><td class="field">Bank</td><td>{{ $owner->bank_name ?? '-' }}</td></tr>
                            <tr><td class="field">Nomor Rekening</td><td>{{ $owner->bank_account_number ?? '-' }}</td></tr>
                            <tr><td class="field">Atas Nama</td><td>{{ $owner->bank_account_holder_name ?? '-' }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Detail Brand & Kontrak</h4>
                    <div class="panel-heading-btn">
                        <span class="label label-default">Kode: {{ $owner->code }}</span>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-2 text-center">
                            <img src="{{ $owner->brand_logo ? asset($owner->brand_logo) : asset('assets/img/default-img.png') }}"
                                 class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                        </div>
                        <div class="col-sm-10">
                            <h3 class="m-t-5">{{ $owner->brand_name }}</h3>
                            <p class="text-muted"><i class="fa fa-phone"></i> {{ $owner->brand_phone ?? 'Tidak ada telepon' }}</p>
                        </div>
                    </div>
                    <hr>
                    <table class="table table-profile">
                        <tbody>
                            <tr><td class="field">Nomor Kontrak</td><td>{{ $owner->contract_number ?? 'Belum diatur' }}</td></tr>
                            <tr>
                                <td class="field">Masa Berlaku Kontrak</td>
                                <td>
                                    @if($owner->contract_start)
                                        <span class="text-success">{{ CarbonCarbon::parse($owner->contract_start)->translatedFormat('d M Y') }}</span>
                                        <span class="text-muted m-l-5 m-r-5">s/d</span>
                                        <span class="text-danger">{{ CarbonCarbon::parse($owner->contract_end)->translatedFormat('d M Y') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr><td class="field">Deskripsi Brand</td><td>{{ $owner->brand_description ?? 'Tidak ada deskripsi.' }}</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="panel-footer text-right">
                    <a href="{{ route('admin.owners.index') }}" class="btn btn-default m-r-5">Kembali</a>
                    <a href="{{ route('admin.owners.edit', $owner->id) }}" class="btn btn-primary"><i class="fa fa-edit"></i> Edit Data</a>
                </div>
            </div>

            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Daftar Outlet Terdaftar</h4>
                    <div class="panel-heading-btn"><span class="label label-primary">{{ $owner->outlets->count() }} Outlet</span></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-valign-middle m-b-0">
                        <thead>
                            <tr><th>Nama Outlet</th><th>Alamat</th><th class="text-right">Telepon</th></tr>
                        </thead>
                        <tbody>
                            @forelse($owner->outlets as $outlet)
                                <tr>
                                    <td><a href="{{ route('admin.outlets.show', $outlet) }}"><strong>{{ $outlet->outlet_name }}</strong></a></td>
                                    <td class="text-muted">{{ Str::limit($outlet->address, 60) }}</td>
                                    <td class="text-right">{{ $outlet->phone_number ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted p-30">Belum ada outlet terdaftar untuk brand ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection