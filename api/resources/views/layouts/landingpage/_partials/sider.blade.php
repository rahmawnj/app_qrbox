<div class="dashboard-left-panel">
    <div class="profile-header">
        <img src="{{ Auth::user()->profile_photo_url ?? asset('assets/img/default-user.png') }}" alt="User Photo"
            class="img-fluid rounded-circle"
            style="width: 70px; height: 70px; object-fit: cover; border: 2px solid var(--primary-color);">
        <h5>{{ Auth::user()->name ?? 'Member' }}</h5>
        <p>{{ Auth::user()->email ?? 'email@example.com' }}</p>
    </div>
    <nav class="nav-menu">
        <ul>
            <li>
                <a href="{{ route('home.member.dashboard') }}"
                    class="{{ Request::routeIs('home.member.dashboard') ? 'active' : '' }}">
                    <i class="ri-dashboard-line"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('home.member.profile') }}"
                    class="{{ Request::routeIs('home.member.profile') ? 'active' : '' }}">
                    <i class="ri-user-line"></i> My Profile
                </a>
            </li>
            <li>
                <a href="{{ route('home.member.scan') }}"
                    class="{{ Request::routeIs('home.member.scan') ? 'active' : '' }}">
                    <i class="ri-camera-line"></i> Scan Camera
                </a>
            </li>
            <li>
                <a href="{{ route('home.member.brands') }}"
                    class="{{ Request::routeIs('home.member.brands') ? 'active' : '' }}">
                    <i class="ri-store-line"></i> Berlangganan
                </a>
            </li>
            <li>
                <a href="{{ route('home.member.orders') }}"
                    class="{{ Request::routeIs('home.member.orders') ? 'active' : '' }}">
                    <i class="ri-book-line"></i> Layanan
                    @php
                        $orders = auth()
                            ->user()
                            ->member->transactions()
                            ->where('status', 'pending') // Ganti 'pending' dengan status yang relevan
                            ->whereHas('payments', function ($query) {
                                $query->where('status', 'success');
                            })
                            ->count();
                    @endphp
                    @if ($orders > 0)
                        <span class="badge bg-secondary ms-1">{{ $orders }}</span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('home.member.transactions') }}"
                    class="{{ Request::routeIs('home.member.transactions') ? 'active' : '' }}">
                    <i class="ri-bank-card-line"></i> Transaksi
                </a>
            </li>

            <li>
                <a href="{{ route('home.member.topup.histories') }}"
                    class="{{ Request::routeIs('home.member.topup.histories') ? 'active' : '' }}">
                    <i class="ri-history-line"></i> Riwayat Top Up
                </a>
            </li>
        </ul>
    </nav>
</div>

<style>
    .dashboard-left-panel {
        /* This is the 'sidebar' you referred to */
        flex-shrink: 0;
        width: 280px;
        /* Fixed width for the menu panel */
        background-color: var(--panel-bg);
        /* White panel background */
        border-radius: 12px;
        padding: 20px;
        height: fit-content;
        /* Important: makes it not full height */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        /* Lighter shadow for light theme */
    }

    .dashboard-left-panel .profile-header {
        text-align: center;
        padding-bottom: 20px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        /* Darker border for light theme */
        margin-bottom: 20px;
    }

    .dashboard-left-panel .profile-header img {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--primary-color);
        margin-bottom: 10px;
    }

    .dashboard-left-panel .profile-header h5 {
        color: var(--text-color-light);
        font-size: 1.1rem;
        margin-bottom: 5px;
    }

    .dashboard-left-panel .profile-header p {
        font-size: 0.85rem;
        color: var(--text-color-muted-dark);
    }

    .dashboard-left-panel .nav-menu ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .dashboard-left-panel .nav-menu ul li {
        margin-bottom: 8px;
    }

    .dashboard-left-panel .nav-menu ul li a {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        color: var(--text-color-muted-dark);
        text-decoration: none;
        border-radius: 8px;
        transition: background-color 0.2s ease, color 0.2s ease;
        font-weight: 500;
    }

    .dashboard-left-panel .nav-menu ul li a i {
        margin-right: 12px;
        font-size: 1.1rem;
    }

    .dashboard-left-panel .nav-menu ul li a:hover,
    .dashboard-left-panel .nav-menu ul li a.active {
        background-color: rgba(43, 108, 176, 0.1);
        color: var(--primary-color);
    }

    .dashboard-left-panel .nav-menu ul li a.text-danger:hover {
        background-color: rgba(220, 53, 69, 0.1);
    }

    /* Responsive Adjustments untuk sidebar */
    @media (max-width: 991.98px) {
        .dashboard-left-panel {
            width: 100%;
            height: auto;
            order: -1;
            padding: 15px;
        }

        .dashboard-left-panel .profile-header {
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .dashboard-left-panel .nav-menu ul {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 5px;
        }

        .dashboard-left-panel .nav-menu ul li {
            margin-bottom: 0;
        }

        .dashboard-left-panel .nav-menu ul li a {
            font-size: 0.85rem;
            padding: 8px 10px;
            flex-direction: column;
            text-align: center;
        }

        .dashboard-left-panel .nav-menu ul li a i {
            margin-right: 0;
            margin-bottom: 5px;
        }
    }
</style>
