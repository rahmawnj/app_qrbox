<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QRBox | API Documentation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        html { scroll-behavior: smooth; }
        pre { background: #1e293b; color: #f8fafc; padding: 1.25rem; border-radius: 0.75rem; overflow-x: auto; font-size: 0.875rem; border: 1px solid #334155; line-height: 1.5; }
        .method-get { background: #10b981; }
        .method-post { background: #3b82f6; }
        section { scroll-margin-top: 2rem; }
        code { color: #e11d48; font-weight: 600; font-family: monospace; }

        /* Sidebar Styling */
        .sidebar-link {
            @apply flex items-center gap-3 text-slate-400 hover:text-white hover:bg-slate-800 px-4 py-3 rounded-lg transition-all duration-200;
        }
        .sidebar-link.active {
            @apply bg-blue-600/20 text-blue-400 border border-blue-600/30;
        }
        aside::-webkit-scrollbar { width: 4px; }
        aside::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-900 leading-relaxed">

<div class="flex flex-col md:flex-row min-h-screen">
    <aside class="w-full md:w-80 bg-slate-900 text-white p-6 sticky top-0 h-screen flex flex-col shadow-2xl z-50">
        <div class="mb-10 px-2 flex items-center gap-3">
            <div class="bg-blue-600 p-2 rounded-lg">
                <i class="fas fa-box-open text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-tight">QRBox<span class="text-blue-500">.id</span></h1>
                <p class="text-slate-500 text-[10px] uppercase tracking-widest font-semibold">API Documentation v1.0</p>
            </div>
        </div>

        <nav class="space-y-2 flex-1">
            <p class="text-slate-500 text-[10px] uppercase tracking-widest font-bold mb-4 px-4">Menu Utama</p>

            <a href="#intro" class="sidebar-link active" onclick="setActive(this)">
                <i class="fas fa-info-circle w-5"></i> Pendahuluan
            </a>
            <a href="#service-types" class="sidebar-link" onclick="setActive(this)">
                <i class="fas fa-list-ul w-5"></i> Service Types
            </a>

            <p class="text-slate-500 text-[10px] uppercase tracking-widest font-bold mt-8 mb-4 px-4">Endpoint API</p>

            <a href="#transaction" class="sidebar-link" onclick="setActive(this)">
                <i class="fas fa-exchange-alt w-5"></i> Transaksi & QRIS
            </a>
            <a href="#payment-check" class="sidebar-link" onclick="setActive(this)">
                <i class="fas fa-check-double w-5"></i> Status Pembayaran
            </a>
            <a href="#bypass" class="sidebar-link" onclick="setActive(this)">
                <i class="fas fa-microchip w-5"></i> IoT / Hardware
            </a>
        </nav>

        <div class="mt-auto pt-6 border-t border-slate-800 px-4">
            <div class="flex items-center gap-3 text-slate-400 text-sm">
                <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                Server Status: Online
            </div>
            <p class="text-slate-600 text-[10px] mt-4">&copy; 2026 QRBox Indonesia</p>
        </div>
    </aside>

    <main class="flex-1 p-6 md:p-12 max-w-5xl">

        <section id="intro" class="mb-20">
            <h2 class="text-4xl font-black mb-6 text-slate-800 tracking-tight">Pendahuluan</h2>
            <p class="text-slate-600 text-lg max-w-3xl leading-relaxed">
                Selamat datang di dokumentasi teknis QRBox.id. API ini dirancang untuk menjembatani sistem pembayaran digital (QRIS) dengan kontrol hardware IoT secara real-time.
            </p>
            <div class="mt-8 p-5 bg-blue-50 border-l-4 border-blue-500 text-blue-900 rounded-r-xl shadow-sm flex items-center gap-4">
                <div class="bg-blue-500 text-white p-3 rounded-lg shadow-md">
                    <i class="fas fa-server"></i>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase text-blue-600 tracking-wider">Base API URL</p>
                    <code class="text-lg">https://api.qrbox.id/api</code>
                </div>
            </div>
        </section>

        <section id="service-types" class="mb-20">
            <h2 class="text-2xl font-bold mb-8 flex items-center text-slate-800">
                <span class="bg-blue-100 p-2 rounded-lg mr-4"><i class="fas fa-tags text-blue-600 text-sm"></i></span>
                Service Types
            </h2>
            <div class="overflow-hidden bg-white rounded-2xl shadow-sm border border-slate-200">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="p-5 font-bold text-slate-700 uppercase text-xs tracking-wider">Kategori</th>
                            <th class="p-5 font-bold text-slate-700 uppercase text-xs tracking-wider">Slug Value (Payload)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50 transition"><td class="p-5 font-bold text-slate-800">LAUNDRY</td><td class="p-5"><code>washer</code>, <code>dryer_a</code>, <code>dryer_b</code></td></tr>
                        <tr class="hover:bg-slate-50 transition"><td class="p-5 font-bold text-slate-800">TURNSTILE</td><td class="p-5"><code>turnstile</code></td></tr>
                        <tr class="hover:bg-slate-50 transition"><td class="p-5 font-bold text-slate-800">DISPENSER</td><td class="p-5"><code>dispenser_a</code>, <code>dispenser_b</code>, <code>dispenser_c</code></td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="transaction" class="mb-20">
            <h2 class="text-2xl font-bold mb-8 flex items-center text-slate-800">
                <span class="bg-blue-100 p-2 rounded-lg mr-4"><i class="fas fa-rocket text-blue-600 text-sm"></i></span>
                API Transaksi & QRIS
            </h2>

            <div class="mb-10 bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
                <div class="flex items-center gap-3 mb-6">
                    <span class="method-get text-white px-3 py-1 rounded-md text-xs font-black">GET</span>
                    <span class="font-mono font-bold text-slate-700 tracking-tight">/device-menu/{device_code}</span>
                </div>
                <p class="text-slate-600 mb-6 font-medium">Mengambil daftar menu, harga, dan durasi aktif perangkat berdasarkan kode unik.</p>
                <pre>{
  "status": "success",
  "service_type": "Laundry",
  "device_name": "Main Machine Laundry",
  "device_code": "DEV-WHNTZR",
  "menus": [
    { "name": "Laundry Menu 1", "type": "washer", "price": 13000, "active": true, "duration": 45 }
  ]
}</pre>
            </div>

            <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
                <div class="flex items-center gap-3 mb-6">
                    <span class="method-post text-white px-3 py-1 rounded-md text-xs font-black">POST</span>
                    <span class="font-mono font-bold text-slate-700 tracking-tight">/qr-request</span>
                </div>
                <p class="text-slate-600 mb-8">Generate pesanan baru dan mendapatkan URL gambar QRIS.</p>

                <div class="space-y-8">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 mb-3 uppercase tracking-[0.2em]">Request Payload</p>
                        <pre>{
  "type": "washer",
  "device_code": "DEV-WHNTZR"
}</pre>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 mb-3 uppercase tracking-[0.2em]">Response</p>
                        <pre>{
  "status": "success",
  "message": {
    "order_id": "TRX-OUT-GOFUR-1768813632-696DF4408018E",
    "device_name": "Main Machine Laundry",
    "qr_image": "http://api.qrbox.id/storage/qrcodes/TRX-XXX.jpg",
    "final_amount": 9000
  }
}</pre>
                    </div>
                </div>
            </div>
        </section>

        <section id="payment-check" class="mb-20">
            <h2 class="text-2xl font-bold mb-8 flex items-center text-slate-800">
                <span class="bg-emerald-100 p-2 rounded-lg mr-4"><i class="fas fa-check-circle text-emerald-600 text-sm"></i></span>
                Status Pembayaran
            </h2>

            <div class="mb-8 bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
                <div class="flex items-center gap-3 mb-4">
                    <span class="method-get text-white px-3 py-1 rounded-md text-xs font-black uppercase">GET</span>
                    <span class="font-mono font-bold text-slate-700">/payment-check?order_id={order_id}</span>
                </div>
                <p class="text-slate-600 mb-6">Endpoint utama untuk mengecek keberhasilan transaksi.</p>
                <pre>{
  "status": "success",
  "message": {
    "type": "dryer_a",
    "order_id": "TRX-OUT-GOFUR-1768811924-696DED94C0A9E",
    "payment_status": "success",
    "device_status": 0,
    "qr_code_deleted": false,
    "description": "Pembayaran Berhasil."
  }
}</pre>
            </div>
        </section>

        <section id="bypass" class="mb-20">
            <h2 class="text-2xl font-bold mb-8 flex items-center text-slate-800">
                <span class="bg-orange-100 p-2 rounded-lg mr-4"><i class="fas fa-microchip text-orange-600 text-sm"></i></span>
                API IoT / Hardware (Polling)
            </h2>
            <div class="bg-slate-900 text-slate-300 p-8 rounded-3xl shadow-xl">
                <div class="flex items-center gap-3 mb-8">
                    <span class="bg-orange-500 text-white px-3 py-1 rounded-md text-xs font-black uppercase border border-orange-400">GET</span>
                    <span class="font-mono text-white text-lg tracking-tight">/check-device?device_code={code}</span>
                </div>

                <p class="mb-10 text-slate-400">Hardware (ESP32) wajib melakukan polling setiap 3-5 detik.</p>

                <div class="grid grid-cols-1 gap-8">
                    <div>
                        <p class="text-[10px] font-black text-emerald-500 mb-3 uppercase tracking-[0.2em] flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                            Kondisi Aktif (Aktivasi)
                        </p>
                        <pre class="bg-slate-800 border-emerald-900/50 text-emerald-400">{
  "status": "success",
  "status_device": "dryer_a",
  "source": "bypass",
  "activation_date": "2026-01-19 16:12:46",
  "message": "Status diterima"
}</pre>
                    </div>

                    <div>
                        <p class="text-[10px] font-black text-slate-500 mb-3 uppercase tracking-[0.2em] flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-slate-600"></span>
                            Kondisi Standby (Idle)
                        </p>
                        <pre class="bg-slate-800 border-slate-700 text-slate-400">{
  "status": "success",
  "status_device": "off",
  "source": null,
  "activation_date": null,
  "message": "Status diterima"
}</pre>
                    </div>
                </div>

                <div class="mt-10 p-6 bg-orange-600/10 border-l-4 border-orange-500 rounded-r-2xl">
                    <h4 class="text-orange-500 font-bold mb-2 flex items-center gap-2 text-sm uppercase">
                        <i class="fas fa-exclamation-triangle"></i> Panduan Hardware
                    </h4>
                    <ul class="text-xs text-slate-400 space-y-2 list-none">
                        <li>• Segera aktifkan relay jika <code>status_device</code> bukan <code>off</code>.</li>
                        <li>• Gunakan HTTPS dengan sertifikat yang valid untuk keamanan data.</li>
                    </ul>
                </div>
            </div>
        </section>

    </main>
</div>

<script>
    // Highlight sidebar links based on scroll position
    window.addEventListener('scroll', () => {
        let current = "";
        const sections = document.querySelectorAll("section");
        const navLinks = document.querySelectorAll(".sidebar-link");

        sections.forEach((section) => {
            const sectionTop = section.offsetTop;
            if (pageYOffset >= sectionTop - 100) {
                current = section.getAttribute("id");
            }
        });

        navLinks.forEach((link) => {
            link.classList.remove("active");
            if (link.getAttribute("href").includes(current)) {
                link.classList.add("active");
            }
        });
    });

    // Manual click active state
    function setActive(el) {
        document.querySelectorAll('.sidebar-link').forEach(link => link.classList.remove('active'));
        el.classList.add('active');
    }
</script>

</body>
</html>
