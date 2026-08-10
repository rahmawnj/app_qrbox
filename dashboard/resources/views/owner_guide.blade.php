<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>QRBox — Owner Guide</title>
    <style>
        :root{--bg:#071312;--panel:#0d1c1a;--panel2:#102522;--text:#effffb;--muted:#9bb8b2;--line:#21433d;--accent:#56dfcf;--accent2:#b8f56f;--warn:#ffd166;--danger:#ff8b8b}
        *{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:radial-gradient(circle at 80% 0,#103a33 0,transparent 34%),var(--bg);color:var(--text);font:15px/1.7 Inter,system-ui,-apple-system,Segoe UI,sans-serif}a{color:inherit;text-decoration:none}.layout{display:grid;grid-template-columns:250px 1fr;min-height:100vh}.side{position:sticky;top:0;height:100vh;padding:26px 18px;border-right:1px solid var(--line);background:rgba(5,15,14,.92);backdrop-filter:blur(12px)}.logo{display:flex;gap:11px;align-items:center;margin-bottom:28px}.mark{width:42px;height:42px;border-radius:12px;background:var(--accent);color:#06201c;display:grid;place-items:center;font-weight:900}.logo b{display:block}.logo small{color:var(--muted)}.nav-title{margin:20px 9px 7px;color:#6f928b;font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}.nav a{display:block;padding:9px 10px;border-radius:9px;color:#b8ccc8}.nav a:hover{background:var(--panel2);color:#fff}.main{max-width:1120px;width:100%;padding:30px 34px 70px;margin:auto}.hero{padding:38px;border:1px solid var(--line);border-radius:22px;background:linear-gradient(135deg,rgba(86,223,207,.12),rgba(184,245,111,.04));box-shadow:0 20px 70px rgba(0,0,0,.18)}.eyebrow{color:var(--accent);font-weight:800;text-transform:uppercase;letter-spacing:.12em;font-size:11px}.hero h1{font-size:clamp(36px,6vw,64px);line-height:1.05;margin:10px 0}.hero p{max-width:760px;color:var(--muted);font-size:17px}.badges{display:flex;gap:8px;flex-wrap:wrap;margin-top:20px}.badge{padding:6px 10px;border:1px solid var(--line);border-radius:99px;color:#cce9e4;background:#0b201d}.section{margin-top:42px;scroll-margin-top:24px}.section h2{font-size:28px;margin:0 0 7px}.lead{color:var(--muted);margin:0 0 18px}.grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}.card{padding:20px;border:1px solid var(--line);border-radius:16px;background:rgba(13,28,26,.86)}.card h3{margin:0 0 7px}.card p{margin:0;color:var(--muted)}.num{width:32px;height:32px;border-radius:9px;display:grid;place-items:center;background:#153a34;color:var(--accent);font-weight:900;margin-bottom:13px}.flow{display:grid;gap:10px}.flow-item{position:relative;padding:16px 18px 16px 50px;border:1px solid var(--line);border-radius:13px;background:var(--panel)}.flow-item:before{content:'→';position:absolute;left:18px;top:15px;color:var(--accent);font-weight:900}.flow-item:last-child:before{content:'✓'}.flow-item b{display:block}.flow-item span{color:var(--muted);font-size:13px}.credential{display:grid;grid-template-columns:1fr 1fr;gap:12px}.secret{padding:16px;border:1px dashed #3b6960;border-radius:13px;background:#091815}.secret strong{display:block;color:var(--accent)}code{font-family:ui-monospace,SFMono-Regular,Consolas,monospace;color:#d8fff8}.menu{display:inline-block;padding:3px 8px;border-radius:6px;background:#183a34;color:var(--accent);font-size:12px}.warn{border-color:#5e4d23;background:#211d0d}.warn strong{color:var(--warn)}.danger{border-color:#5e2d2d;background:#210f0f}.danger strong{color:var(--danger)}.api{font-family:ui-monospace,monospace;padding:13px 15px;border-radius:10px;background:#06100f;border:1px solid var(--line);overflow:auto;color:#bcefe7}.check{display:grid;gap:9px}.check div{padding:12px 14px;border:1px solid var(--line);border-radius:10px;color:#cfe3df}.check div:before{content:'✓';color:var(--accent);margin-right:9px;font-weight:900}.footer{text-align:center;color:#66817c;padding-top:45px}@media(max-width:850px){.layout{display:block}.side{position:static;height:auto;border-right:0;border-bottom:1px solid var(--line)}.nav{display:flex;flex-wrap:wrap;gap:5px}.nav-title{display:none}.nav a{padding:7px 9px}.main{padding:18px}.grid,.credential{grid-template-columns:1fr}.hero{padding:25px}}
    </style>
</head>
<body>
<div class="layout">
<aside class="side">
    <div class="logo"><div class="mark">QR</div><div><b>QRBox</b><small>Owner Guide</small></div></div>
    <div class="nav-title">Panduan</div>
    <nav class="nav">
        <a href="#start">Mulai</a><a href="#device">Device</a><a href="#setup">Setup IoT</a><a href="#service">Service</a><a href="#payment">Pembayaran</a><a href="#iot">API & IoT</a><a href="#bypass">Bypass</a><a href="#trouble">Troubleshooting</a>
    </nav>
</aside>
<main class="main">
<section class="hero" id="start">
    <div class="eyebrow">QRBox Owner Guide</div>
    <h1>Dari device sampai mesin aktif.</h1>
    <p>Panduan singkat untuk Owner setelah device QRBox sudah didaftarkan oleh Admin. Ikuti urutan ini untuk menghubungkan device, mengatur layanan, menerima pembayaran, dan menjalankan mesin melalui IoT.</p>
    <div class="badges"><span class="badge">Tidak perlu generate token sendiri</span><span class="badge">Device Code + Token → IoT</span><span class="badge">Bypass untuk testing</span></div>
</section>

<section class="section">
    <h2>Alur utama</h2><p class="lead">Urutan kerja QRBox dari provisioning sampai mesin aktif.</p>
    <div class="flow">
        <div class="flow-item"><b>Admin mendaftarkan device</b><span>Device dibuat di sistem dan credential disiapkan.</span></div>
        <div class="flow-item"><b>Owner menerima Device Code + Token</b><span>Credential ini digunakan oleh firmware IoT untuk berkomunikasi dengan QRBox.</span></div>
        <div class="flow-item"><b>Owner mengatur Service / Harga</b><span>Gunakan menu <span class="menu">Device</span> dan pengaturan service yang tersedia.</span></div>
        <div class="flow-item"><b>IoT terhubung ke WiFi & QRBox API</b><span>IoT melakukan komunikasi dengan server menggunakan credential device.</span></div>
        <div class="flow-item"><b>Customer melakukan pembayaran</b><span>Customer membayar layanan melalui QR/payment gateway.</span></div>
        <div class="flow-item"><b>QRBox menerima status pembayaran</b><span>Server memproses status transaksi sebelum memberi izin aktivasi.</span></div>
        <div class="flow-item"><b>Command dikirim ke IoT</b><span>IoT menerima perintah untuk mengaktifkan service.</span></div>
        <div class="flow-item"><b>Relay ON → mesin aktif</b><span>Relay menghubungkan/menyalakan mesin sesuai konfigurasi hardware.</span></div>
    </div>
</section>

<section class="section" id="device">
    <h2>1. Device sudah didaftarkan Admin</h2><p class="lead">Owner tidak perlu membuat device dari nol jika Admin sudah melakukan provisioning.</p>
    <div class="credential">
        <div class="secret"><strong>Device Code</strong><p>Identitas device yang dipakai IoT saat berkomunikasi dengan QRBox.</p></div>
        <div class="secret"><strong>Token</strong><p>Credential autentikasi device. Simpan dengan aman dan jangan dipasang di dokumentasi publik.</p></div>
    </div>
    <div class="card warn" style="margin-top:14px"><strong>Penting</strong><p>Token diberikan oleh sistem/Admin. Owner tidak perlu men-generate token sendiri. Jika token perlu diganti, gunakan mekanisme regenerate token yang disediakan Admin.</p></div>
</section>

<section class="section" id="setup">
    <h2>2. Masukkan credential ke IoT</h2><p class="lead">Pada firmware IoT, simpan dua nilai dari Admin lalu sambungkan device ke WiFi.</p>
    <div class="grid">
        <div class="card"><div class="num">01</div><h3>Isi Device Code</h3><p>Masukkan Device Code sesuai device yang diberikan.</p></div>
        <div class="card"><div class="num">02</div><h3>Isi Token</h3><p>Masukkan Token yang diberikan Admin. Jangan hard-code token contoh dari dokumentasi.</p></div>
        <div class="card"><div class="num">03</div><h3>Konfigurasi WiFi</h3><p>Pastikan IoT mendapatkan koneksi internet yang stabil.</p></div>
        <div class="card"><div class="num">04</div><h3>Test koneksi API</h3><p>Pastikan device bisa mengakses endpoint QRBox dan mendapatkan respons yang valid.</p></div>
    </div>
</section>

<section class="section" id="service">
    <h2>3. Atur Service & Harga</h2><p class="lead">Setelah device siap, Owner mengatur layanan yang bisa dibeli customer.</p>
    <div class="grid">
        <div class="card"><h3>Outlet</h3><p>Pastikan device berada pada outlet yang benar melalui menu <span class="menu">Outlet</span>.</p></div>
        <div class="card"><h3>Service</h3><p>Pilih layanan/mesin yang tersedia pada device melalui menu pengelolaan device/service.</p></div>
        <div class="card"><h3>Harga</h3><p>Pastikan harga service sudah sesuai sebelum QR digunakan customer.</p></div>
        <div class="card"><h3>Status device</h3><p>Pastikan device aktif/online sebelum melakukan transaksi pengujian.</p></div>
    </div>
</section>

<section class="section" id="payment">
    <h2>4. Pembayaran → mesin aktif</h2><p class="lead">Ini adalah alur normal ketika customer membayar.</p>
    <div class="flow">
        <div class="flow-item"><b>Customer memilih mesin/service</b><span>Service dan harga ditentukan dari konfigurasi QRBox.</span></div>
        <div class="flow-item"><b>Customer scan QR & membayar</b><span>Pembayaran diproses oleh payment gateway.</span></div>
        <div class="flow-item"><b>QRBox menerima status pembayaran</b><span>Server memvalidasi transaksi dan statusnya.</span></div>
        <div class="flow-item"><b>QRBox mengirim command ke IoT</b><span>Command aktivasi diteruskan ke device terkait.</span></div>
        <div class="flow-item"><b>IoT mengaktifkan relay</b><span>Relay ON sesuai durasi/logic service.</span></div>
    </div>
</section>

<section class="section" id="iot">
    <h2>5. Cara kerja API ↔ IoT</h2><p class="lead">Secara sederhana, IoT tidak memproses pembayaran sendiri. IoT berkomunikasi dengan server QRBox.</p>
    <div class="api">CUSTOMER
   ↓
PAYMENT GATEWAY
   ↓ payment success
QRBOX SERVER / API
   ↓ command
IOT DEVICE
   ↓ GPIO
RELAY
   ↓
MESIN</div>
    <div class="grid" style="margin-top:15px">
        <div class="card"><h3>Request dari IoT</h3><p>IoT mengirim identitas device/credential untuk meminta informasi atau status yang diperlukan.</p></div>
        <div class="card"><h3>Respons API</h3><p>Server mengembalikan data yang dibutuhkan device untuk melanjutkan proses.</p></div>
        <div class="card"><h3>Payment event</h3><p>Status pembayaran menjadi sumber keputusan apakah transaksi boleh mengaktifkan mesin.</p></div>
        <div class="card"><h3>Activation command</h3><p>Setelah kondisi terpenuhi, IoT menjalankan command dan mengontrol relay.</p></div>
    </div>
</section>

<section class="section" id="bypass">
    <h2>6. Bypass — menyalakan mesin tanpa pembayaran</h2><p class="lead">Bypass digunakan untuk kebutuhan operasional seperti testing, maintenance, atau pengecekan mesin.</p>
    <div class="card warn"><strong>Kapan digunakan?</strong><p>Gunakan bypass ketika perlu menyalakan mesin tanpa transaksi customer, misalnya setelah instalasi, test relay, maintenance, atau demo.</p></div>
    <div class="flow" style="margin-top:15px">
        <div class="flow-item"><b>Buka menu Bypass</b><span>Pada dashboard Owner, masuk ke fitur bypass yang tersedia.</span></div>
        <div class="flow-item"><b>Pilih device/mesin</b><span>Tentukan device dan service yang ingin dijalankan.</span></div>
        <div class="flow-item"><b>Jalankan bypass</b><span>Sistem memberi izin aktivasi tanpa menunggu pembayaran normal.</span></div>
        <div class="flow-item"><b>Relay ON → mesin aktif</b><span>IoT menerima perintah dan menjalankan relay sesuai implementasi.</span></div>
        <div class="flow-item"><b>Cek Bypass Logs</b><span>Gunakan menu <span class="menu">Bypass Logs</span> untuk melihat aktivitas bypass.</span></div>
    </div>
    <div class="card danger" style="margin-top:15px"><strong>Jangan gunakan bypass untuk transaksi customer.</strong><p>Bypass melewati alur pembayaran normal. Gunakan hanya untuk kebutuhan operasional yang sah dan pastikan aktivitasnya tercatat.</p></div>
</section>

<section class="section" id="trouble">
    <h2>7. Kalau mesin tidak menyala</h2><p class="lead">Periksa dari atas ke bawah sebelum menghubungi Admin.</p>
    <div class="check">
        <div>IoT terhubung ke WiFi/internet.</div>
        <div>Device Code sesuai device yang diberikan Admin.</div>
        <div>Token masih valid dan belum diregenerate.</div>
        <div>Device terdaftar dan statusnya aktif.</div>
        <div>Service dan harga sudah dikonfigurasi.</div>
        <div>Pembayaran customer benar-benar berstatus berhasil.</div>
        <div>API QRBox dapat diakses oleh IoT.</div>
        <div>IoT menerima command aktivasi.</div>
        <div>Relay dan wiring mesin bekerja dengan benar.</div>
        <div>Untuk testing tanpa pembayaran, gunakan Bypass lalu cek Bypass Logs.</div>
    </div>
</section>

<div class="footer">QRBox Owner Guide · Gunakan credential device dengan aman.</div>
</main></div>
</body>
</html>
