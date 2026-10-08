@extends('layouts.dashboard.app')

@section('title', 'Detail Transaksi')

@section('content')
    <ol class="breadcrumb float-xl-end">
        <li class="breadcrumb-item"><a href="{{ route('admin.transactions.index') }}">Transaksi</a></li>
        <li class="breadcrumb-item active">Detail Transaksi</li>
    </ol>

    <h1 class="page-header">Detail Transaksi <small>{{ $transaction->order_id ?? '#' . $transaction->id }}</small></h1>

    <div class="row">
        <div class="col-xl-8">
            <div class="panel panel-inverse mb-4">
                <div class="panel-heading">
                    <h4 class="panel-title"><i class="fa fa-receipt me-2"></i>Informasi Transaksi</h4>
                    <div class="panel-heading-btn">
                        <a href="{{ route('admin.transactions.index') }}" class="btn btn-xs btn-default">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-profile">
                            <tbody>
                                <tr>
                                    <td class="field">ID</td>
                                    <td><strong>#{{ $transaction->id }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="field">ID Pesanan</td>
                                    <td>{{ $transaction->order_id ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="field">Tipe</td>
                                    <td>
                                        <span class="label {{ $transaction->type === 'payment' ? 'label-success' : 'label-danger' }}">
                                            {{ strtoupper($transaction->type ?? '-') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field">Status</td>
                                    <td>
                                        <span class="label
                                            {{ $transaction->status === 'success' ? 'label-success' : ($transaction->status === 'failed' ? 'label-danger' : 'label-warning') }}">
                                            {{ strtoupper($transaction->status ?? '-') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field">Nominal Bersih</td>
                                    <td><strong>Rp {{ number_format($transaction->amount ?? 0, 0, ',', '.') }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="field">Nominal Kotor</td>
                                    <td>Rp {{ number_format($transaction->gross_amount ?? 0, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="field">Service Fee</td>
                                    <td>Rp {{ number_format($transaction->service_fee_amount ?? 0, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="field">Persentase Fee</td>
                                    <td>{{ rtrim(rtrim(number_format((float) ($transaction->service_fee_percentage ?? 0), 3, ',', '.'), '0'), ',') }}%</td>
                                </tr>
                                <tr>
                                    <td class="field">Zona Waktu</td>
                                    <td>{{ $transaction->timezone ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="field">Tanggal Transaksi</td>
                                    <td>{{ optional($transaction->created_at)->format('d/m/Y H:i:s') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="field">Tanggal Bisnis</td>
                                    <td>{{ $transaction->date ? \Carbon\Carbon::parse($transaction->date)->format('d/m/Y') : '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="field">Jam Bisnis</td>
                                    <td>{{ $transaction->time ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="field">Catatan</td>
                                    <td>{!! $transaction->notes ? nl2br(e($transaction->notes)) : '<span class="text-muted">Tidak ada catatan</span>' !!}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="panel panel-inverse mb-4">
                <div class="panel-heading">
                    <h4 class="panel-title"><i class="fa fa-microchip me-2"></i>Perangkat Transaksi</h4>
                </div>
                <div class="panel-body">
                    @if ($transaction->deviceTransactions->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Device Code</th>
                                        <th>Nama Device</th>
                                        <th>Service</th>
                                        <th>Status</th>
                                        <th>Aktivasi</th>
                                        <th>Bypass</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transaction->deviceTransactions as $deviceTransaction)
                                        <tr>
                                            <td><strong>{{ $deviceTransaction->device_code }}</strong></td>
                                            <td>{{ $deviceTransaction->device?->name ?? '-' }}</td>
                                            <td>{{ $deviceTransaction->service_type ?? '-' }}</td>
                                            <td>
                                                <span class="label {{ $deviceTransaction->status ? 'label-success' : 'label-default' }}">
                                                    {{ $deviceTransaction->status ? 'Aktif' : 'Tidak Aktif' }}
                                                </span>
                                            </td>
                                            <td>{{ optional($deviceTransaction->activated_at)->format('d/m/Y H:i:s') ?? '-' }}</td>
                                            <td>{{ optional($deviceTransaction->bypass_activation)->format('d/m/Y H:i:s') ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fa fa-info-circle"></i> Tidak ada perangkat yang tercatat pada transaksi ini.
                        </div>
                    @endif
                </div>
            </div>

            @if ($transaction->payments->isNotEmpty())
                <div class="panel panel-inverse mb-4">
                    <div class="panel-heading">
                        <h4 class="panel-title"><i class="fa fa-credit-card me-2"></i>Informasi Pembayaran</h4>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nominal</th>
                                        <th>Waktu Pembayaran</th>
                                        <th>Metode</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transaction->payments as $payment)
                                        <tr>
                                            <td>#{{ $payment->id }}</td>
                                            <td>Rp {{ number_format($payment->amount ?? 0, 0, ',', '.') }}</td>
                                            <td>{{ $payment->payment_time ? \Carbon\Carbon::parse($payment->payment_time)->format('d/m/Y H:i:s') : (optional($payment->created_at)->format('d/m/Y H:i:s') ?? '-') }}</td>
                                            <td>{{ $payment->payment_method ?? '-' }}</td>
                                            <td>{{ $payment->notes ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-xl-4">
            <div class="panel panel-inverse mb-4">
                <div class="panel-heading">
                    <h4 class="panel-title"><i class="fa fa-briefcase me-2"></i>Owner / Brand</h4>
                </div>
                <div class="panel-body">
                    <table class="table table-profile mb-0">
                        <tbody>
                            <tr>
                                <td class="field">Brand</td>
                                <td>
                                    @if ($transaction->owner)
                                        <a href="{{ route('admin.owners.show', $transaction->owner) }}">
                                            <strong>{{ $transaction->owner->brand_name }}</strong>
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="field">Kode</td>
                                <td>{{ $transaction->owner->code ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="field">Pemilik</td>
                                <td>{{ $transaction->owner->user->name ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel panel-inverse mb-4">
                <div class="panel-heading">
                    <h4 class="panel-title"><i class="fa fa-store me-2"></i>Outlet</h4>
                </div>
                <div class="panel-body">
                    @if ($transaction->outlet)
                        <table class="table table-profile mb-0">
                            <tbody>
                                <tr>
                                    <td class="field">Outlet</td>
                                    <td>
                                        <a href="{{ route('admin.outlets.show', $transaction->outlet) }}">
                                            <strong>{{ $transaction->outlet->outlet_name }}</strong>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field">Alamat</td>
                                    <td>{{ $transaction->outlet->address ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="fa fa-exclamation-triangle"></i> Outlet tidak terhubung langsung ke transaksi ini.
                        </div>
                    @endif
                </div>
            </div>

            @if ($transaction->qrisTransaction)
                <div class="panel panel-inverse mb-4">
                    <div class="panel-heading">
                        <h4 class="panel-title"><i class="fa fa-qrcode me-2"></i>QRIS</h4>
                    </div>
                    <div class="panel-body">
                        <table class="table table-profile mb-0">
                            <tbody>
                                <tr>
                                    <td class="field">QR ID</td>
                                    <td>#{{ $transaction->qrisTransaction->id }}</td>
                                </tr>
                                <tr>
                                    <td class="field">Payment URL</td>
                                    <td>
                                        @if ($transaction->qrisTransaction->payment_url)
                                            <a href="{{ $transaction->qrisTransaction->payment_url }}" target="_blank" rel="noopener">
                                                Buka Payment URL
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
