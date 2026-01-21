<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QRBox | API Documentation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        pre { background: #1e293b; color: #f8fafc; padding: 1.5rem; border-radius: 0.75rem; border: 1px solid #334155; font-family: 'Fira Code', monospace; }
        code { color: #e11d48; font-weight: 600; }
        .method { font-size: 0.75rem; font-weight: 900; padding: 4px 10px; border-radius: 6px; color: white; margin-right: 10px; }
        .get { background-color: #10b981; }
        .post { background-color: #3b82f6; }

        /* Sidebar Navigation */
        #sidebar { background: #0f172a; min-height: 100vh; color: #94a3b8; position: sticky; top: 0; }
        .nav-link { color: #94a3b8; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 5px; transition: 0.3s; }
        .nav-link:hover { background: #1e293b; color: #fff; }
        .nav-link.active { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); }
        .sidebar-heading { font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: #475569; margin-top: 2rem; padding: 0 1rem; }

        /* Custom UI */
        .api-card { background: white; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .base-url-box { background: #eff6ff; border-left: 4px solid #3b82f6; border-radius: 0 0.5rem 0.5rem 0; padding: 1rem; display: flex; align-items: center; }
        .iot-box { background: #0f172a; border-radius: 1.5rem; padding: 2.5rem; color: #cbd5e1; }
        .status-pulse { height: 10px; width: 10px; background-color: #10b981; border-radius: 50%; display: inline-block; margin-right: 10px; animation: pulse 2s infinite; }

        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.4; } 100% { opacity: 1; } }
        section { scroll-margin-top: 30px; }
        @media (max-width: 768px) { #sidebar { height: auto; min-height: auto; } }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block collapse p-4">
            <div class="d-flex align-items-center mb-5">
                <div class="bg-primary p-2 rounded-3 me-3">
                    <i class="fas fa-box-open text-white"></i>
                </div>
                <div>
                    <h5 class="mb-0 text-white fw-bold">QRBox<span class="text-primary">.id</span></h5>
                    <small class="text-uppercase" style="font-size: 10px; letter-spacing: 1px;">API Docs v1.0</small>
                </div>
            </div>

            <div class="sidebar-heading">Menu Utama</div>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link active" href="#intro"><i class="fas fa-info-circle me-2"></i> Pendahuluan</a></li>
                <li class="nav-item"><a class="nav-link" href="#service-types"><i class="fas fa-tags me-2"></i> Service Types</a></li>
            </ul>

            <div class="sidebar-heading">Endpoint API</div>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link" href="#transaction"><i class="fas fa-exchange-alt me-2"></i> Transaksi & QRIS</a></li>
                <li class="nav-item"><a class="nav-link" href="#payment-check"><i class="fas fa-check-double me-2"></i> Status Pembayaran</a></li>
                <li class="nav-item"><a class="nav-link" href="#bypass"><i class="fas fa-microchip me-2"></i> IoT / Hardware</a></li>
            </ul>

            <div class="mt-5 pt-4 border-top border-secondary">
                <div class="small d-flex align-items-center">
                    <span class="status-pulse"></span> Server Status: Online
                </div>
                <div class="mt-3" style="font-size: 10px;">&copy; 2026 QRBox Indonesia</div>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-5 py-5">

            <section id="intro" class="mb-5">
                <h1 class="display-5 fw-800 mb-4">Pendahuluan</h1>
                <p class="lead text-muted">
                    Selamat datang di dokumentasi teknis QRBox.id. API ini dirancang untuk menjembatani sistem pembayaran digital (QRIS) dengan kontrol hardware IoT secara real-time menggunakan autentikasi berbasis token parameter.
                </p>
                <div class="base-url-box mt-4">
                    <div class="bg-primary text-white p-2 rounded-3 me-3 shadow-sm">
                        <i class="fas fa-server"></i>
                    </div>
                    <div>
                        <small class="text-primary fw-bold text-uppercase" style="font-size: 10px;">Base API URL</small>
                        <div class="h5 mb-0 font-monospace">https://api.qrbox.id/api</div>
                    </div>
                </div>
            </section>



            <section id="service-types" class="mb-5">
                <h3 class="fw-bold mb-4 d-flex align-items-center">
                    <span class="p-2 bg-light rounded-3 me-3"><i class="fas fa-list-ul text-primary"></i></span>
                    Service Types
                </h3>
                <div class="table-responsive bg-white rounded-4 border">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="p-4 text-uppercase small fw-bold">Kategori</th>
                                <th class="p-4 text-uppercase small fw-bold">Slug Value (Payload)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="p-4 fw-bold">LAUNDRY</td>
                                <td class="p-4"><code>washer</code>, <code>dryer_a</code>, <code>dryer_b</code></td>
                            </tr>
                            <tr>
                                <td class="p-4 fw-bold">TURNSTILE</td>
                                <td class="p-4"><code>turnstile</code></td>
                            </tr>
                            <tr>
                                <td class="p-4 fw-bold">DISPENSER</td>
                                <td class="p-4"><code>dispenser_a</code>, <code>dispenser_b</code>, <code>dispenser_c</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="transaction" class="mb-5">
                <h3 class="fw-bold mb-4 d-flex align-items-center">
                    <span class="p-2 bg-light rounded-3 me-3"><i class="fas fa-rocket text-primary"></i></span>
                    API Transaksi & QRIS
                </h3>

                <div class="api-card">
                    <div class="d-flex align-items-center mb-3">
                        <span class="method get">GET</span>
                        <span class="fw-bold text-dark font-monospace">/device-menu/{device_code}?api_token={token}</span>
                    </div>
                    <p class="text-muted">Mengambil daftar menu, harga, dan durasi aktif perangkat. Kirimkan <code>api_token</code> sebagai query parameter.</p>
                    <pre>{
  "status": "success",
  "service_type": "Laundry",
  "device_name": "Main Machine Laundry",
  "device_code": "DEV-WHNTZR",
  "menus": [
    {
      "name": "Laundry Menu 1",
      "type": "washer",
      "price": 12000,
      "active": true,
      "duration": 0,
      "description": "75"
    },
    {
      "name": "Laundry Menu 2",
      "type": "dryer_a",
      "price": 11000,
      "active": true,
      "duration": 31,
      "description": "75"
    },
    {
      "name": "Laundry Menu 3",
      "type": "dryer_b",
      "price": 8000,
      "active": true,
      "duration": 89,
      "description": "75"
    },
    {
      "name": "Laundry Menu 4",
      "type": "none",
      "price": 0,
      "active": false,
      "duration": 0,
      "description": "-"
    }
  ]
}</pre>
                </div>

                <div class="api-card">
                    <div class="d-flex align-items-center mb-3">
                        <span class="method post">POST</span>
                        <span class="fw-bold text-dark font-monospace">/qr-request</span>
                    </div>
                    <p class="text-muted">Generate pesanan baru dan mendapatkan URL gambar QRIS.</p>

                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-uppercase fw-bold text-muted mb-2 d-block" style="font-size: 10px;">Request Body (JSON)</small>
                            <pre>{
  "api_token": "your_iot_token_here",
  "type": "washer",
  "device_code": "DEV-WHNTZR"
}</pre>
                        </div>
                        <div class="col-md-6">
                            <small class="text-uppercase fw-bold text-muted mb-2 d-block" style="font-size: 10px;">Response</small>
                            <pre>{
  "status": "success",
  "message": {
    "order_id": "TRX-OUT-GOFUR-1768960177-697030B155794",
    "device_name": "Main Machine Laundry",
    "device_code": "DEV-WHNTZR",
    "transaction_id": 7,
    "payment_status": "pending",
    "qr_image": "http://127.0.0.1:8000/storage/qrcodes/TRX-OUT-GOFUR-1768960177-697030B155794.jpg",
    "expires_at": "2026-01-21 09:04:38",
    "original_price": 11000,
    "final_amount": 9900,
    "fee_deducted": 1100
  }
}</pre>
                        </div>
                    </div>
                </div>
            </section>

      <section id="payment-check" class="mb-5">
    <h3 class="fw-bold mb-4 d-flex align-items-center">
        <span class="p-2 bg-light rounded-3 me-3"><i class="fas fa-check-circle text-success"></i></span>
        Status Pembayaran (By Order ID)
    </h3>
    <div class="api-card">
        <div class="d-flex align-items-center mb-3">
            <span class="method get">GET</span>
            <span class="fw-bold text-dark font-monospace">/payment-check?order_id={id}&api_token={token}</span>
        </div>
        <p class="text-muted">
            Mengecek status pembayaran berdasarkan <code>order_id</code> spesifik. Endpoint ini melakukan validasi token terhadap outlet pemilik perangkat.
        </p>

        <div class="row g-4">
            <div class="col-md-6">
                <small class="text-uppercase fw-bold text-success mb-2 d-block" style="font-size: 10px;">Response: Pembayaran Berhasil</small>
                <pre>{
  "status": "success",
  "message": {
    "type": "washer",
    "order_id": "TRX-OUT-1768960238",
    "payment_status": "success",
    "device_status": 1,
    "qr_code_deleted": true,
    "description": "Pembayaran Berhasil."
  }
}</pre>
                <small class="text-muted d-block mt-2" style="font-size: 11px;">
                    <i class="fas fa-info-circle"></i> Memanggil response ini akan mengupdate <code>bypass_activation</code> di server.
                </small>
            </div>

            <div class="col-md-6">
                <small class="text-uppercase fw-bold text-warning mb-2 d-block" style="font-size: 10px;">Response: Belum Bayar/Pending</small>
                <pre>{
  "status": "success",
  "message": {
    "type": "washer",
    "order_id": "TRX-OUT-1768960238",
    "payment_status": "pending",
    "qr_code_deleted": false,
    "description": "Pembayaran tidak berhasil."
  }
}</pre>
            </div>

            <div class="col-12 mt-4">
                <div class="p-3 rounded-3 bg-light border">
                    <h6 class="fw-bold small text-uppercase mb-3 text-secondary">Kemungkinan Error:</h6>
                    <div class="d-flex gap-4 small">
                        <div><code class="text-danger">401 Unauthorized</code><br>Token API salah.</div>
                        <div><code class="text-danger">400 Bad Request</code><br>Order ID tidak diisi.</div>
                        <div><code class="text-dark">Status: error</code><br>Order tidak ditemukan di sistem.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

            <section id="payment-check-2" class="mb-5">
    <h3 class="fw-bold mb-4 d-flex align-items-center">
        <span class="p-2 bg-light rounded-3 me-3"><i class="fas fa-search-dollar text-success"></i></span>
        Status Pembayaran (Auto-Check)
    </h3>
    <div class="api-card">
        <div class="d-flex align-items-center mb-3">
            <span class="method get">GET</span>
            <span class="fw-bold text-dark font-monospace">/payment-check-2?service_type={type}&device_code={code}&api_token={token}</span>
        </div>
        <p class="text-muted">
            Mengecek transaksi sukses terakhir untuk perangkat spesifik dalam kurun waktu 1 jam.
            <strong>Catatan:</strong> Memanggil endpoint ini akan otomatis mengubah status antrean transaksi menjadi <code>false</code> (sudah diambil).
        </p>

        <div class="row g-4">


            <div class="col-md-6">
                <small class="text-uppercase fw-bold text-success mb-2 d-block" style="font-size: 10px;">Success Response</small>
                <pre>{
  "status": "success",
  "message": {
    "order_id": "TRX-OUT-1768960238",
    "payment_status": "success",
    "device_status": 1,
    "amount": 13000,
    "description": "Pembayaran Berhasil.",
    "qr_code_deleted": true
  }
}</pre>
            </div>
            <div class="col-md-6">
                <small class="text-uppercase fw-bold text-danger mb-2 d-block" style="font-size: 10px;">Error / Not Found</small>
                <pre>{
  "status": "error",
  "message": {
    "order_id": null,
    "description": "Order not found or payment not yet successful."
  }
}</pre>
            </div>
        </div>
    </div>
</section>

            <section id="bypass" class="mb-5">
                <h3 class="fw-bold mb-4 d-flex align-items-center">
                    <span class="p-2 bg-light rounded-3 me-3"><i class="fas fa-microchip text-warning"></i></span>
                    BYPASS
                </h3>

                <div class="iot-box">
                    <div class="d-flex align-items-center mb-4">
                        <span class="method" style="background: #f97316;">GET</span>
                        <span class="h5 mb-0 text-white font-monospace">/check-device?device_code={code}&api_token={token}</span>
                    </div>


                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2);">
                                <small class="text-success fw-bold d-block mb-2 text-uppercase"><i class="fas fa-bolt me-1"></i> Aktivasi Relay</small>
                                <pre class="m-0 border-0" style="background: transparent;">{
  "status": "success",
  "status_device": "dryer_a",
  "source": "bypass",
  "activation_date": "2026-01-19 16:12:46"
}</pre>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="p-3 rounded-3" style="background: rgba(148, 163, 184, 0.1); border: 1px solid rgba(148, 163, 184, 0.2);">
                                <small class="text-secondary fw-bold d-block mb-2 text-uppercase"><i class="fas fa-sleep me-1"></i> Mode Standby</small>
                                <pre class="m-0 border-0" style="background: transparent;">{
  "status": "success",
  "status_device": "off",
  "source": null
}</pre>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-4 rounded-4" style="background: rgba(249, 115, 22, 0.1); border-left: 4px solid #f97316;">
                        <h6 class="text-warning fw-bold mb-2 text-uppercase"><i class="fas fa-exclamation-triangle me-2"></i>Panduan Hardware</h6>
                        <ul class="small text-secondary mb-0">
                            <li>Segera aktifkan relay jika <code>status_device</code> mengembalikan nilai selain <code>off</code>.</li>
                            <li>Kirim <code>api_token</code> di setiap request untuk menghindari 401 Unauthorized.</li>
                            <li>Pastikan hardware menangani timeout jika server tidak merespon dalam 10 detik.</li>
                        </ul>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Smooth scrolling & Sidebar active state
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Update active sidebar on scroll
    window.addEventListener('scroll', () => {
        let current = "";
        const sections = document.querySelectorAll("section");
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            if (pageYOffset >= sectionTop - 150) {
                current = section.getAttribute("id");
            }
        });

        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href').includes(current)) {
                link.classList.add('active');
            }
        });
    });
</script>
</body>
</html>
