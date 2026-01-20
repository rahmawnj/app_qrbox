<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>@yield('title','Laundry App')</title>

    {{-- Bootstrap (CDN) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Google Font: Poppins --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    {{-- Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <style>
        :root {
            --primary-color: #0056b3; /* Consistent primary blue */
            --secondary-color: #ffc107; /* Consistent accent yellow */
            --blue-gradient-start: #007bff; /* Brighter blue for gradient */
            --blue-gradient-end: #0056b3; /* Deeper blue for gradient */
            --light-bg: #e3f2fd;
            --card-radius: 14px;
            --shadow-soft: 0 8px 28px rgba(33, 150, 243, 0.15);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f0f4f8 0%, #d9e2ec 100%);
            color: #212121;
        }

        a {
            transition: opacity 0.18s ease;
        }

        a:hover {
            opacity: 0.87;
        }

        .bubble-overlay {
            position: relative;
            overflow: hidden;
            isolation: isolate;
                        background: linear-gradient(to right, var(--blue-gradient-start), var(--blue-gradient-end));

        }

        .bubble-overlay > .bubble-container {
            position: absolute;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            list-style: none; /* Menghilangkan bullet point */
            padding: 0;
            margin: 0;
        }

        .bubble {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2); /* Warna bubble putih transparan */
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.2); /* Efek glow yang lebih lembut */
            bottom: -150px;
            animation-iteration-count: infinite;
            animation-name: bubble-move-up;
            animation-timing-function: linear;
        }

        @keyframes bubble-move-up {
            0% {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) scale(0.2);
                opacity: 0;
            }
        }

        .card-modern {
            border: 0;
            border-radius: var(--card-radius);
            background: #fff;
            box-shadow: var(--shadow-soft);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        .card-modern .card-body {
            position: relative;
            z-index: 2;
        }

        .text-primary-custom {
            color: var(--primary-color) !important;
        }
    </style>

    @stack('styles')
</head>
<body>
    {{-- header --}}
    @includeIf('layouts.landingpage._partials.header')

    {{-- main --}}
    <main class="main-content">
        @yield('content')
    </main>

    {{-- footer --}}
    @includeIf('layouts.landingpage._partials.footer')

    {{-- bootstrap bundle --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>

    {{-- Bubble overlay script (global; safe) --}}
    <script>
        (function() {
            function rand(min, max) {
                return Math.random() * (max - min) + min;
            }

            function createBubblesFor(el) {
                try {
                    if (!el) return;

                    // Menggunakan data dari halaman login/register untuk konsistensi
                    const bubbleCount = 10;
                    const container = document.createElement('ul');
                    container.className = 'bubbles bubble-container';
                    el.appendChild(container);

                    const bubbleData = [
                        { left: 25, size: 80, delay: 0, duration: 10 },
                        { left: 10, size: 20, delay: 2, duration: 12 },
                        { left: 70, size: 20, delay: 4, duration: 15 },
                        { left: 40, size: 60, delay: 0, duration: 18 },
                        { left: 65, size: 20, delay: 0, duration: 20 },
                        { left: 75, size: 110, delay: 3, duration: 17 },
                        { left: 35, size: 150, delay: 7, duration: 13 },
                        { left: 50, size: 25, delay: 15, duration: 16 },
                        { left: 20, size: 15, delay: 2, duration: 9 },
                        { left: 85, size: 150, delay: 0, duration: 11 },
                    ];

                    bubbleData.forEach(data => {
                        const b = document.createElement('li');
                        b.className = 'bubble';
                        b.style.width = data.size + 'px';
                        b.style.height = data.size + 'px';
                        b.style.left = data.left + '%';
                        b.style.animationDelay = data.delay + 's';
                        b.style.animationDuration = data.duration + 's';
                        container.appendChild(b);
                    });

                    el.__bubbles_created = true;
                } catch (e) {
                    console.error('BubbleOverlay error:', e);
                }
            }

            try {
                const io = new IntersectionObserver(entries => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            createBubblesFor(entry.target);
                            io.unobserve(entry.target);
                        }
                    });
                }, { root: null, rootMargin: '0px', threshold: 0.05 });

                document.addEventListener('DOMContentLoaded', () => {
                    document.querySelectorAll('.bubble-overlay').forEach(el => {
                        if (el.dataset.bubbleImmediate === "true") {
                            createBubblesFor(el);
                        } else {
                            io.observe(el);
                        }
                    });
                });
            } catch (e) {
                document.addEventListener('DOMContentLoaded', () => {
                    document.querySelectorAll('.bubble-overlay').forEach(createBubblesFor);
                });
            }

            window.BubbleOverlay = { createFor: createBubblesFor };
        })();
    </script>

    @stack('scripts')
</body>
</html>
