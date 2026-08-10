<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Laundry App')</title>

    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />

    <style>
        /* =========================================================
 * main.css
 * Deskripsi: Gaya CSS untuk layout utama, global, dan komponen umum.
 * ========================================================= */

        /* Variables - Essential for consistent theming across the app */
        :root {
            --primary-color: #2b6cb0;
            /* A deeper blue, from the footer in the second image */
            --primary-dark-shade: #21598c;
            /* Slightly darker for hover */
            --secondary-color: #ffc107;
            /* Yellow for accents/CTA */
            --secondary-dark-shade: #e0a800;
            --text-dark: #333;
            --text-muted: #666;
            --light-bg: #f0f4f8;
            --white-bg: #ffffff;
            --border-color: #dee2e6;
            --card-hover-shadow: 0 15px 35px rgba(0, 0, 0, 0.18);
            --default-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);

            /* Dashboard Specific Colors (ADJUSTED TO LIGHT THEME) */
            --dashboard-bg: var(--light-bg);
            /* Use light background for dashboard */
            --panel-bg: var(--white-bg);
            /* White background for panels/cards */
            --text-color-light: var(--text-dark);
            /* Dark text on light background */
            --text-color-muted-dark: var(--text-muted);
            /* Muted text for hints/less important info on light */
            --accent-green: #28a745;
            /* Standard Bootstrap success green */
            --accent-red: #dc3545;
            /* Standard Bootstrap danger red */
        }

        /* Base Body Styles - Apply to all pages */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            /* Default light background for non-dashboard pages */
            color: var(--text-dark);
        }

        /* Global Reusable Styles - Utility classes that can be used anywhere */
        .rounded-12 {
            border-radius: 12px !important;
        }

        .shadow-sm-custom {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
        }

        .shadow-md-custom {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1) !important;
        }

        .shadow-lg-custom {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
        }

        /* Button Styles - General button styling */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transition: background-color 0.3s ease, border-color 0.3s ease;
            border-radius: 8px;
            font-weight: 600;
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark-shade);
            border-color: var(--primary-dark-shade);
            color: white;
        }

        .btn-warning {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: var(--text-dark);
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
            font-weight: 600;
        }

        .btn-warning:hover {
            background-color: var(--secondary-dark-shade);
            border-color: var(--secondary-dark-shade);
            color: var(--text-dark);
        }

        .btn-outline-primary {
            border-color: var(--primary-color);
            color: var(--primary-color);
            font-weight: 600;
            transition: all 0.3s ease;
            border-radius: 50px;
            padding: 12px 30px;
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.2);
        }

        /* General Layout/Spacing - Could be global if used widely */
        .py-5 {
            padding-top: 3rem !important;
            padding-bottom: 3rem !important;
        }

        .bg-primary {
            background-color: var(--primary-color) !important;
        }

        /* Animations - Global animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes animateBubbles {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 0;
            }

            50% {
                opacity: 1;
            }

            100% {
                transform: translateY(-1000px) rotate(720deg);
                opacity: 0;
            }
        }

        /* Navbar */
        .navbar {
            position: sticky;
            top: 0;
            width: 100%;
            z-index: 1030;
        }

        .navbar.dashboard-navbar {
            background-color: var(--panel-bg);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 10px 0;
        }

        .navbar.dashboard-navbar .navbar-brand {
            color: var(--primary-color) !important;
        }

        .navbar.dashboard-navbar .nav-link {
            color: var(--text-color-light);
        }

        .navbar.dashboard-navbar .nav-link:hover,
        .navbar.dashboard-navbar .nav-link.active {
            color: var(--primary-color);
            background-color: #e9ecef;
        }

        .navbar-brand {
            font-weight: 600;
            color: var(--primary-color) !important;
            display: flex;
            align-items: center;
            font-size: 1.3rem;
        }

        .nav-item {
            margin: 0px 2px;
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

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%23007bff' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        .navbar.dashboard-navbar .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%23333' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        .navbar-nav .nav-link {
            color: var(--text-muted);
            font-weight: 500;
            padding: 4px 5px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: var(--primary-color);
            background-color: #e9ecef;
        }

        .navbar-nav .nav-link.active {
            font-weight: 600;
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
            background-color: var(--white-bg);
        }

        .navbar.dashboard-navbar .dropdown-menu {
            background-color: var(--panel-bg);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .dropdown-item {
            padding: 10px 20px;
            color: var(--text-dark);
            transition: background-color 0.2s ease;
        }

        .navbar.dashboard-navbar .dropdown-item {
            color: var(--text-color-light);
        }

        .dropdown-item:hover {
            background-color: var(--light-bg);
            color: var(--primary-color);
        }

        .navbar.dashboard-navbar .dropdown-item:hover {
            background-color: #e9ecef;
        }

        .dropdown-divider {
            margin: 5px 0;
            border-top-color: rgba(0, 0, 0, 0.1);
        }

        .navbar.dashboard-navbar .dropdown-divider {
            border-top-color: rgba(0, 0, 0, 0.1);
        }


        /* FOOTER STYLES */
        footer {
            background-color: var(--primary-color);
            color: white;
            padding: 40px 0;
            font-size: 0.9rem;
            box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.15);
        }

        footer .footer-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: white;
        }

        footer .footer-logo img {
            height: 40px;
            margin-right: 10px;
        }

        footer .footer-text {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 25px;
            line-height: 1.6;
        }

        footer .footer-links a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
            padding: 5px 0;
            display: block;
        }

        footer .footer-links a:hover {
            color: var(--secondary-color);
        }

        footer .social-icons {
            margin-top: 25px;
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        footer .social-icons a {
            font-size: 1.8rem;
            color: white;
            transition: color 0.3s ease, transform 0.2s ease;
        }

        footer .social-icons a:hover {
            color: var(--secondary-color);
            transform: translateY(-3px);
        }

        footer .copyright {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
        }

        /* Media Queries for Responsiveness Footer */
        @media (min-width: 768px) {

            footer .footer-logo,
            footer .social-icons {
                justify-content: flex-start;
            }

            footer .footer-text {
                text-align: left;
            }

            footer .footer-links a {
                display: block;
                margin-right: 0;
            }

            footer .copyright {
                text-align: left;
            }
        }

        @media (max-width: 767.98px) {
            footer .text-start {
                text-align: center !important;
            }
        }

        /* Dashboard Specific Styles (ADJUSTED TO LIGHT THEME) */
        body.member-dashboard {
            background-color: var(--dashboard-bg);
            color: var(--text-color-light);
        }

        .dashboard-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .dashboard-main-content {
            flex-grow: 1;
            display: flex;
            padding: 30px;
            gap: 30px;
        }

        .dashboard-right-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .dashboard-card {
            background-color: var(--panel-bg);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .dashboard-card h3 {
            color: var(--text-color-light);
            font-size: 1.6rem;
            margin-bottom: 25px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding-bottom: 15px;
        }

        .profile-progress-card h4 {
            font-size: 1.2rem;
            color: var(--text-color-light);
            margin-bottom: 10px;
        }

        .progress-bar-container {
            width: 100%;
            background-color: #e2e8f0;
            border-radius: 5px;
            height: 8px;
            margin-bottom: 15px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background-color: var(--primary-color);
            border-radius: 5px;
        }

        .profile-status-icons {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .profile-status-icons .status-item {
            display: flex;
            align-items: center;
            color: var(--accent-green);
            font-size: 0.95rem;
            font-weight: 500;
        }

        .profile-status-icons .status-item i {
            margin-right: 8px;
            font-size: 1.2rem;
        }

        .profile-status-icons .status-item.pending {
            color: var(--text-color-muted-dark);
        }

        .form-label {
            color: var(--text-color-muted-dark);
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            background-color: var(--white-bg);
            border: 1px solid var(--border-color);
            color: var(--text-dark);
            border-radius: 8px;
            padding: 10px 15px;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: var(--white-bg);
            color: var(--text-dark);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(43, 108, 176, 0.25);
        }

        .form-control::placeholder {
            color: var(--text-muted);
            opacity: 0.7;
        }

        /* Responsive Adjustments */
        @media (max-width: 991.98px) {
            .dashboard-main-content {
                flex-direction: column;
                padding: 20px;
                gap: 20px;
            }

            .dashboard-right-content {
                gap: 20px;
            }

            .dashboard-card {
                padding: 20px;
            }

            .dashboard-card h3 {
                font-size: 1.4rem;
                margin-bottom: 20px;
                padding-bottom: 10px;
            }
        }

        @media (max-width: 767.98px) {
            body {
                padding-top: 56px;
            }

            .navbar-brand {
                font-size: 1.1rem;
            }

            .navbar-brand img {
                height: 30px;
            }
        }
    </style>
    @stack('styles') {{-- Satu-satunya tempat untuk stack styles dari child views --}}
</head>

<body class="member-dashboard">

    <div class="dashboard-container">
        {{-- Header (Navbar) --}}
        @include('layouts.landingpage._partials.header')

        <div class="dashboard-main-content">
            {{-- Left Panel (Dashboard Navigation) --}}
            @include('layouts.landingpage._partials.sider')

            {{-- Main Content Area --}}
            <main class="dashboard-right-content">
                @yield('content')
            </main>
        </div>

        {{-- Footer --}}
        @include('layouts.landingpage._partials.footer-minimal') {{-- Changed partial name --}}
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

    @stack('scripts')

</body>

</html>
