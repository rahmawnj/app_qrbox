@php
    $title = 'Daftar Notifikasi';
@endphp
@extends('layouts.dashboard.app')

@push('styles')
    {{-- Pastikan ada style yang dibutuhkan di sini --}}
@endpush

@section('content')
    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">{{ $title }}</h4>
            <div class="panel-heading-btn">
                <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand"><i class="fa fa-expand"></i></a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload"><i class="fa fa-redo"></i></a>
            </div>
        </div>
        <div class="panel-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Semua Notifikasi ({{ auth()->user()->notifications->count() }})</h5>
                <form action="{{ route('notifications.markAsRead', ['notification' => 'all']) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-sm btn-primary">
                        Tandai Semua Sudah Dibaca
                    </button>
                </form>
            </div>

            <div class="list-group">
                @forelse (auth()->user()->notifications as $notification)
                    @php
                        $data = $notification->data;
                        $isRead = $notification->read_at;
                    @endphp

                    <a href="{{ $data['url'] ?? '#' }}"
                       class="list-group-item list-group-item-action {{ $isRead ? 'text-muted' : 'fw-bold' }}"
                       style="{{ $isRead ? '' : 'background-color: #fffacd;' }}"
                       data-notification-id="{{ $notification->id }}"
                       data-is-read="{{ $isRead ? 'true' : 'false' }}">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">{{ $data['title'] ?? 'Notifikasi Baru' }}</h6>
                            <small class="text-nowrap">{{ $notification->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-1 text-truncate">{{ $data['message'] ?? 'Tidak ada deskripsi.' }}</p>
                    </a>
                @empty
                    <div class="alert alert-info text-center">
                        Tidak ada notifikasi yang ditemukan.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const unreadNotifications = document.querySelectorAll('.list-group-item-action[style*="background-color: #fffacd;"]');

        unreadNotifications.forEach(notificationLink => {
            notificationLink.addEventListener('click', function(event) {
                event.preventDefault();

                const notificationId = this.dataset.notificationId;
                const url = this.getAttribute('href');

                fetch(`{{ route('notifications.markAsRead') }}?notification=${notificationId}`, {
                    method: 'PATCH',
                    headers: {
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
                .then(data => {
                    console.log('Notifikasi berhasil ditandai sudah dibaca.');
                    this.classList.remove('fw-bold');
                    this.classList.add('text-muted');
                    this.style.backgroundColor = '';
                    window.location.href = url;
                })
                .catch(error => {
                    console.error('Terjadi kesalahan:', error);
                    window.location.href = url;
                });
            });
        });
    });
</script>
@endpush
