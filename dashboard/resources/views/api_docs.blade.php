<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QRBox API Docs & Playground</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #172033;
            --muted: #64748b;
            --line: #dbe2ea;
            --paper: #f4f7fb;
            --panel: #ffffff;
            --accent: #0f766e;
            --accent-dark: #0b5f59;
            --accent-soft: #e5f6f3;
            --blue: #2563eb;
            --blue-soft: #eff6ff;
            --orange: #ea580c;
            --red: #dc2626;
            --green: #15803d;
            --code: #0d1726;
            --shadow: 0 10px 35px rgba(15, 23, 42, .07);
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            background: var(--paper);
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            line-height: 1.55;
        }
        a { color: inherit; text-decoration: none; }
        code, pre, input, textarea, select, button { font-family: "Cascadia Code", "Fira Code", Consolas, monospace; }

        .shell { display: grid; grid-template-columns: 270px minmax(0, 1fr); min-height: 100vh; }
        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            padding: 24px 18px;
            background: #0d1726;
            color: #eef4ff;
            overflow-y: auto;
        }
        .brand { display: flex; align-items: center; gap: 11px; margin-bottom: 26px; }
        .brand-mark {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 11px;
            background: var(--accent);
            color: white;
            font-weight: 900;
            letter-spacing: -.08em;
        }
        .brand-title { font-weight: 900; font-size: 16px; }
        .brand-subtitle { color: #94a3b8; font-size: 11px; margin-top: 2px; }
        .nav-label {
            margin: 22px 8px 8px;
            color: #64748b;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .12em;
        }
        .nav-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 9px 10px;
            margin: 2px 0;
            border-radius: 8px;
            color: #cbd5e1;
            font-size: 12px;
        }
        .nav-link:hover { background: #162337; color: #fff; }
        .pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            padding: 3px 7px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 900;
        }
        .get { background: #dbeafe; color: #1d4ed8; }
        .post { background: #dcfce7; color: #15803d; }

        .main { padding: 30px clamp(18px, 4vw, 50px) 70px; }
        .content { max-width: 1240px; margin: 0 auto; }
        .hero {
            padding: 28px;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: linear-gradient(135deg, #ffffff 0%, #f0fdfa 100%);
            box-shadow: var(--shadow);
        }
        h1 { margin: 0 0 8px; font-size: clamp(25px, 4vw, 38px); letter-spacing: -.04em; }
        h2 { margin: 0; font-size: 21px; letter-spacing: -.025em; }
        h3 { margin: 7px 0 0; font-size: 16px; }
        p { color: var(--muted); }
        .hero p { max-width: 900px; margin-bottom: 0; }

        .toolbar {
            display: grid;
            grid-template-columns: minmax(250px, 1.4fr) minmax(220px, 1fr);
            gap: 14px;
            margin: 18px 0;
        }
        .panel {
            border: 1px solid var(--line);
            border-radius: 14px;
            background: var(--panel);
            padding: 18px;
            box-shadow: var(--shadow);
        }
        .panel-title { font-size: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: .07em; color: #475569; margin-bottom: 10px; }
        .config-row { display: flex; gap: 8px; }
        .config-row input { flex: 1; }

        label {
            display: block;
            margin: 0 0 6px;
            color: #475569;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .07em;
        }
        input, textarea, select {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            background: #fff;
            color: var(--ink);
            padding: 10px 12px;
            font-size: 12px;
            outline: none;
        }
        textarea { min-height: 150px; resize: vertical; line-height: 1.5; }
        input:focus, textarea:focus, select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(15, 118, 110, .12);
        }
        button {
            border: 0;
            border-radius: 9px;
            padding: 10px 13px;
            background: var(--accent);
            color: #fff;
            font-weight: 900;
            font-size: 12px;
            cursor: pointer;
        }
        button:hover { background: var(--accent-dark); }
        button.secondary { background: #334155; }
        button.secondary:hover { background: #1e293b; }
        button.light { background: #e2e8f0; color: #334155; }
        button.danger { background: var(--red); }
        button:disabled { opacity: .55; cursor: not-allowed; }

        .device-console {
            margin: 18px 0 28px;
            border: 1px solid #a7e3dc;
            border-radius: 16px;
            background: var(--accent-soft);
            padding: 18px;
        }
        .device-console-head {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 14px;
        }
        .device-console-head h2 { color: #115e59; }
        .device-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr 1fr 1fr;
            gap: 10px;
        }
        .device-stat {
            padding: 12px;
            border: 1px solid rgba(15,118,110,.16);
            border-radius: 10px;
            background: rgba(255,255,255,.7);
        }
        .device-stat small {
            display: block;
            color: #64748b;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .device-stat strong {
            display: block;
            margin-top: 3px;
            word-break: break-word;
            font-size: 13px;
        }
        .console-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }

        section { max-width: 1240px; margin: 0 auto 26px; scroll-margin-top: 20px; }
        .section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin: 30px 0 13px;
        }
        .section-head p { margin: 4px 0 0; font-size: 12px; }

        .endpoint-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 15px; }
        .card {
            border: 1px solid var(--line);
            border-radius: 14px;
            background: var(--panel);
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        .card-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 17px;
            border-bottom: 1px solid var(--line);
        }
        .card-title-wrap { min-width: 0; }
        .endpoint {
            margin-top: 7px;
            color: #475569;
            font-size: 11px;
            word-break: break-all;
        }
        .card-body { padding: 17px; }
        .description { margin: 0 0 14px; font-size: 12px; }
        .fields {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 14px;
        }
        .full { grid-column: 1 / -1; }
        .body-wrap { margin-top: 4px; }
        .actions {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 12px;
        }
        .status-line { color: #64748b; font-size: 11px; margin-left: auto; }
        .status-ok { color: var(--green); font-weight: 800; }
        .status-error { color: var(--red); font-weight: 800; }

        pre {
            margin: 11px 0 0;
            padding: 13px;
            border-radius: 10px;
            background: var(--code);
            color: #dbeafe;
            overflow: auto;
            font-size: 11px;
            line-height: 1.55;
        }
        .response { min-height: 100px; white-space: pre-wrap; word-break: break-word; }
        .request-preview { min-height: 65px; }
        .code-label {
            margin-top: 15px;
            color: #475569;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .07em;
        }
        .hint {
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            background: #f8fafc;
            color: #475569;
            font-size: 12px;
        }
        .hint strong { color: var(--ink); }
        .empty {
            padding: 24px;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            background: #fff;
            color: #64748b;
            text-align: center;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid var(--line);
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
        }
        .table th, .table td {
            padding: 11px 13px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            font-size: 12px;
        }
        .table th {
            background: #edf2f7;
            color: #475569;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .07em;
        }
        .table tr:last-child td { border-bottom: 0; }
        .copy { background: #475569; padding: 8px 10px; font-size: 10px; }
        .copy:hover { background: #334155; }
        .token-row { display: flex; gap: 8px; }
        .token-row input { flex: 1; }
        .muted { color: #64748b; }
        .mono { font-family: "Cascadia Code", "Fira Code", Consolas, monospace; }

        @media (max-width: 1050px) {
            .shell { grid-template-columns: 1fr; }
            .sidebar { position: static; height: auto; }
            .toolbar, .endpoint-list, .device-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 700px) {
            .main { padding: 16px; }
            .toolbar, .endpoint-list, .device-grid, .fields { grid-template-columns: 1fr; }
            .full { grid-column: auto; }
            .status-line { width: 100%; margin-left: 0; }
            .config-row, .token-row { flex-direction: column; }
        }
    </style>
</head>
<body>
<div class="shell">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-mark">QR</div>
            <div>
                <div class="brand-title">QRBox API</div>
                <div class="brand-subtitle">Docs & Device Playground</div>
            </div>
        </div>

        <div class="nav-label">Dokumentasi</div>
        <a class="nav-link" href="#overview">Overview</a>
        <a class="nav-link" href="#device-console">Device Console</a>
        <a class="nav-link" href="#device-api">Device API</a>
        <a class="nav-link" href="#payment-api">Payment API</a>
        <a class="nav-link" href="#callback-api">Callback</a>

        <div class="nav-label">Endpoint</div>
        <a class="nav-link" href="#device-menu"><span>Device Menu</span><span class="pill get">GET</span></a>
        <a class="nav-link" href="#update-status"><span>Update Status</span><span class="pill post">POST</span></a>
        <a class="nav-link" href="#check-device"><span>Check Device</span><span class="pill get">GET</span></a>
        <a class="nav-link" href="#device-price"><span>Device Price</span><span class="pill get">GET</span></a>
        <a class="nav-link" href="#qr-request"><span>QR Request</span><span class="pill post">POST</span></a>
        <a class="nav-link" href="#payment-check"><span>Payment Check</span><span class="pill get">GET</span></a>
        <a class="nav-link" href="#payment-check-2"><span>Payment Check 2</span><span class="pill get">GET</span></a>
        <a class="nav-link" href="#payment-status-update"><span>Callback</span><span class="pill post">POST</span></a>
    </aside>

    <main class="main">
        <div class="content">
            <section id="overview" class="hero">
                <h1>QRBox API Docs & Playground</h1>
                <p>
                    Playground khusus untuk testing API yang terhubung ke device QRBox.
                    Pilih device sekali di atas, lalu endpoint di bawah otomatis memakai
                    device code, device ID, service type, menu, dan parameter terkait device tersebut.
                </p>
            </section>

            <div class="toolbar">
                <div class="panel">
                    <div class="panel-title">Base API URL</div>
                    <div class="config-row">
                        <input id="baseUrl" value="{{ url('/') }}" placeholder="http://127.0.0.1:8000">
                        <button type="button" id="applyBase">Apply</button>
                    </div>
                    <div class="muted" style="font-size:11px;margin-top:7px;">
                        Playground otomatis menambahkan <code>/api</code>.
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-title">API Token Device</div>
                    <div class="token-row">
                        <input id="apiToken" type="password" placeholder="Masukkan device_token outlet">
                        <button type="button" class="secondary" id="toggleToken">Show</button>
                    </div>
                    <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap;">
                        <button type="button" class="light" id="saveToken">Save Local</button>
                        <button type="button" class="light" id="clearToken">Clear</button>
                    </div>
                    <div class="muted" style="font-size:11px;margin-top:7px;">
                        Token hanya disimpan di browser lokal jika tombol Save Local dipakai.
                    </div>
                </div>
            </div>

            <section id="device-console">
                <div class="section-head">
                    <div>
                        <h2>Device Console</h2>
                        <p>Ambil device langsung dari database QRBox.</p>
                    </div>
                </div>

                <div class="device-console">
                    <div class="device-console-head">
                        <div style="flex:1">
                            <h2>Testing Device</h2>
                            <p style="margin:4px 0 0;color:#0f766e;font-size:12px;">
                                Setelah memilih device, semua playground akan mengikuti device ini.
                            </p>
                        </div>
                        <button type="button" class="secondary" id="refreshDevice">Refresh Device Data</button>
                    </div>

                    <div class="fields" style="margin-bottom:10px;">
                        <div class="full">
                            <label for="deviceSelect">Pilih Device</label>
                            <select id="deviceSelect">
                                <option value="">-- pilih device --</option>
                            </select>
                        </div>
                    </div>

                    <div class="device-grid">
                        <div class="device-stat">
                            <small>Device ID</small>
                            <strong id="deviceId">-</strong>
                        </div>
                        <div class="device-stat">
                            <small>Device Code</small>
                            <strong id="deviceCode">-</strong>
                        </div>
                        <div class="device-stat">
                            <small>Status</small>
                            <strong id="deviceStatus">-</strong>
                        </div>
                        <div class="device-stat">
                            <small>Service Type</small>
                            <strong id="deviceService">-</strong>
                        </div>
                    </div>

                    <div class="device-grid" style="margin-top:10px;">
                        <div class="device-stat">
                            <small>Outlet</small>
                            <strong id="deviceOutlet">-</strong>
                        </div>
                        <div class="device-stat">
                            <small>Service Type ID</small>
                            <strong id="serviceTypeId">-</strong>
                        </div>
                        <div class="device-stat">
                            <small>Menu Aktif</small>
                            <strong id="menuCount">0</strong>
                        </div>
                        <div class="device-stat">
                            <small>Menu Default</small>
                            <strong id="defaultMenu">-</strong>
                        </div>
                    </div>

                    <div class="console-actions">
                        <button type="button" id="copyDeviceConfig">Copy Device Config</button>
                        <button type="button" class="secondary" id="runHealthCheck">Run Health Check</button>
                        <button type="button" class="light" id="runDeviceMenu">Load Device Menu</button>
                    </div>

                    <pre id="deviceConfigPreview" style="margin-top:12px;">Belum ada device yang dipilih.</pre>
                </div>
            </section>

            <section id="device-api">
                <div class="section-head">
                    <div>
                        <h2>Device API</h2>
                        <p>Endpoint yang dipakai device untuk membaca konfigurasi, status, dan harga.</p>
                    </div>
                </div>
                <div class="endpoint-list">
                    <div id="device-menu"></div>
                    <div id="update-status"></div>
                    <div id="check-device"></div>
                    <div id="device-price"></div>
                </div>
            </section>

            <section id="payment-api">
                <div class="section-head">
                    <div>
                        <h2>Payment API</h2>
                        <p>Flow testing pembayaran QRIS dari device sampai polling status.</p>
                    </div>
                </div>
                <div class="endpoint-list">
                    <div id="qr-request"></div>
                    <div id="payment-check"></div>
                    <div id="payment-check-2"></div>
                </div>
            </section>

            <section id="callback-api">
                <div class="section-head">
                    <div>
                        <h2>Callback / Simulator</h2>
                        <p>Simulasikan callback payment gateway untuk mengaktifkan device transaction.</p>
                    </div>
                </div>
                <div id="payment-status-update"></div>
                <div class="hint" style="margin-top:12px;">
                    <strong>Flow testing:</strong>
                    QR Request → simpan <code>order_id</code> → lakukan pembayaran di QRIS/Midtrans →
                    atau simulasi callback settlement menggunakan endpoint Callback → jalankan Payment Check.
                </div>
            </section>
        </div>
    </main>
</div>

<template id="playground-card-template">
    <article class="card">
        <div class="card-head">
            <div class="card-title-wrap">
                <span class="pill method-pill"></span>
                <h3 class="card-title"></h3>
                <div class="endpoint"></div>
            </div>
            <button class="copy" type="button">Copy cURL</button>
        </div>
        <div class="card-body">
            <p class="description"></p>
            <div class="fields"></div>
            <div class="body-wrap">
                <label>Request Body JSON</label>
                <textarea class="request-body"></textarea>
            </div>
            <div class="code-label">Request</div>
            <pre class="request-preview">-</pre>
            <div class="actions">
                <button class="send" type="button">Send Request</button>
                <button class="secondary fill" type="button">Reset</button>
                <span class="status-line">Ready</span>
            </div>
            <div class="code-label">Response</div>
            <pre class="response">{}</pre>
        </div>
    </article>
</template>

<script>
    const devices = @json($devices);

    const state = {
        selectedDeviceId: localStorage.getItem('qrbox_selected_device_id') || '',
        apiToken: localStorage.getItem('qrbox_api_token') || '',
        baseUrl: localStorage.getItem('qrbox_api_base_url') || document.getElementById('baseUrl').value,
        lastOrderId: localStorage.getItem('qrbox_last_order_id') || ''
    };

    const globals = {
        baseUrl: document.getElementById('baseUrl'),
        apiToken: document.getElementById('apiToken'),
        deviceSelect: document.getElementById('deviceSelect')
    };

    globals.baseUrl.value = state.baseUrl;
    globals.apiToken.value = state.apiToken;

    const cards = [
        {
            id: 'device-menu',
            method: 'GET',
            title: 'Get Device Menu',
            endpoint: '/device-menu/{device_code}',
            description: 'Mengambil konfigurasi menu yang benar-benar tersimpan pada device terpilih.',
            fields: [
                { key: 'device_code', label: 'Device Code', source: 'device.code' }
            ],
            body: null,
            auth: false
        },
        {
            id: 'update-status',
            method: 'POST',
            title: 'Update Device Status',
            endpoint: '/devices/{device_id}/update-status',
            description: 'Mengubah status device untuk simulasi bypass. Contoh: washer, dryer_a, dryer_b, atau off.',
            fields: [
                { key: 'device_id', label: 'Device ID', source: 'device.id' }
            ],
            body: {
                device_status: 'washer',
                bypass_note: 'Testing dari QRBox API Playground'
            },
            auth: false
        },
        {
            id: 'check-device',
            method: 'GET',
            title: 'Check Device Status',
            endpoint: '/check-device?device_code={device_code}&api_token={api_token}',
            description: 'Endpoint polling device. Jika ada bypass/session aktif, endpoint ini mengembalikan status layanan dan kemudian mengonsumsi activation tersebut.',
            fields: [
                { key: 'device_code', label: 'Device Code', source: 'device.code' },
                { key: 'api_token', label: 'API Token', source: 'apiToken', secret: true }
            ],
            body: null,
            auth: true
        },
        {
            id: 'device-price',
            method: 'GET',
            title: 'Device Price',
            endpoint: '/device-price/{device_id}/{service_type_id}',
            description: 'Mengambil harga service type dari pivot device_service_type untuk device terpilih.',
            fields: [
                { key: 'device_id', label: 'Device ID', source: 'device.id' },
                { key: 'service_type_id', label: 'Service Type ID', source: 'device.service_type_id' }
            ],
            body: null,
            auth: false
        },
        {
            id: 'qr-request',
            method: 'POST',
            title: 'QR Request',
            endpoint: '/qr-request',
            description: 'Membuat transaksi QRIS berdasarkan menu/service type device terpilih. Endpoint ini memanggil Midtrans sungguhan.',
            fields: [
                { key: 'device_code', label: 'Device Code', source: 'device.code' },
                { key: 'api_token', label: 'API Token', source: 'apiToken', secret: true },
                { key: 'type', label: 'Service Type / Menu', source: 'defaultMenuType', editable: true }
            ],
            body: {
                type: '',
                device_code: '',
                api_token: ''
            },
            auth: true
        },
        {
            id: 'payment-check',
            method: 'GET',
            title: 'Payment Check',
            endpoint: '/payment-check?order_id={order_id}&api_token={api_token}',
            description: 'Polling status pembayaran berdasarkan order_id. Setelah sukses, DeviceTransaction akan dikonsumsi oleh endpoint.',
            fields: [
                { key: 'order_id', label: 'Order ID', source: 'lastOrderId', editable: true },
                { key: 'api_token', label: 'API Token', source: 'apiToken', secret: true }
            ],
            body: null,
            auth: true
        },
        {
            id: 'payment-check-2',
            method: 'GET',
            title: 'Payment Check 2',
            endpoint: '/payment-check-2?device_code={device_code}&service_type={service_type}&api_token={api_token}',
            description: 'Polling transaksi sukses terbaru untuk device dan service type tertentu.',
            fields: [
                { key: 'device_code', label: 'Device Code', source: 'device.code' },
                { key: 'service_type', label: 'Service Type', source: 'defaultMenuType', editable: true },
                { key: 'api_token', label: 'API Token', source: 'apiToken', secret: true }
            ],
            body: null,
            auth: true
        },
        {
            id: 'payment-status-update',
            method: 'POST',
            title: 'Payment Status Update / Callback Simulator',
            endpoint: '/payment-status-update',
            description: 'Simulasikan notification Midtrans. Untuk mengaktifkan device, gunakan transaction_status settlement/capture pada order_id transaksi yang ada.',
            fields: [],
            body: {
                order_id: '',
                transaction_status: 'settlement',
                gross_amount: '13000.00',
                payment_type: 'qris',
                settlement_time: ''
            },
            auth: false
        }
    ];

    function pretty(value) {
        return JSON.stringify(value, null, 2);
    }

    function selectedDevice() {
        return devices.find(device => String(device.id) === String(state.selectedDeviceId)) || devices[0] || null;
    }

    function activeMenus(device) {
        return Array.isArray(device?.menus) ? device.menus : [];
    }

    function defaultMenu(device) {
        return activeMenus(device)[0] || null;
    }

    function valueFromSource(source) {
        const device = selectedDevice();

        if (source === 'device.id') return device?.id ?? '';
        if (source === 'device.code') return device?.code ?? '';
        if (source === 'device.service_type_id') return device?.service_type_id ?? '';
        if (source === 'device.service_type') return device?.service_type ?? '';
        if (source === 'apiToken') return state.apiToken;
        if (source === 'lastOrderId') return state.lastOrderId;
        if (source === 'defaultMenuType') return defaultMenu(device)?.type ?? '';
        if (source === 'defaultMenuPrice') return defaultMenu(device)?.price ?? '';

        return '';
    }

    function normalizeBaseUrl() {
        const raw = (globals.baseUrl.value || '').trim();
        if (!raw) return window.location.origin + '/api';

        let base = raw.replace(/\\/+$/, '');
        base = base.replace(/\\/api$/i, '');
        return base + '/api';
    }

    function selectedToken() {
        return (globals.apiToken.value || '').trim();
    }

    function buildUrl(card, root) {
        let endpoint = card.endpoint;

        card.fields.forEach(field => {
            const input = root.querySelector('[data-field="' + field.key + '"]');
            const value = input ? input.value : valueFromSource(field.source);
            endpoint = endpoint.replaceAll('{' + field.key + '}', encodeURIComponent(value));
        });

        return new URL(normalizeBaseUrl() + endpoint).toString();
    }

    function buildBody(card, root) {
        if (!card.body) return null;

        let body = {};
        try {
            body = JSON.parse(root.querySelector('.request-body').value || '{}');
        } catch (error) {
            throw new Error('Request body bukan JSON yang valid.');
        }

        card.fields.forEach(field => {
            const input = root.querySelector('[data-field="' + field.key + '"]');
            if (!input) return;

            if (field.source === 'device.code') body.device_code = input.value;
            if (field.source === 'apiToken') body.api_token = input.value;
            if (field.key === 'type') body.type = input.value;
        });

        return body;
    }

    function curlFor(card, root) {
        const url = buildUrl(card, root);
        const method = card.method;
        const body = buildBody(card, root);

        let command = 'curl -X ' + method + ' ' + JSON.stringify(url);
        command += ' -H "Accept: application/json"';

        if (body) {
            command += ' -H "Content-Type: application/json"';
            command += ' -d ' + JSON.stringify(JSON.stringify(body));
        }

        return command;
    }

    function syncQrOrderId(text) {
        try {
            const data = JSON.parse(text);
            const orderId = data?.message?.order_id;
            if (orderId) {
                state.lastOrderId = orderId;
                localStorage.setItem('qrbox_last_order_id', orderId);
            }
        } catch (error) {}
    }

    function updateDeviceConsole() {
        const device = selectedDevice();

        document.getElementById('deviceId').textContent = device?.id ?? '-';
        document.getElementById('deviceCode').textContent = device?.code ?? '-';
        document.getElementById('deviceStatus').textContent = device?.status ?? '-';
        document.getElementById('deviceService').textContent = device?.service_type || '-';
        document.getElementById('deviceOutlet').textContent = device?.outlet_name || '-';
        document.getElementById('serviceTypeId').textContent = device?.service_type_id ?? '-';

        const menus = activeMenus(device);
        document.getElementById('menuCount').textContent = menus.length;

        const first = defaultMenu(device);
        document.getElementById('defaultMenu').textContent = first
            ? (first.name || first.type || '-') + ' / Rp ' + Number(first.price || 0).toLocaleString('id-ID')
            : '-';

        document.getElementById('deviceConfigPreview').textContent = device
            ? pretty({
                id: device.id,
                code: device.code,
                name: device.name,
                status: device.status,
                outlet_id: device.outlet_id,
                outlet_name: device.outlet_name,
                service_type_id: device.service_type_id,
                service_type: device.service_type,
                menus: menus
            })
            : 'Belum ada device yang dipilih.';

        document.querySelectorAll('.device-dependent').forEach(element => {
            element.dispatchEvent(new CustomEvent('devicechanged'));
        });
    }

    function fillDeviceSelect() {
        globals.deviceSelect.innerHTML = '<option value="">-- pilih device --</option>';

        devices.forEach(device => {
            const option = document.createElement('option');
            option.value = device.id;
            option.textContent = device.name + ' — ' + device.code + (device.outlet_name ? ' — ' + device.outlet_name : '');
            globals.deviceSelect.appendChild(option);
        });

        if (selectedDevice()) {
            state.selectedDeviceId = String(selectedDevice().id);
            globals.deviceSelect.value = state.selectedDeviceId;
        }
    }

    function resetBody(card, root) {
        if (!card.body) return;
        const body = JSON.parse(JSON.stringify(card.body));
        const device = selectedDevice();

        if (card.id === 'qr-request') {
            body.type = defaultMenu(device)?.type || '';
            body.device_code = device?.code || '';
            body.api_token = selectedToken();
        }

        if (card.id === 'payment-status-update') {
            body.order_id = state.lastOrderId || '';
            body.settlement_time = new Date().toISOString().slice(0, 19).replace('T', ' ');
        }

        root.querySelector('.request-body').value = pretty(body);
    }

    function mountCard(card) {
        const host = document.getElementById(card.id);
        if (!host) return;

        const template = document.getElementById('playground-card-template');
        const node = template.content.firstElementChild.cloneNode(true);

        node.dataset.cardId = card.id;
        node.id = card.id;
        node.querySelector('.method-pill').textContent = card.method;
        node.querySelector('.method-pill').classList.add(card.method === 'GET' ? 'get' : 'post');
        node.querySelector('.card-title').textContent = card.title;
        node.querySelector('.endpoint').textContent = card.endpoint;
        node.querySelector('.description').textContent = card.description;

        const fields = node.querySelector('.fields');

        card.fields.forEach(field => {
            const wrap = document.createElement('div');
            wrap.className = field.key === 'order_id' ? 'full' : '';

            const label = document.createElement('label');
            label.textContent = field.label;
            wrap.appendChild(label);

            const input = document.createElement('input');
            input.dataset.field = field.key;
            input.type = field.secret ? 'password' : 'text';
            input.value = valueFromSource(field.source);
            input.readOnly = !field.editable;
            if (field.editable) input.style.background = '#fff';
            wrap.appendChild(input);
            fields.appendChild(wrap);
        });

        if (!card.body) {
            node.querySelector('.body-wrap').style.display = 'none';
        } else {
            resetBody(card, node);
        }

        function refreshFields() {
            card.fields.forEach(field => {
                const input = node.querySelector('[data-field="' + field.key + '"]');
                if (!input || field.editable) return;
                input.value = valueFromSource(field.source);
            });

            if (card.id === 'qr-request') {
                const typeInput = node.querySelector('[data-field="type"]');
                if (typeInput && !typeInput.value) typeInput.value = defaultMenu(selectedDevice())?.type || '';
            }

            resetBody(card, node);
            renderRequestPreview();
        }

        function renderRequestPreview() {
            try {
                const url = buildUrl(card, node);
                const body = buildBody(card, node);
                let text = card.method + ' ' + url;
                if (body) text += '\\n\\n' + pretty(body);
                node.querySelector('.request-preview').textContent = text;
            } catch (error) {
                node.querySelector('.request-preview').textContent = error.message;
            }
        }

        node.querySelector('.request-body')?.addEventListener('input', renderRequestPreview);

        card.fields.forEach(field => {
            node.querySelector('[data-field="' + field.key + '"]')?.addEventListener('input', renderRequestPreview);
        });

        node.querySelector('.copy').addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(curlFor(card, node));
                node.querySelector('.status-line').textContent = 'cURL copied';
                node.querySelector('.status-line').className = 'status-line status-ok';
            } catch (error) {
                node.querySelector('.status-line').textContent = 'Gagal copy cURL';
                node.querySelector('.status-line').className = 'status-line status-error';
            }
        });

        node.querySelector('.fill').addEventListener('click', () => {
            refreshFields();
            node.querySelector('.response').textContent = '{}';
            node.querySelector('.status-line').textContent = 'Example restored';
            node.querySelector('.status-line').className = 'status-line';
        });

        node.querySelector('.send').addEventListener('click', async () => {
            const status = node.querySelector('.status-line');
            const response = node.querySelector('.response');
            const sendButton = node.querySelector('.send');

            try {
                const url = buildUrl(card, node);
                const body = buildBody(card, node);

                sendButton.disabled = true;
                status.textContent = 'Sending...';
                status.className = 'status-line';
                response.textContent = 'Loading...';

                const options = {
                    method: card.method,
                    headers: {
                        'Accept': 'application/json'
                    }
                };

                if (body !== null) {
                    options.headers['Content-Type'] = 'application/json';
                    options.body = JSON.stringify(body);
                }

                const res = await fetch(url, options);
                const raw = await res.text();

                let parsed = raw;
                try {
                    parsed = JSON.parse(raw);
                } catch (error) {}

                response.textContent = typeof parsed === 'string' ? parsed : pretty(parsed);
                syncQrOrderId(response.textContent);

                status.textContent = res.status + ' ' + res.statusText;
                status.className = 'status-line ' + (res.ok ? 'status-ok' : 'status-error');
            } catch (error) {
                response.textContent = error.message;
                status.textContent = 'Request failed';
                status.className = 'status-line status-error';
            } finally {
                sendButton.disabled = false;
            }
        });

        host.replaceWith(node);
        refreshFields();
        node.classList.add('device-dependent');
    }

    function updateAllCards() {
        document.querySelectorAll('.device-dependent').forEach(node => {
            const card = cards.find(item => item.id === node.dataset.cardId);
            if (!card) return;

            card.fields.forEach(field => {
                const input = node.querySelector('[data-field="' + field.key + '"]');
                if (!input || field.editable) return;
                input.value = valueFromSource(field.source);
            });

            if (card.id === 'qr-request' || card.id === 'payment-status-update') {
                resetBody(card, node);
            }

            const urlPreview = node.querySelector('.request-preview');
            if (urlPreview) {
                try {
                    let text = card.method + ' ' + buildUrl(card, node);
                    const body = buildBody(card, node);
                    if (body) text += '\\n\\n' + pretty(body);
                    urlPreview.textContent = text;
                } catch (error) {
                    urlPreview.textContent = error.message;
                }
            }
        });
    }

    // Smooth navigation for sidebar anchors after dynamic cards are mounted.
    document.querySelectorAll('.sidebar a[href^="#"]').forEach(link => {
        link.addEventListener('click', event => {
            const targetId = link.getAttribute('href').slice(1);
            const target = document.getElementById(targetId);
            if (!target) return;

            event.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            history.replaceState(null, '', '#' + targetId);
        });
    });

    cards.forEach(mountCard);
    fillDeviceSelect();
    updateDeviceConsole();
    updateAllCards();

    globals.deviceSelect.addEventListener('change', () => {
        state.selectedDeviceId = globals.deviceSelect.value;
        localStorage.setItem('qrbox_selected_device_id', state.selectedDeviceId);
        updateDeviceConsole();
        updateAllCards();
    });

    document.getElementById('applyBase').addEventListener('click', () => {
        state.baseUrl = globals.baseUrl.value.trim();
        localStorage.setItem('qrbox_api_base_url', state.baseUrl);
        updateAllCards();
    });

    document.getElementById('saveToken').addEventListener('click', () => {
        state.apiToken = selectedToken();
        localStorage.setItem('qrbox_api_token', state.apiToken);
        updateAllCards();
        alert('API token disimpan di browser lokal.');
    });

    document.getElementById('clearToken').addEventListener('click', () => {
        state.apiToken = '';
        globals.apiToken.value = '';
        localStorage.removeItem('qrbox_api_token');
        updateAllCards();
    });

    document.getElementById('toggleToken').addEventListener('click', event => {
        const input = globals.apiToken;
        input.type = input.type === 'password' ? 'text' : 'password';
        event.currentTarget.textContent = input.type === 'password' ? 'Show' : 'Hide';
    });

    globals.apiToken.addEventListener('input', () => {
        state.apiToken = globals.apiToken.value;
        updateAllCards();
    });

    document.getElementById('copyDeviceConfig').addEventListener('click', async () => {
        const device = selectedDevice();
        if (!device) return alert('Pilih device terlebih dahulu.');

        const config = {
            device_id: device.id,
            device_code: device.code,
            device_name: device.name,
            service_type_id: device.service_type_id,
            service_type: device.service_type,
            outlet_id: device.outlet_id,
            outlet_name: device.outlet_name,
            api_token: selectedToken()
        };

        try {
            await navigator.clipboard.writeText(pretty(config));
            alert('Device config berhasil dicopy.');
        } catch (error) {
            alert('Gagal copy device config.');
        }
    });

    async function quickRequest(url, options, label) {
        try {
            const response = await fetch(url, options);
            const raw = await response.text();

            let parsed = raw;
            try { parsed = JSON.parse(raw); } catch (error) {}

            document.getElementById('deviceConfigPreview').textContent =
                label + '\\n\\n' + (typeof parsed === 'string' ? parsed : pretty(parsed));

            return parsed;
        } catch (error) {
            document.getElementById('deviceConfigPreview').textContent = label + '\\n\\n' + error.message;
        }
    }

    document.getElementById('runHealthCheck').addEventListener('click', () => {
        const device = selectedDevice();
        if (!device) return alert('Pilih device terlebih dahulu.');

        const token = selectedToken();
        if (!token) return alert('Masukkan API token device terlebih dahulu.');

        const url = normalizeBaseUrl()
            + '/check-device?device_code=' + encodeURIComponent(device.code)
            + '&api_token=' + encodeURIComponent(token);

        quickRequest(url, { method: 'GET', headers: { Accept: 'application/json' } }, 'HEALTH CHECK ' + device.code);
    });

    document.getElementById('runDeviceMenu').addEventListener('click', () => {
        const device = selectedDevice();
        if (!device) return alert('Pilih device terlebih dahulu.');

        const url = normalizeBaseUrl() + '/device-menu/' + encodeURIComponent(device.code);

        quickRequest(url, { method: 'GET', headers: { Accept: 'application/json' } }, 'DEVICE MENU ' + device.code);
    });

    document.getElementById('refreshDevice').addEventListener('click', () => {
        window.location.reload();
    });
</script>
</body>
</html>
