<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Login - Business Portal</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
    <meta content="Portal akses dashboard bisnis Anda." name="description" />

    <link rel="icon" href="https://via.placeholder.com/32/0056b3/ffffff?text=B" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />

    <style>
        :root {
            /* GANTI WARNA DI SINI UNTUK CUSTOM BRANDING */
            --primary-color: #2563eb;
            --primary-dark: #1e40af;
            --accent-color: #fbbf24;
            --bg-gradient: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            --sidebar-gradient: linear-gradient(180deg, #2563eb 0%, #1e40af 100%);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-gradient);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .login-wrapper {
            display: flex;
            background-color: #fff;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 950px;
            max-width: 100%;
            min-height: 550px;
        }

        /* Sidebar Styling */
        .login-sidebar {
            flex: 1.2;
            background: var(--sidebar-gradient);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 50px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-content-box {
            position: relative;
            z-index: 2; /* Di atas bubble */
        }

        .login-sidebar h1 {
            font-size: 2.2rem;
            margin-bottom: 15px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .login-sidebar p {
            font-size: 0.95rem;
            line-height: 1.6;
            opacity: 0.85;
            max-width: 300px;
        }

        .icon-large {
            font-size: 4.5rem;
            margin-bottom: 25px;
            color: white;
            filter: drop-shadow(0 10px 15px rgba(0,0,0,0.1));
            animation: bounceIn 0.8s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        }

        /* Bubble Animation */
        .bubbles {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 1;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .bubbles li {
            position: absolute;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            bottom: -100px;
            animation: animateBubbles 12s linear infinite;
        }

        /* Variasi Ukuran Bubble */
        .bubbles li:nth-child(1) { left: 10%; width: 40px; height: 40px; animation-delay: 0s; }
        .bubbles li:nth-child(2) { left: 30%; width: 20px; height: 20px; animation-delay: 2s; animation-duration: 8s; }
        .bubbles li:nth-child(3) { left: 70%; width: 60px; height: 60px; animation-delay: 4s; }
        .bubbles li:nth-child(4) { left: 90%; width: 30px; height: 30px; animation-delay: 1s; }
        .bubbles li:nth-child(5) { left: 50%; width: 50px; height: 50px; animation-delay: 6s; }

        /* Form Styling */
        .login-form-container {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-form-container h2 {
            color: #1e293b;
            font-size: 1.75rem;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .subtitle {
            color: #64748b;
            margin-bottom: 32px;
            font-size: 0.9rem;
        }

        .form-label {
            font-weight: 500;
            color: #475569;
            font-size: 0.85rem;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 16px;
            border: 1.5px solid #e2e8f0;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
        }

        /* Mobile Responsive */
        @media (max-width: 850px) {
            .login-wrapper { flex-direction: column; width: 100%; }
            .login-sidebar { padding: 40px 20px; border-radius: 0; }
            .login-sidebar p { display: none; }
            .icon-large { font-size: 3rem; margin-bottom: 10px; }
            .login-form-container { padding: 40px 30px; }
        }

        @keyframes animateBubbles {
            0% { transform: translateY(0) scale(1); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-80vh) scale(0.5); opacity: 0; }
        }

        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(1); }
        }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <div class="login-sidebar">
            <ul class="bubbles">
                <li></li><li></li><li></li><li></li><li></li>
            </ul>
            <div class="login-content-box">
                <i class="fas fa-rocket icon-large"></i> <h1>Selamat Datang Kembali</h1>
                <p>Kelola bisnis Anda dengan lebih cerdas dan pantau transaksi secara real-time.</p>
            </div>
        </div>

        <div class="login-form-container">
            <h2>Masuk ke Akun</h2>
            <p class="subtitle">Silakan masukkan detail akun Anda.</p>

            @if ($errors->any())
            <div class="alert alert-danger py-2 px-3 mb-4" style="font-size: 0.85rem; border-radius: 10px;">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input name="email" type="email" class="form-control" id="email" placeholder="nama@perusahaan.com" required autofocus />
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <label for="password" class="form-label">Password</label>
                    </div>
                    <input name="password" type="password" class="form-control" id="password" placeholder="••••••••" required />
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="rememberMe" />
                    <label class="form-check-label text-muted" for="rememberMe" style="font-size: 0.85rem;">
                        Ingat perangkat ini
                    </label>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Masuk Sekarang</button>
                </div>

                <div class="text-center mt-4">
                    <p class="text-muted small">Butuh bantuan? <a href="#" class="text-decoration-none fw-bold" style="color: var(--primary-color);">Hubungi Support</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
