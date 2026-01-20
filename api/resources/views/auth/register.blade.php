<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Register - Laundry App</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
    <meta content="Halaman pendaftaran member untuk aplikasi laundry Anda." name="description" />
    <meta content="Laundry App Team" name="author" />

    <link rel="icon" href="https://via.placeholder.com/32/007bff/ffffff?text=L" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />

    <style>
        :root {
            --primary-color: #0056b3;
            /* Consistent primary blue */
            --secondary-color: #ffc107;
            /* Consistent accent yellow */
            --blue-gradient-start: #007bff;
            /* Brighter blue for gradient */
            --blue-gradient-end: #0056b3;
            /* Deeper blue for gradient */
        }

        /* --- PERUBAHAN UTAMA: Hapus overflow: hidden dan sesuaikan display --- */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f0f4f8 0%, #d9e2ec 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px 0;
        }

        .register-wrapper {
            display: flex;
            background-color: #fff;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            width: 900px;
            max-width: 90%;
        }

        .register-sidebar {
            flex: 1;
            background: linear-gradient(to right, var(--blue-gradient-start), var(--blue-gradient-end));
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .register-sidebar h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 700;
            position: relative;
            z-index: 2;
        }

        .register-sidebar p {
            font-size: 1rem;
            line-height: 1.6;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }

        .register-sidebar .icon-large {
            font-size: 5rem;
            margin-bottom: 20px;
            animation: bounceIn 1s ease-out;
            position: relative;
            z-index: 2;
        }

        /* PERUBAHAN: Animasi bubble yang baru */
        .bubbles {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .bubbles li {
            position: absolute;
            background: rgba(255, 255, 255, 0.2);
            display: block;
            border-radius: 50%;
            animation: animateBubbles 15s linear infinite;
            bottom: -150px;
        }

        .bubbles li:nth-child(1) { left: 25%; width: 80px; height: 80px; animation-delay: 0s; animation-duration: 10s; }
        .bubbles li:nth-child(2) { left: 10%; width: 20px; height: 20px; animation-delay: 2s; animation-duration: 12s; }
        .bubbles li:nth-child(3) { left: 70%; width: 20px; height: 20px; animation-delay: 4s; animation-duration: 15s; }
        .bubbles li:nth-child(4) { left: 40%; width: 60px; height: 60px; animation-delay: 0s; animation-duration: 18s; }
        .bubbles li:nth-child(5) { left: 65%; width: 20px; height: 20px; animation-delay: 0s; animation-duration: 20s; }
        .bubbles li:nth-child(6) { left: 75%; width: 110px; height: 110px; animation-delay: 3s; animation-duration: 17s; }
        .bubbles li:nth-child(7) { left: 35%; width: 150px; height: 150px; animation-delay: 7s; animation-duration: 13s; }
        .bubbles li:nth-child(8) { left: 50%; width: 25px; height: 25px; animation-delay: 15s; animation-duration: 16s; }
        .bubbles li:nth-child(9) { left: 20%; width: 15px; height: 15px; animation-delay: 2s; animation-duration: 9s; }
        .bubbles li:nth-child(10) { left: 85%; width: 150px; height: 150px; animation-delay: 0s; animation-duration: 11s; }


        .register-form-container {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .register-form-container h2 {
            text-align: center;
            color: #333;
            font-size: 2rem;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .form-label {
            font-weight: 500;
            color: #555;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 12px;
            padding: 15px 20px;
            border: 1px solid #ced4da;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
            outline: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 12px;
            padding: 15px 20px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 10px rgba(0, 123, 255, 0.3);
        }

        .btn-primary:hover {
            background-color: var(--blue-gradient-end);
            border-color: var(--blue-gradient-end);
            transform: translateY(-2px);
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .form-check-label {
            font-size: 0.95rem;
            color: #666;
        }

        .alert-danger {
            background-color: #ffe6e6;
            color: #cc0000;
            border-color: #ffb3b3;
            border-radius: 8px;
            padding: 12px 20px;
            font-size: 0.9rem;
            margin-bottom: 25px;
        }

        .alert-danger ul {
            margin-bottom: 0;
            padding-left: 20px;
        }

        .text-danger {
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 0.95rem;
            color: #555;
        }

        .login-link a {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: var(--blue-gradient-end);
            text-decoration: underline;
        }

        /* --- PERUBAHAN UTAMA: Responsive adjustments --- */
        @media (max-width: 768px) {
            body {
                display: block;
                min-height: auto;
            }

            .register-wrapper {
                flex-direction: column;
                width: 95%;
                margin: 20px auto;
            }

            .register-sidebar {
                padding: 30px;
                border-bottom-left-radius: 0;
                border-bottom-right-radius: 0;
                height: 25vh;
                justify-content: flex-start;
            }

            .register-sidebar h1 {
                font-size: 2rem;
            }

            .register-sidebar .icon-large {
                font-size: 4rem;
                margin-bottom: 10px;
            }

            .register-sidebar p {
                display: none;
            }

            .register-form-container {
                padding: 30px;
            }
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0.1);
                opacity: 0;
            }

            60% {
                transform: scale(1.1);
                opacity: 1;
            }

            100% {
                transform: scale(1);
            }
        }

        @keyframes animateBubbles {
            0% {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
            100% {
                transform: translateY(-100vh) scale(0.2);
                opacity: 0;
            }
        }
    </style>
</head>

<body>
    <div class="register-wrapper">
        <div class="register-sidebar">
            {{-- Tambahkan list bubble di sini --}}
            <ul class="bubbles">
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
            </ul>
            <i class="fas fa-user-plus icon-large"></i>
            <h1>Daftar Member Baru</h1>
            <p>Bergabunglah dengan Laundry App dan nikmati kemudahan serta berbagai promo menarik untuk pakaian bersih
                Anda!</p>
        </div>
        <div class="register-form-container">
            <h2>Buat Akun Anda</h2>

            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('register') }}" method="POST">
                @csrf

                <div class="mb-4"> <label for="name" class="form-label">Nama Lengkap</label>
                    <input name="name" type="text" value="{{ old('name') }}" class="form-control" id="name"
                        placeholder="Masukkan nama lengkap Anda" required autocomplete="name" autofocus />
                    @error('name')
                    <div class="text-danger">
                        <strong>{{ $message }}</strong>
                    </div>
                    @enderror
                </div>

                <div class="mb-4"> <label for="email" class="form-label">Email Address</label>
                    <input name="email" type="email" value="{{ old('email') }}" class="form-control" id="email"
                        placeholder="contoh@email.com" required autocomplete="email" />
                    @error('email')
                    <div class="text-danger">
                        <strong>{{ $message }}</strong>
                    </div>
                    @enderror
                </div>

                <div class="row">
                    <div class="mb-4 col-md-6"> <label for="password" class="form-label">Password</label>
                        <input name="password" type="password" class="form-control" id="password"
                            placeholder="Buat password Anda" required autocomplete="new-password" />
                        @error('password')
                        <div class="text-danger">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>

                    <div class="mb-4 col-md-6"> <label for="password_confirmation" class="form-label">Konfirmasi
                            Password</label>
                        <input name="password_confirmation" type="password" class="form-control"
                            id="password_confirmation" placeholder="Ulangi password Anda" required
                            autocomplete="new-password" />
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Daftar Sekarang</button>
                </div>
            </form>

            <div class="text-center mt-3">
                <p class="text-muted mb-0">Sudah punya akun? <a href="{{ route('login') }}" class="text-decoration-none"
                        style="color: var(--primary-color);">Login di sini</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>

</html>