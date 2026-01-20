<div id="header" class="app-header app-header-inverse">
    <div class="navbar-header">
        @php
            // 1. Inisialisasi Default Global
            $logoSrc = asset('assets/img/logo.png');
            $brandName = config('app.name', 'Laundry App');
            $isLoggedIn = Auth::check() || session('is_hardcoded_admin');

            if ($isLoggedIn) {
                $user = Auth::user();

                // 2. Logika untuk Admin atau Member (Selalu Default)
                // Jika hardcoded admin (session), Auth::user() mungkin null, jadi kita cek null safe
                if (!$user || $user->role === 'admin' || $user->role === 'member') {
                    $logoSrc = asset('assets/img/logo.png');
                    $brandName = config('app.name', 'Laundry App');
                }
                // 3. Logika untuk Owner/Outlet (Mengambil data Brand)
                else {
                    $brand = getBrand(); // Helper kamu
                    if ($brand) {
                        $logoSrc = $brand->brand_logo ? asset($brand->brand_logo) : asset('assets/img/logo.png');
                        $brandName = $brand->brand_name ?? 'Laundry Brand';
                    } else {
                        // Fallback jika role bukan admin/member tapi data brand tidak ditemukan
                        $logoSrc = asset('assets/img/logo.png');
                        $brandName = config('app.name', 'Laundry App');
                    }
                }
            }
        @endphp

        <a href="{{ $isLoggedIn ? '#' : url('/') }}" class="navbar-brand">
            <img height="25" style="margin-right: 5px; border-radius: 10%;" src="{{ $logoSrc }}" alt="Brand Logo">
            <b>{{ $brandName }}</b>
        </a>

        <button type="button" class="navbar-mobile-toggler" data-toggle="app-sidebar-mobile">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
    </div>

    <div class="navbar-nav">
        @if(Auth::check())
            {{-- <div class="navbar-item dropdown">
                <a href="#" data-bs-toggle="dropdown" class="navbar-link dropdown-toggle icon">
                    <i class="fa fa-bell"></i>
                    @php
                        $unreadNotifications = Auth::user()->unreadNotifications;
                        $allNotifications = Auth::user()->notifications;
                        $count = $unreadNotifications->count();
                    @endphp
                    @if ($count > 0)
                        <span class="badge">{{ $count }}</span>
                    @endif
                </a>
                <div class="dropdown-menu media-list dropdown-menu-end">
                    <div class="dropdown-header">NOTIFICATIONS ({{ $allNotifications->count() }})</div>
                    @php $latestNotifications = $allNotifications->take(5); @endphp
                    @forelse ($latestNotifications as $notification)
                        @php $data = $notification->data; @endphp
                        <a href="{{ $data['url'] ?? '#' }}" class="dropdown-item media"
                           style="{{ $notification->read_at ? '' : 'background-color: #ffe4e1;' }}"
                           data-notification-id="{{ $notification->id }}">
                            <div class="media-body">
                                <h6 class="media-heading">{{ $data['title'] ?? 'Notifikasi Baru' }}</h6>
                                <p class="text-truncate">{{ $data['message'] ?? 'Tidak ada deskripsi.' }}</p>
                                <div class="text-muted fs-10px">{{ $notification->created_at->diffForHumans() }}</div>
                            </div>
                        </a>
                    @empty
                        <div class="dropdown-item text-center text-muted p-20px">
                            Tidak ada notifikasi.
                        </div>
                    @endforelse
                    <div class="dropdown-footer text-center">
                        <a href="{{ route('notifications.list') }}" class="text-decoration-none">Lihat Semua Notifikasi</a>
                    </div>
                </div>
            </div> --}}

            <div class="navbar-item navbar-user dropdown">
                <a href="#" class="navbar-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
                    <img src="{{ asset(Auth::user()->image ?? 'assets/img/default-user.png') }}" alt="" />
                    <div class="d-none d-md-inline text-start ms-2">
                        <div>{{ Auth::user()->name }}</div>
                        <small class="text-white">{{ Auth::user()->role }}</small>
                    </div>
                    <b class="caret ms-6px"></b>
                </a>
                <div class="dropdown-menu dropdown-menu-end me-1">
                    <a href="{{ route('profile.form') }}" class="dropdown-item">Edit Profile</a>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item">Log Out</button>
                    </form>
                </div>
            </div>
        @else
            {{-- Tampilan jika admin config/hardcoded login tanpa object User --}}
            <div class="navbar-item navbar-user dropdown">
                <a href="#" class="navbar-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
                    <img src="{{ asset('assets/img/default-user.png') }}" alt="" />
                    <div class="d-none d-md-inline text-start ms-2">
                        <div>{{ session('admin_data.name') ?? 'Super Admin' }}</div>
                        <small class="text-white">Administrator</small>
                    </div>
                    <b class="caret ms-6px"></b>
                </a>
                <div class="dropdown-menu dropdown-menu-end me-1">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item">Log Out</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const notifications = document.querySelectorAll('.dropdown-item.media[data-notification-id]');
        notifications.forEach(notificationLink => {
            notificationLink.addEventListener('click', function(event) {
                event.preventDefault();
                const notificationId = this.dataset.notificationId;
                const url = this.getAttribute('href');

                fetch(`{{ route('notifications.markAsRead') }}?notification=${notificationId}`, {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                })
                .finally(() => { window.location.href = url; });
            });
        });
    });
</script>
