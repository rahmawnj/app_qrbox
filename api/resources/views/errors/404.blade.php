<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>404 Not Found - Laundry App</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
    <meta content="Halaman tidak ditemukan untuk aplikasi Laundry App." name="description" />
    <meta content="Laundry App Team" name="author" />

    <link rel="icon" href="https://via.placeholder.com/32/007bff/ffffff?text=L" type="image/png">
    {{-- Anda bisa mengganti ini dengan ikon aplikasi Anda yang sebenarnya --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    {{-- Menggunakan Font Awesome untuk ikon, jika ada --}}

    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #0056b3;
            --light-bg: #f0f4f8;
            --text-dark: #333;
            --text-muted: #666;
            --white-bg: #fff;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--light-bg) 0%, #d9e2ec 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            overflow: hidden; /* Prevent scrollbar from bubbles */
            color: var(--text-dark);
        }

        .error-wrapper {
            display: flex;
            flex-direction: column; /* Default to column for small screens */
            background-color: var(--white-bg);
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            width: 900px; /* Consistent with login/register */
            max-width: 95%;
            padding: 40px;
            text-align: center;
            position: relative; /* For the decorative elements */
            z-index: 1;
        }

        @media (min-width: 768px) {
            .error-wrapper {
                flex-direction: row; /* Row layout for larger screens */
                padding: 0; /* Remove padding if inner content has its own */
            }
        }

        .error-sidebar {
            flex: 1;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-radius: 15px; /* Slightly smaller for internal element */
            margin-bottom: 30px; /* Space between sidebar and form on small screens */
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        @media (min-width: 768px) {
            .error-sidebar {
                margin-bottom: 0;
                border-radius: 20px 0 0 20px; /* Only left side rounded */
            }
        }

        /* Decorative overlays - Similar to login/register */
        .error-sidebar::before {
            content: '';
            position: absolute;
            top: -50px;
            left: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            transform: rotate(45deg);
            animation: floatBubble 8s ease-in-out infinite alternate;
        }

        .error-sidebar::after {
            content: '';
            position: absolute;
            bottom: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            transform: rotate(-45deg);
            animation: floatBubble 10s ease-in-out infinite alternate-reverse;
        }

        .error-sidebar h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 700;
            position: relative;
            z-index: 2;
        }

        .error-sidebar p {
            font-size: 1rem;
            line-height: 1.6;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }

        .error-gif-container {
            width: 100%;
            max-width: 250px; /* Control GIF size */
            margin-bottom: 30px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            animation: bounceIn 1s ease-out; /* Apply bounce animation */
        }

        .error-gif-container img {
            width: 100%;
            height: auto;
            display: block;
        }

        .error-content-section {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center; /* Center content horizontally */
            text-align: center; /* Ensure text is centered */
        }

        .error-code {
            font-size: 8rem;
            font-weight: 900; /* Ultra-bold */
            color: var(--primary-color);
            margin-bottom: 10px;
            line-height: 1; /* Remove extra space above */
            animation: fadeIn 1.5s ease-out;
        }

        .error-message {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 20px;
            animation: fadeIn 1.5s ease-out 0.3s;
        }

        .error-desc {
            font-size: 1.1rem;
            color: var(--text-muted);
            margin-bottom: 30px;
            max-width: 500px; /* Limit width for readability */
            animation: fadeIn 1.5s ease-out 0.6s;
        }

        .btn-go-home {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            border-radius: 12px;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 123, 255, 0.3);
            text-decoration: none; /* Ensure no underline */
        }

        .btn-go-home:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 123, 255, 0.4);
        }

        /* Keyframes for animations (consistent with login/register) */
        @keyframes bounceIn {
            0% { transform: scale(0.1); opacity: 0; }
            60% { transform: scale(1.05); opacity: 1; }
            80% { transform: scale(0.95); }
            100% { transform: scale(1); }
        }

        @keyframes floatBubble {
            0% { transform: translate(0, 0) rotate(45deg); }
            50% { transform: translate(20px, 20px) rotate(50deg); }
            100% { transform: translate(0, 0) rotate(45deg); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .error-wrapper {
                padding: 30px;
            }
            .error-sidebar {
                padding: 30px;
            }
            .error-sidebar h1 {
                font-size: 2rem;
            }
            .error-gif-container {
                max-width: 200px;
            }
            .error-content-section {
                padding: 30px;
            }
            .error-code {
                font-size: 6rem;
            }
            .error-message {
                font-size: 2rem;
            }
            .error-desc {
                font-size: 1rem;
            }
            .btn-go-home {
                padding: 12px 25px;
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="error-wrapper">
        <div class="error-sidebar">
            <div class="error-gif-container">
                <img src="{{ asset('assets/img/boss.gif') }}" alt="Boss GIF" />
            </div>
            <h1>Ups, Halaman Tidak Ditemukan!</h1>
            <p>Sepertinya Anda tersesat. Jangan khawatir, kami akan bantu Anda kembali ke jalur yang benar.</p>
        </div>
        <div class="error-content-section">
            <div class="error-code">404</div>
            <div class="error-message">Halaman Ini Tidak Ada!</div>
            <div class="error-desc">
                Maaf, halaman yang Anda cari tidak dapat ditemukan. Mungkin alamat yang Anda masukkan salah, atau halaman tersebut telah dihapus.
            </div>
            <div>
                <a href="{{ route('dashboard') }}" class="btn btn-go-home">Kembali ke Beranda</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>

</html>
