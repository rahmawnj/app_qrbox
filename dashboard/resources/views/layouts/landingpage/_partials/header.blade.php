<style>
    /* Styling navbar */
    .navbar {
        box-shadow: var(--default-shadow);
        background-color: var(--white-bg);
        padding-top: 0;
        padding-bottom: 2px;
        transition: all 0.3s ease;
    }

    /* --- Style Navbar lain yang Anda berikan --- */
    .navbar-brand {
        font-weight: 600;
        color: var(--primary-color) !important;
        display: flex;
        align-items: center;
        font-size: 1.3rem;
    }

    .nav-item {
        margin: 0 2px;
    }

    .navbar-brand img {
        height: 35px;
        margin-right: 8px;
    }

    .navbar-toggler {
        border: none;
        padding: 0;
    }

    .navbar-toggler:focus {
        box-shadow: none;
    }

    .navbar-nav .nav-link {
        color: var(--primary-color); /* Teks biru saat tidak aktif */
        font-weight: 500;
        padding: 4px 5px;
        border-radius: 8px;
        transition: all 0.3s ease;
        position: relative; /* Diperlukan untuk pseudo-element underline */
    }

    .navbar-nav .nav-link:hover {
        color: var(--primary-color); /* Teks tetap biru saat hover */
        background-color: transparent; /* Menghilangkan background hover */
    }

    .navbar-nav .nav-link::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        width: 0;
        height: 2px;
        background-color: var(--secondary-color); /* Garis underline kuning */
        transition: width 0.3s ease-in-out, left 0.3s ease-in-out;
    }

    .navbar-nav .nav-link:hover::after {
        width: 100%;
        left: 0;
    }

    .navbar-nav .nav-link.active {
        color: #000; /* Teks hitam agar lebih jelas */
        background-color: var(--secondary-color); /* Background kuning saat aktif */
        font-weight: 600;
    }

    .navbar-nav .nav-link.active::after,
    .navbar-nav .nav-link.active:hover::after {
        width: 0; /* Menghilangkan underline saat link aktif */
    }

    .navbar-nav .dropdown-toggle {
        display: flex;
        align-items: center;
    }

    .navbar-nav .dropdown-toggle i {
        font-size: 1.2rem;
        margin-right: 5px;
    }

    .dropdown-menu {
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        border: none;
        padding: 10px 0;
    }

    .dropdown-item {
        padding: 10px 20px;
        color: var(--text-color);
        transition: background-color 0.2s ease;
    }

    .dropdown-item:hover {
        background-color: var(--light-bg);
        color: var(--primary-color);
    }

    .dropdown-divider {
        margin: 5px 0;
    }

    /* Perbaikan pada gaya header */
    #main-navbar.navbar {
        background-color: #fff !important;
    }

    .header-custom-style {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
    }

    .badge-notification-blink {
        animation: blink-animation 1.5s infinite;
    }

    @keyframes blink-animation {
        0%, 100% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }
    }

    .dropdown-item {
        white-space: normal;
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
    }

    .bg-light-hover {
        background-color: #f8f9fa; /* Warna latar belakang untuk notifikasi belum dibaca */
    }
</style>

<header>
    <nav id="main-navbar" class="navbar navbar-expand-lg navbar-light fixed-top header-custom-style">
        <div class="container-fluid d-flex flex-column align-items-center">
            <div class="navbar-top-row d-flex justify-content-between align-items-center w-100 py-1">
                <a class="navbar-brand me-auto" href="#">
                    <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" class="d-inline-block align-text-top me-2">
                    <span class="fw-bold">LAUNDRY APP</span>
                </a>

                <button class="navbar-toggler me-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <ul class="navbar-nav mb-0">
                    @auth
                        {{-- Dropdown Notifikasi --}}
                        <li class="nav-item dropdown me-2">
                            <a class="nav-link" href="#" id="navbarNotificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-notification-4-line fs-5"></i>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                    <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle-x badge-notification-blink">
                                        {{ auth()->user()->unreadNotifications->count() }}
                                    </span>
                                @endif
                            </a>
                            <div class="dropdown-menu dropdown-menu-end p-2" aria-labelledby="navbarNotificationDropdown" style="width: 350px;">
                                <div class="d-flex justify-content-between align-items-center mb-2 px-2">
                                    <h6 class="mb-0 fw-bold">Notifikasi</h6>
                                </div>
                                <div class="dropdown-divider"></div>
                                @forelse(auth()->user()->notifications->take(5) as $notification)
                                    <a class="dropdown-item d-flex align-items-start {{ is_null($notification->read_at) ? 'bg-light-hover' : '' }}" href="{{ $notification->data['url'] }}" onclick="markAsRead(this, '{{ $notification->id }}')">
                                        <div class="flex-grow-1">
                                            <div class="d-flex w-100 justify-content-between">
                                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                                @if(is_null($notification->read_at))
                                                    <span class="text-danger fw-bold" style="font-size: 0.7em;">Baru</span>
                                                @endif
                                            </div>
                                            @if(isset($notification->data['title']))
                                                <h6 class="mb-0">{{ $notification->data['title'] }}</h6>
                                            @endif
                                            <p class="mb-0 text-truncate">{{ Str::limit($notification->data['message'], 33, '...') }}</p>
                                        </div>
                                    </a>
                                @empty
                                    <div class="dropdown-item text-center text-muted">Tidak ada notifikasi baru.</div>
                                @endforelse
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-center text-primary" href="{{ route('home.member.notifications.list') }}">Lihat Semua</a>
                            </div>
                        </li>

                        {{-- Dropdown Akun --}}
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarUserDropdown"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="{{ Auth::user()->avatar_url ?? asset('assets/img/default-user.png') }}"
                                    alt="User Avatar" class="rounded-circle me-2"
                                    style="width: 28px; height: 28px; object-fit: cover;">
                                <div class="d-flex flex-column align-items-start d-none d-lg-flex">
                                    <span style="font-size: 0.9rem;">{{ Auth::user()->name }}</span>
                                    <small class="text-muted" style="font-size: 0.75rem;">{{ Auth::user()->email }}</small>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUserDropdown">
                                <li>
                                    <div class="dropdown-item-text text-center border-bottom">
                                        <img src="{{ Auth::user()->avatar_url ?? asset('assets/img/default-user.png') }}"
                                            alt="User Avatar" class="rounded-circle mb-1"
                                            style="width: 60px; height: 60px; object-fit: cover;">
                                        <p class="mb-0 fw-bold">{{ Auth::user()->name }}</p>
                                        <small class="text-muted">{{ Auth::user()->email }}</small>
                                    </div>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('dashboard') }}">
                                        <i class="ri-dashboard-line me-2"></i> Dashboard
                                    </a>
                                </li>
                                @if (auth()->user()->role == 'member')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('home.member.profile') }}">
                                            <i class="ri-user-line me-2"></i> Profile Saya
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('home.member.orders') }}">
                                            <i class="ri-box-3-line me-2"></i> Pesanan Saya
                                        </a>
                                    </li>
                                @endif
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger"><i
                                                class="fas fa-sign-out-alt me-2"></i> Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-user-3-line"></i> Akun
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="{{ route('login') }}">Login</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="{{ route('register') }}">Register</a></li>
                            </ul>
                        </li>
                    @endauth
                </ul>

            </div>

            <div class="navbar-bottom-row collapse navbar-collapse justify-content-center w-100" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs('home') ? 'active' : '' }}"
                            {{ Request::routeIs('home') ? 'aria-current="page"' : '' }}
                            href="{{ route('home') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs('home.outlets') ? 'active' : '' }}"
                            {{ Request::routeIs('home.outlets') ? 'aria-current="page"' : '' }}
                            href="{{ route('home.outlets') }}">Outlet</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs('home.brands') ? 'active' : '' }}"
                            {{ Request::routeIs('home.brands') ? 'aria-current="page"' : '' }}
                            href="{{ route('home.brands') }}">Brand</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>
<script>
    function adjustMainContentPadding() {
        const navbar = document.getElementById('main-navbar');
        const mainContent = document.querySelector('.main-content');
        if (navbar && mainContent) {
            const navbarHeight = navbar.offsetHeight;
            mainContent.style.paddingTop = `${navbarHeight}px`;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        adjustMainContentPadding();

        // Ambil token CSRF dari meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const notificationCountBadge = document.querySelector('#navbarNotificationDropdown .badge');

        // Fungsi untuk menandai satu notifikasi sudah dibaca
        window.markAsRead = function(element, notificationId) {
            // Hentikan navigasi langsung
            event.preventDefault();

            if (!notificationId || !csrfToken) {
                console.error('Notification ID or CSRF token is missing.');
                window.location.href = element.href;
                return;
            }

            fetch(`{{ route('home.member.notifications.markAsRead') }}?notification=${notificationId}`, {
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
                .then(data => {
                    console.log('Notifikasi berhasil ditandai sudah dibaca.', data.message);
                    // Hapus kelas warna
                    element.classList.remove('bg-light-hover');
                    // Hapus badge "Baru"
                    const newBadge = element.querySelector('.text-danger.fw-bold');
                    if (newBadge) {
                        newBadge.remove();
                    }
                    // Kurangi jumlah notifikasi yang belum dibaca
                    let currentCount = parseInt(notificationCountBadge.textContent, 10);
                    if (!isNaN(currentCount) && currentCount > 0) {
                        currentCount--;
                        notificationCountBadge.textContent = currentCount;
                        if (currentCount === 0) {
                            notificationCountBadge.remove();
                        }
                    }
                })
                .catch(error => {
                    console.error('Terjadi kesalahan:', error);
                })
                .finally(() => {
                    // Lanjutkan ke URL setelah proses fetch
                    window.location.href = element.href;
                });
        };
    });

    window.addEventListener('resize', adjustMainContentPadding);
</script>
