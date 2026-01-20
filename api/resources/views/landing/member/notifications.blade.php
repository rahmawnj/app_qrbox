@extends('layouts.landingpage.dashboard-app')

@section('title', 'Daftar Notifikasi Saya')
@section('header_title', 'Pemberitahuan Terbaru')

@push('styles')
    <style>
        .dashboard-card {
            border-radius: 0.75rem; /* Ukuran radius lebih kecil */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); /* Shadow lebih tipis */
            border: none;
            overflow: hidden;
            background-color: #ffffff;
            transition: all 0.2s ease-in-out;
        }

        .notification-card {
            cursor: pointer;
            padding: 1rem 1.25rem; /* Padding lebih kecil */
            border-left: 5px solid transparent; /* Border default transparan */
        }

        .notification-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
        }

        /* Styling untuk notifikasi belum dibaca */
        .notification-unread {
            background-color: #fcf8e3; /* Warna latar yang lebih soft */
            border-left-color: #ffc107;
        }

        .notification-read {
            background-color: #f8f9fa;
        }

        .notification-read p {
            color: #6c757d; /* Teks abu-abu untuk notifikasi yang sudah dibaca */
        }

        .notification-title {
            font-size: 1rem; /* Ukuran font lebih kecil */
            font-weight: 600;
            color: #344767;
        }

        .notification-message {
            font-size: 0.875rem; /* Ukuran font lebih kecil */
            color: #6c757d;
            margin-bottom: 0;
        }

        .notification-time {
            font-size: 0.75rem;
            color: #999;
        }

        .badge-new {
            font-size: 0.65rem; /* Ukuran badge lebih kecil */
            font-weight: 700;
            padding: 0.3em 0.6em;
            border-radius: 50rem;
        }

        /* Styling untuk modal */
        .modal-content {
            border-radius: 1rem;
        }

        .modal-body .list-unstyled li {
            font-size: 0.9rem;
        }

        .modal-body .fw-bold {
            font-size: 0.95rem;
        }

        .empty-state-card {
            border: 2px dashed #e9ecef;
            background-color: #fcfcfc;
        }
    </style>
@endpush

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="card-title-custom mb-2"><i class="fas fa-bell me-2"></i> Notifikasi Saya</h3>
            <p class="text-muted">Lihat semua pemberitahuan dari sistem dan pesanan Anda.</p>
        </div>
    </div>

    @if($notifications->isNotEmpty())
        <div class="row">
            @foreach($notifications as $notification)
                @php
                    $isRead = $notification->read_at;
                    $data = $notification->data;
                    $notificationClass = $isRead ? 'notification-read' : 'notification-unread';
                @endphp
                <div class="col-12 mb-3">
                    <div class="card dashboard-card notification-card {{ $notificationClass }}"
                         data-bs-toggle="modal" data-bs-target="#notificationDetailModal{{ $notification->id }}">
                        <div class="card-body p-0">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="notification-title mb-0">
                                            {{ $data['title'] ?? 'Notifikasi Baru' }}
                                        </h6>
                                        @if (!$isRead)
                                            <span class="badge bg-warning badge-new">BARU</span>
                                        @endif
                                    </div>
                                    <p class="notification-message mb-1 text-truncate">
                                        {{ $data['message'] ?? 'Tidak ada deskripsi.' }}
                                    </p>
                                    <small class="notification-time">
                                        <i class="fas fa-clock me-1"></i> {{ $notification->created_at->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Detail Notifikasi --}}
                <div class="modal fade" id="notificationDetailModal{{ $notification->id }}" tabindex="-1" aria-labelledby="notificationDetailModalLabel{{ $notification->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content dashboard-card">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold" id="notificationDetailModalLabel{{ $notification->id }}">Detail Notifikasi</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <ul class="list-unstyled">
                                    <li class="d-flex justify-content-between mb-2">
                                        <span class="fw-bold"><i class="fas fa-heading me-2"></i>Judul:</span>
                                        <span>{{ $data['title'] ?? 'N/A' }}</span>
                                    </li>
                                    <li class="d-flex justify-content-between mb-2">
                                        <span class="fw-bold"><i class="fas fa-info-circle me-2"></i>Status:</span>
                                        <span><span class="badge {{ $isRead ? 'bg-secondary' : 'bg-warning' }}">{{ $isRead ? 'Sudah Dibaca' : 'Belum Dibaca' }}</span></span>
                                    </li>
                                    <li class="d-flex justify-content-between mb-2">
                                        <span class="fw-bold"><i class="fas fa-calendar-alt me-2"></i>Tanggal:</span>
                                        <span>{{ \Carbon\Carbon::parse($notification->created_at)->translatedFormat('d F Y H:i') }}</span>
                                    </li>
                                </ul>
                                <hr>
                                <p class="fw-bold text-color-dark mb-2"><i class="fas fa-file-alt me-2"></i>Isi Pesan:</p>
                                <p class="text-muted">{{ $data['message'] ?? 'Tidak ada deskripsi lengkap.' }}</p>
                            </div>
                            <div class="modal-footer">
                                @if (isset($data['url']))
                                    <a href="{{ $data['url'] }}" class="btn btn-primary rounded-pill">Lihat Pesanan</a>
                                @endif
                                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="row">
            <div class="col-12">
                <div class="dashboard-card empty-state-card text-center py-5">
                    <i class="fas fa-bell-slash fa-4x text-muted mb-4"></i>
                    <h4 class="fw-bold text-color-dark mb-3">Tidak Ada Notifikasi</h4>
                    <p class="text-muted mb-4">
                        Sepertinya kotak masuk notifikasi Anda masih kosong.
                    </p>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ambil token CSRF dari meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            document.querySelectorAll('.notification-card').forEach(card => {
                card.addEventListener('click', function() {
                    const notificationId = this.dataset.bsTarget.replace('#notificationDetailModal', '');
                    const isRead = this.classList.contains('notification-read');

                    if (!isRead) {
                        fetch(`{{ route('notifications.markAsRead') }}?notification=${notificationId}`, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            if (response.ok) {
                                return response.json();
                            }
                            throw new Error('Gagal menandai notifikasi sebagai sudah dibaca.');
                        })
                        .then(() => {
                            // Perbarui tampilan notifikasi setelah berhasil
                            const newBadge = this.querySelector('.badge');
                            if (newBadge) {
                                newBadge.remove();
                            }
                            this.classList.remove('notification-unread');
                            this.classList.add('notification-read');
                            console.log('Notifikasi berhasil ditandai sudah dibaca.');
                        })
                        .catch(error => {
                            console.error('Terjadi kesalahan:', error);
                        });
                    }
                });
            });
        });
    </script>
@endpush
