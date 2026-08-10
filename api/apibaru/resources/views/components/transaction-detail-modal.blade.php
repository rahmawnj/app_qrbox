@props(['transaction'])

@php

    // Define badge classes based on status
    $statusBadgeClass = '';
    switch ($transaction->status) {
        case 'pending':
            $statusBadgeClass = 'badge-pending';
            break;
        case 'success':
            $statusBadgeClass = 'badge-success';
            break;
        case 'failed':
            $statusBadgeClass = 'badge-failed';
            break;
        default:
            $statusBadgeClass = 'bg-secondary'; // Fallback
            break;
    }

    // Logika baru untuk badge tipe channel dan display name
    $channelTypeDisplayName = '';
    $channelTypeBadgeClass = '';
    switch ($transaction->channel_type) { // Menggunakan channel_type dari tabel transactions
        case 'drop_off':
            $channelTypeDisplayName = 'Drop-off';
            $channelTypeBadgeClass = 'badge-drop-off';
            break;
        case 'self_service':
            // Untuk self-service, cek apakah ada member_id untuk membedakan QRIS atau Member
            if ($transaction->member_id) {
                $channelTypeDisplayName = 'Self-service (Member)';
            } else {
                $channelTypeDisplayName = 'Self-service (QRIS)'; // Asumsi jika tidak ada member_id, itu QRIS
            }
            $channelTypeBadgeClass = 'badge-self-service';
            break;
        default:
            $channelTypeDisplayName = ucfirst($transaction->channel_type); // Fallback
            $channelTypeBadgeClass = 'bg-info'; // Fallback badge
            break;
    }
@endphp

<div class="modal fade" id="transactionDetailModal{{ $transaction->id }}" tabindex="-1"
    aria-labelledby="transactionDetailModalLabel{{ $transaction->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionDetailModalLabel{{ $transaction->id }}">Detail Transaksi
                    #{{ $transaction->order_id }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <dl class="row">
                    <dt class="col-sm-4">ID Nota:</dt>
                    <dd class="col-sm-8">{{ $transaction->order_id }}</dd>

                    <dt class="col-sm-4">Pemilik:</dt>
                    <dd class="col-sm-8">{{ $transaction->owner->user->name ?? '-' }}
                        ({{ $transaction->owner->brand_name ?? '-' }})
                    </dd>

                    <dt class="col-sm-4">Outlet:</dt>
                    <dd class="col-sm-8">{{ $transaction->outlet->outlet_name ?? '-' }}</dd>

                    <dt class="col-sm-4">Total Jumlah:</dt>
                    <dd class="col-sm-8">
                        Rp{{ number_format($transaction->amount, 0, ',', '.') }}</dd>

                    <dt class="col-sm-4">Tipe Channel:</dt>
                    <dd class="col-sm-8">
                        <span class="badge badge-status {{ $channelTypeBadgeClass }}">
                            {{ $channelTypeDisplayName }}
                        </span>
                    </dd>

                    <dt class="col-sm-4">Status Transaksi:</dt>
                    <dd class="col-sm-8">
                        <span class="badge badge-status {{ $statusBadgeClass }}">
                            {{ ucfirst($transaction->status) }}
                        </span>
                    </dd>

                    <dt class="col-sm-4">Waktu Transaksi:</dt>
                    <dd class="col-sm-8">
                        {{ \Carbon\Carbon::parse($transaction->created_at)->setTimezone($transaction->timezone)->format('d F Y H:i:s') }}
                        ({{ strtoupper($transaction->timezone) }})
                    </dd>

                   
                    {{-- Detail spesifik Drop-off Transaction --}}
                    @if ($transaction->channel_type === 'drop_off' && $transaction->dropOffTransaction)
                        <h6 class="mt-4 border-top pt-3">Detail Drop-off:</h6>
                        <dt class="col-sm-4">Layanan:</dt>
                        <dd class="col-sm-8">
                            {{ $transaction->dropOffTransaction->service->name ?? '-' }}
                            (Rp{{ number_format($transaction->dropOffTransaction->service_price ?? 0, 0, ',', '.') }})
                        </dd>
                        @if (!empty($transaction->dropOffTransaction->addons))
                            <dt class="col-sm-4">Tambahan:</dt>
                            <dd class="col-sm-8">
                                <ul>
                                    @foreach (json_decode($transaction->dropOffTransaction->addons, true) as $addon) {{-- Pastikan addons di-decode jika disimpan sebagai JSON string --}}
                                        <li>{{ $addon['name'] ?? '-' }}
                                            (Rp{{ number_format($addon['price'] ?? 0, 0, ',', '.') }})
                                        </li>
                                    @endforeach
                                </ul>
                            </dd>
                        @endif
                        <dt class="col-sm-4">Metode Pengambilan:</dt>
                        <dd class="col-sm-8">{{ ucfirst(str_replace('_', ' ', $transaction->dropOffTransaction->pickup_method ?? '-')) }}</dd>
                        <dt class="col-sm-4">Progress Order:</dt>
                        <dd class="col-sm-8">{{ ucfirst($transaction->dropOffTransaction->progress ?? '-') }}</dd>
                        <dt class="col-sm-4">Kasir:</dt>
                        <dd class="col-sm-8">{{ $transaction->dropOffTransaction->cashier_name ?? '-' }}</dd>
                        <dt class="col-sm-4">Catatan:</dt>
                        <dd class="col-sm-8">{{ $transaction->dropOffTransaction->notes ?? '-' }}</dd>
                    @endif

                    {{-- Detail spesifik Self-service Transaction --}}
                    @if ($transaction->channel_type === 'self_service' && $transaction->selfServiceTransaction)
                        <h6 class="mt-4 border-top pt-3">Detail Self-service:</h6>
                        <dt class="col-sm-4">Kode Perangkat:</dt>
                        <dd class="col-sm-8">{{ $transaction->selfServiceTransaction->device_code ?? '-' }}</dd>
                        <dt class="col-sm-4">Jumlah Percobaan Ulang:</dt>
                        <dd class="col-sm-8">{{ $transaction->selfServiceTransaction->device_status ?? '-' }}</dd>
                        <dt class="col-sm-4">Terakhir Dicoba Pada:</dt>
                        <dd class="col-sm-8">
                            {{ $transaction->selfServiceTransaction->last_attempt_at ? \Carbon\Carbon::parse($transaction->selfServiceTransaction->last_attempt_at)->setTimezone($transaction->timezone)->format('d F Y H:i:s') : '-' }}
                        </dd>
                        <dt class="col-sm-4">Pilihan Pengguna:</dt>
                        <dd class="col-sm-8">{{ $transaction->selfServiceTransaction->service_type ?? '-' }}</dd>
                    @endif

                    {{-- Breakdown Layanan Perangkat (Device Transactions) --}}
                    @if ($transaction->deviceTransactions->isNotEmpty())
                        <h6 class="mt-4 border-top pt-3">Detail Layanan Perangkat:</h6>
                        <div class="row">
                            @foreach ($transaction->deviceTransactions as $dt)
                                <div class="col-md-6 mb-3">
                                    <div class="card card-body p-3">
                                        <h7 class="card-title text-primary mb-1">{{ $dt->service_type }}</h7>
                                        <p class="card-text text-sm mb-1">
                                            Status Mesin:
                                            <span class="badge bg-{{ $dt->status == 0 ? 'success' : 'warning' }}">
                                                {{ $dt->status == 0 ? 'Sudah Dijalankan' : 'Belum Dijalankan' }}
                                            </span>
                                        </p>
                                        <p class="card-text text-sm mb-1">
                                            Waktu Kadaluarsa:
                                            {{ $dt->activated_at ? \Carbon\Carbon::parse($dt->activated_at)->setTimezone($dt->timezone)->format('d M H:i:s') : '-' }}
                                        </p>
                                        {{-- Jika bypass_activation relevan untuk ditampilkan --}}
                                        @if ($dt->bypass_activation)
                                            <p class="card-text text-sm mb-1">
                                                Bypass Aktivasi:
                                                {{ \Carbon\Carbon::parse($dt->bypass_activation)->setTimezone($dt->timezone)->format('d M H:i:s') }}
                                            </p>
                                        @endif
                                        @if ($dt->notes)
                                            <p class="card-text text-sm mb-0">Catatan: {{ $dt->notes }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mt-4 border-top pt-3">Tidak ada detail layanan perangkat untuk transaksi ini.
                        </p>
                    @endif

                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
