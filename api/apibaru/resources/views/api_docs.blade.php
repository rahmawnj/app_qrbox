<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QRBox API Docs & Playground</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #18212f;
            --muted: #64748b;
            --line: #d8dee8;
            --paper: #f6f8fb;
            --panel: #ffffff;
            --accent: #0f766e;
            --accent-soft: #d9f3ef;
            --blue: #2563eb;
            --orange: #ea580c;
            --red: #dc2626;
            --code: #101827;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            background: var(--paper);
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            line-height: 1.6;
        }

        a { color: inherit; text-decoration: none; }
        code, pre, input, textarea, select { font-family: "Cascadia Code", "Fira Code", Consolas, monospace; }
        .shell { display: grid; grid-template-columns: 280px minmax(0, 1fr); min-height: 100vh; }
        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            padding: 28px 22px;
            background: #101827;
            color: #eef4ff;
            overflow-y: auto;
        }
        .brand { display: flex; align-items: center; gap: 12px; margin-bottom: 28px; }
        .brand-mark {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 8px;
            background: var(--accent);
            font-weight: 900;
        }
        .brand-title { font-size: 18px; font-weight: 800; letter-spacing: .2px; }
        .brand-subtitle { color: #94a3b8; font-size: 11px; text-transform: uppercase; letter-spacing: .12em; }
        .nav-label { color: #718096; font-size: 11px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; margin: 24px 10px 8px; }
        .nav-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin: 5px 0;
            padding: 10px 12px;
            border-radius: 8px;
            color: #cbd5e1;
            font-size: 14px;
        }
        .nav-link:hover { background: #1f2a3a; color: #ffffff; }
        .pill {
            display: inline-flex;
            align-items: center;
            min-height: 24px;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .04em;
            color: #fff;
        }
        .get { background: var(--accent); }
        .post { background: var(--blue); }
        .main { padding: 34px; }
        .hero {
            max-width: 1180px;
            margin: 0 auto 18px;
            padding: 34px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel);
        }
        h1, h2, h3 { line-height: 1.2; margin: 0; }
        h1 { font-size: clamp(32px, 5vw, 56px); letter-spacing: 0; }
        h2 { font-size: 26px; margin-bottom: 14px; }
        h3 { font-size: 18px; }
        p { margin: 0; color: var(--muted); }
        .hero p { max-width: 760px; margin-top: 14px; font-size: 17px; }
        .config {
            max-width: 1180px;
            margin: 0 auto 22px;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            padding: 16px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #eaf1f7;
        }
        label { display: block; margin-bottom: 6px; color: #475569; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; }
        input, textarea, select {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #fff;
            color: var(--ink);
            padding: 10px 12px;
            font-size: 13px;
            outline: none;
        }
        textarea { min-height: 170px; resize: vertical; line-height: 1.5; }
        input:focus, textarea:focus, select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(15, 118, 110, .14); }
        section {
            max-width: 1180px;
            margin: 0 auto 22px;
            scroll-margin-top: 20px;
        }
        .section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin: 34px 0 14px;
        }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .card {
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel);
            overflow: hidden;
        }
        .card-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 18px;
            border-bottom: 1px solid var(--line);
        }
        .endpoint { margin-top: 7px; color: #334155; font-size: 13px; word-break: break-all; }
        .card-body { padding: 18px; }
        .meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin: 14px 0;
        }
        .full { grid-column: 1 / -1; }
        .hint {
            padding: 12px;
            border: 1px solid #c9e6e1;
            border-radius: 8px;
            background: var(--accent-soft);
            color: #115e59;
            font-size: 13px;
        }
        .actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-top: 14px; }
        button {
            border: 0;
            border-radius: 8px;
            padding: 10px 14px;
            background: var(--accent);
            color: #fff;
            font-weight: 800;
            cursor: pointer;
        }
        button.secondary { background: #334155; }
        button:hover { filter: brightness(.96); }
        pre {
            margin: 12px 0 0;
            padding: 14px;
            border-radius: 8px;
            background: var(--code);
            color: #dbeafe;
            overflow: auto;
            font-size: 12px;
            line-height: 1.55;
        }
        .response {
            min-height: 142px;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .status-line { color: #64748b; font-size: 13px; }
        .table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fff;
        }
        .table th, .table td { padding: 13px 14px; border-bottom: 1px solid var(--line); text-align: left; font-size: 14px; }
        .table th { background: #edf2f7; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: .08em; }
        .table tr:last-child td { border-bottom: 0; }
        .copy { background: #475569; padding: 8px 10px; font-size: 12px; }
        .danger { color: var(--red); }
        @media (max-width: 980px) {
            .shell { grid-template-columns: 1fr; }
            .sidebar { position: static; height: auto; }
            .main { padding: 18px; }
            .config, .grid { grid-template-columns: 1fr; }
            .meta { grid-template-columns: 1fr; }
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
                <div class="brand-subtitle">Docs & Playground</div>
            </div>
        </div>

        <div class="nav-label">Dokumentasi</div>
        <a class="nav-link" href="#overview">Overview</a>
        <a class="nav-link" href="#service-types">Service Types</a>
        <a class="nav-link" href="#device">Device</a>
        <a class="nav-link" href="#payment">Payment</a>
        <a class="nav-link" href="#callback">Callback</a>

        <div class="nav-label">Playground</div>
        <a class="nav-link" href="#device-menu"><span>Device Menu</span><span class="pill get">GET</span></a>
        <a class="nav-link" href="#update-status"><span>Update Status</span><span class="pill post">POST</span></a>
        <a class="nav-link" href="#check-device"><span>Check Device</span><span class="pill get">GET</span></a>
        <a class="nav-link" href="#qr-request"><span>QR Request</span><span class="pill post">POST</span></a>
        <a class="nav-link" href="#payment-check"><span>Payment Check</span><span class="pill get">GET</span></a>
        <a class="nav-link" href="#payment-check-2"><span>Payment Check 2</span><span class="pill get">GET</span></a>
        <a class="nav-link" href="#payment-status-update"><span>Status Update</span><span class="pill post">POST</span></a>
        <a class="nav-link" href="#device-price"><span>Device Price</span><span class="pill get">GET</span></a>
    </aside>

    <main class="main">
        <section id="overview" class="hero">
            <h1>QRBox API Documentation</h1>
            <p>Halaman publik satu halaman untuk membaca dokumentasi dan langsung mengetes endpoint API QRBox. Isi nilai global di bawah, termasuk <code>api_token</code>, lalu setiap playground akan otomatis memakai contoh URL dan payload yang sesuai.</p>
        </section>

        <div class="config" aria-label="Global playground configuration">
            <div>
                <label for="baseUrl">Base API URL</label>
                <input id="baseUrl" value="{{ url('/api') }}">
            </div>
            <div>
                <label for="apiToken">API Token</label>
                <input id="apiToken" value="ISI_DEVICE_TOKEN">
            </div>
            <div>
                <label for="deviceCode">Device Code</label>
                <input id="deviceCode" value="DEV-WHNTZR">
            </div>
            <div>
                <label for="orderId">Order ID</label>
                <input id="orderId" value="TRX-OUTLET-ORDER-ID">
            </div>
        </div>

        <section id="service-types">
            <div class="section-head">
                <h2>Service Types</h2>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Value payload</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Laundry</td>
                        <td><code>washer</code>, <code>dryer_a</code>, <code>dryer_b</code></td>
                        <td>Umumnya dipakai untuk QR request dan polling mesin.</td>
                    </tr>
                    <tr>
                        <td>Turnstile</td>
                        <td><code>turnstile</code></td>
                        <td>Untuk akses gate atau perangkat sejenis.</td>
                    </tr>
                    <tr>
                        <td>Dispenser</td>
                        <td><code>dispenser_a</code>, <code>dispenser_b</code>, <code>dispenser_c</code></td>
                        <td>Sesuaikan dengan option device.</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section id="device">
            <div class="section-head">
                <h2>Device API</h2>
            </div>
            <div class="grid">
                <div id="device-menu"></div>
                <div id="update-status"></div>
                <div id="check-device"></div>
                <div id="device-price"></div>
            </div>
        </section>

        <section id="payment">
            <div class="section-head">
                <h2>Payment API</h2>
            </div>
            <div class="grid">
                <div id="qr-request"></div>
                <div id="payment-check"></div>
                <div id="payment-check-2"></div>
            </div>
        </section>

        <section id="callback">
            <div class="section-head">
                <h2>Callback API</h2>
            </div>
            <div class="grid">
                <div id="payment-status-update"></div>
            </div>
            <p class="hint">Endpoint callback biasanya dipanggil oleh Midtrans. Playground ini membantu simulasi payload status transaksi untuk testing lokal.</p>
        </section>
    </main>
</div>

@verbatim
<template id="playground-card-template">
    <article class="card">
        <div class="card-head">
            <div>
                <span class="pill method-pill"></span>
                <h3 class="card-title"></h3>
                <div class="endpoint"></div>
            </div>
            <button class="copy" type="button">Copy URL</button>
        </div>
        <div class="card-body">
            <p class="description"></p>
            <div class="meta"></div>
            <div class="body-wrap">
                <label>Request Body</label>
                <textarea class="request-body"></textarea>
            </div>
            <div class="actions">
                <button class="send" type="button">Send Request</button>
                <button class="secondary fill" type="button">Reset Example</button>
                <span class="status-line">Ready</span>
            </div>
            <label style="margin-top:16px;">Response</label>
            <pre class="response">{}</pre>
        </div>
    </article>
</template>
@endverbatim

<script>
    const cards = [
        {
            id: 'device-menu',
            method: 'GET',
            title: 'Get Device Menu',
            endpoint: '/device-menu/{device_code}',
            description: 'Mengambil nama device, service type, dan empat opsi menu dari device_code. api_token ikut dikirim supaya format testing konsisten.',
            fields: [
                { key: 'device_code', label: 'Device Code', global: 'deviceCode' },
                { key: 'api_token', label: 'API Token', global: 'apiToken', queryOnly: true }
            ],
            body: null,
            sampleResponse: { status: 'success', service_type: 'Laundry', device_name: 'Main Machine Laundry', device_code: 'DEV-WHNTZR', menus: [{ name: 'Laundry Menu 1', type: 'washer', price: 13000, active: true, duration: 45 }] }
        },
        {
            id: 'update-status',
            method: 'POST',
            title: 'Update Device Status',
            endpoint: '/devices/{device}/update-status',
            description: 'Bypass atau matikan status perangkat menggunakan ID device Laravel. api_token ikut tersedia di payload testing.',
            fields: [{ key: 'device', label: 'Device ID', value: '1' }],
            body: { device_status: 'washer', bypass_note: 'Testing bypass dari API playground', api_token: 'ISI_DEVICE_TOKEN' },
            sampleResponse: { status: 'success', message: 'Device DEV-WHNTZR berhasil diperbarui menjadi "washer"' }
        },
        {
            id: 'check-device',
            method: 'GET',
            title: 'Check Device Status',
            endpoint: '/check-device?device_code={device_code}',
            description: 'Dipakai hardware untuk polling status aktivasi setiap beberapa detik. api_token ikut dikirim sebagai query.',
            fields: [
                { key: 'device_code', label: 'Device Code', global: 'deviceCode' },
                { key: 'api_token', label: 'API Token', global: 'apiToken', queryOnly: true }
            ],
            body: null,
            sampleResponse: { status: 'success', status_device: 'off', source: null, activation_date: null, message: 'Status diterima' }
        },
        {
            id: 'qr-request',
            method: 'POST',
            title: 'QR Request',
            endpoint: '/qr-request',
            description: 'Membuat transaksi QRIS Midtrans berdasarkan device_code, service type, dan device token.',
            fields: [
                { key: 'device_code', label: 'Device Code', global: 'deviceCode', bodyOnly: true },
                { key: 'api_token', label: 'API Token', global: 'apiToken', bodyOnly: true }
            ],
            body: { type: 'washer', device_code: 'DEV-WHNTZR', api_token: 'ISI_DEVICE_TOKEN' },
            sampleResponse: { status: 'success', message: { order_id: 'TRX-OUTLET-EXAMPLE', payment_status: 'pending', qr_image: 'https://domain.test/storage/app/public/qrcodes/TRX-OUTLET-EXAMPLE.jpg', original_price: 13000 } }
        },
        {
            id: 'payment-check',
            method: 'GET',
            title: 'Payment Check',
            endpoint: '/payment-check?order_id={order_id}&api_token={api_token}',
            description: 'Cek status pembayaran menggunakan order_id. Token device ikut dikirim sebagai query.',
            fields: [
                { key: 'order_id', label: 'Order ID', global: 'orderId' },
                { key: 'api_token', label: 'API Token', global: 'apiToken' }
            ],
            body: null,
            sampleResponse: { status: 'success', message: { type: 'washer', order_id: 'TRX-OUTLET-EXAMPLE', payment_status: 'success', description: 'Pembayaran Berhasil.' } }
        },
        {
            id: 'payment-check-2',
            method: 'GET',
            title: 'Payment Check 2',
            endpoint: '/payment-check-2?device_code={device_code}&service_type={service_type}&api_token={api_token}',
            description: 'Cek transaksi sukses terbaru berdasarkan device_code dan service_type.',
            fields: [
                { key: 'device_code', label: 'Device Code', global: 'deviceCode' },
                { key: 'service_type', label: 'Service Type', value: 'washer' },
                { key: 'api_token', label: 'API Token', global: 'apiToken' }
            ],
            body: null,
            sampleResponse: { status: 'success', message: { order_id: 'TRX-OUTLET-EXAMPLE', payment_status: 'success', amount: 11700, description: 'Pembayaran Berhasil.' } }
        },
        {
            id: 'payment-status-update',
            method: 'POST',
            title: 'Payment Status Update',
            endpoint: '/payment-status-update',
            description: 'Simulasi callback Midtrans untuk mengubah status internal transaksi.',
            fields: [],
            body: { order_id: 'TRX-OUTLET-ORDER-ID', transaction_status: 'settlement', gross_amount: '13000.00', payment_type: 'qris', settlement_time: '2026-07-14 10:00:00' },
            sampleResponse: { status: 'success', message: 'Status updated to success' }
        },
        {
            id: 'device-price',
            method: 'GET',
            title: 'Device Price',
            endpoint: '/device-price/{device}/{serviceType}',
            description: 'Mengambil harga dari pivot device_service_type berdasarkan ID device dan ID service type. api_token ikut dikirim sebagai query.',
            fields: [
                { key: 'device', label: 'Device ID', value: '1' },
                { key: 'serviceType', label: 'Service Type ID', value: '1' },
                { key: 'api_token', label: 'API Token', global: 'apiToken', queryOnly: true }
            ],
            body: null,
            sampleResponse: { price: 13000 }
        }
    ];

    const globals = {
        baseUrl: document.getElementById('baseUrl'),
        apiToken: document.getElementById('apiToken'),
        deviceCode: document.getElementById('deviceCode'),
        orderId: document.getElementById('orderId')
    };

    function pretty(value) {
        return JSON.stringify(value, null, 2);
    }

    function normalizeBaseUrl() {
        return globals.baseUrl.value.replace(/\/+$/, '');
    }

    function valueFor(field, root) {
        const input = root.querySelector(`[data-field="${field.key}"]`);
        if (input) {
            return input.value;
        }
        if (field.global && globals[field.global]) {
            return globals[field.global].value;
        }
        return field.value || '';
    }

    function buildUrl(card, root) {
        let endpoint = card.endpoint;
        card.fields.filter((field) => !field.bodyOnly).forEach((field) => {
            endpoint = endpoint.replaceAll(`{${field.key}}`, encodeURIComponent(valueFor(field, root)));
        });
        endpoint = endpoint.replaceAll('{device_code}', encodeURIComponent(globals.deviceCode.value));
        endpoint = endpoint.replaceAll('{api_token}', encodeURIComponent(globals.apiToken.value));
        endpoint = endpoint.replaceAll('{order_id}', encodeURIComponent(globals.orderId.value));
        const url = new URL(normalizeBaseUrl() + endpoint);
        card.fields.filter((field) => field.queryOnly).forEach((field) => {
            if (!url.searchParams.has(field.key)) {
                url.searchParams.set(field.key, valueFor(field, root));
            }
        });
        return url.toString();
    }

    function bodyFor(card, textarea, root) {
        if (!card.body) {
            return null;
        }
        const parsed = JSON.parse(textarea.value || '{}');
        card.fields.filter((field) => field.bodyOnly).forEach((field) => {
            parsed[field.key] = valueFor(field, root);
        });
        if (Object.prototype.hasOwnProperty.call(parsed, 'api_token') && parsed.api_token === 'ISI_DEVICE_TOKEN') {
            parsed.api_token = globals.apiToken.value;
        }
        if (Object.prototype.hasOwnProperty.call(parsed, 'device_code') && parsed.device_code === 'DEV-WHNTZR') {
            parsed.device_code = globals.deviceCode.value;
        }
        if (Object.prototype.hasOwnProperty.call(parsed, 'order_id') && parsed.order_id === 'TRX-OUTLET-ORDER-ID') {
            parsed.order_id = globals.orderId.value;
        }
        return parsed;
    }

    function mountCard(card) {
        const host = document.getElementById(card.id);
        if (!host) return;

        const template = document.getElementById('playground-card-template');
        const node = template.content.firstElementChild.cloneNode(true);
        const pill = node.querySelector('.method-pill');
        const bodyWrap = node.querySelector('.body-wrap');
        const textarea = node.querySelector('.request-body');
        const response = node.querySelector('.response');
        const status = node.querySelector('.status-line');

        pill.textContent = card.method;
        pill.classList.add(card.method === 'GET' ? 'get' : 'post');
        node.querySelector('.card-title').textContent = card.title;
        node.querySelector('.endpoint').textContent = card.endpoint;
        node.querySelector('.description').textContent = card.description;
        response.textContent = pretty(card.sampleResponse);

        const meta = node.querySelector('.meta');
        card.fields.forEach((field) => {
            const wrap = document.createElement('div');
            wrap.innerHTML = `<label>${field.label}</label><input data-field="${field.key}" value="${field.global ? globals[field.global].value : field.value}">`;
            meta.appendChild(wrap);
        });

        if (card.body) {
            textarea.value = pretty(card.body);
        } else {
            bodyWrap.style.display = 'none';
        }

        node.querySelector('.copy').addEventListener('click', async () => {
            await navigator.clipboard.writeText(buildUrl(card, node));
            status.textContent = 'URL copied';
        });

        node.querySelector('.fill').addEventListener('click', () => {
            if (card.body) textarea.value = pretty(card.body);
            response.textContent = pretty(card.sampleResponse);
            status.textContent = 'Example restored';
        });

        node.querySelector('.send').addEventListener('click', async () => {
            const url = buildUrl(card, node);
            status.textContent = 'Sending...';
            response.textContent = 'Loading ' + url;

            try {
                const options = { method: card.method, headers: { Accept: 'application/json' } };
                const payload = bodyFor(card, textarea, node);
                if (payload) {
                    options.headers['Content-Type'] = 'application/json';
                    options.body = JSON.stringify(payload);
                }
                const res = await fetch(url, options);
                const text = await res.text();
                let parsed = text;
                try { parsed = JSON.parse(text); } catch (error) {}
                response.textContent = typeof parsed === 'string' ? parsed : pretty(parsed);
                status.textContent = `${res.status} ${res.statusText}`;
                status.classList.toggle('danger', !res.ok);
            } catch (error) {
                response.textContent = error.message;
                status.textContent = 'Request failed';
                status.classList.add('danger');
            }
        });

        host.replaceWith(node);
    }

    cards.forEach(mountCard);
</script>
</body>
</html>
