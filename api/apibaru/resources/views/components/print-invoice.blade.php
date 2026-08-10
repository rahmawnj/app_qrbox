@props(['transaction'])

@php
    // Ambil konfigurasi struk dari owner terkait transaksi
    $config = $transaction->owner->receipt_config ?? [];

    // Default konfigurasi jika tidak ada di database atau field yang hilang
    $defaultConfig = [
        'show_brand_name' => true,
        'show_outlet_address' => true,
        'show_outlet_phone' => true,
        'show_nota_id' => true,
        'show_customer_info' => true,
        'show_payment_method' => true,
        'show_cashier_name' => true,
        'show_amount_paid' => true,
        'show_datetime' => true,
        'show_notes' => true,
        'show_qr_code' => false,
        'show_addons' => true,
        'show_service_type' => true,
        'header_style' => 'centered',
        'font_size' => '12px',
        'logo_size' => 'normal',
        'thank_you_message' => '-- Terima Kasih --',
        'instruction_message' => 'Nota ini wajib dibawa sebagai bukti transaksi.',
    ];

    // Gabungkan konfigurasi dari DB dengan default, untuk memastikan semua key ada
    $config = array_merge($defaultConfig, $config);

    // Pastikan nilai boolean sesuai
    foreach (array_keys($defaultConfig) as $key) {
        if (in_array($key, ['header_style', 'font_size', 'logo_size', 'thank_you_message', 'instruction_message'])) {
            continue;
        }
        $config[$key] = (bool) ($config[$key] ?? false);
    }

    // --- Data Transaction Logic ---
    $customerName = null;
    $customerPhone = null;
    $cashierName = null;
    $serviceDetails = null;
    $servicePrice = 0;
    $pickupMethod = null;
    $addonsData = [];
    $notes = null;

    if ($transaction->channel_type == 'self_service') {
        if ($transaction->member) {
            $customerName = $transaction->member->user->name ?? 'Member ID: ' . ($transaction->member->user->id ?? 'N/A');
            $customerPhone = $transaction->member->user->phone_number ?? 'N/A';
        } else {
            $customerName = 'Non-Member';
            $customerPhone = 'N/A';
        }
        $serviceDetails = 'Self-Service Laundry';
        $servicePrice = $transaction->amount;
    } elseif ($transaction->channel_type == 'drop_off') {
        $customerName = $transaction->dropOffTransaction->customer_name ?? 'Non-Member';
        $customerPhone = $transaction->dropOffTransaction->customer_phone_number ?? 'N/A';
        $cashierName = $transaction->dropOffTransaction->cashier_name ?? 'N/A';
        $serviceDetails = $transaction->dropOffTransaction->service->name ?? 'N/A';
        $servicePrice = $transaction->dropOffTransaction->service_price ?? 0;
        $notes = $transaction->dropOffTransaction->notes ?? null;

        $pickupMethod = $transaction->dropOffTransaction->pickup_method ?? 'N/A';
        if ($pickupMethod == 'pickup') {
            $pickupMethod = 'Ambil Sendiri';
        } elseif ($pickupMethod == 'delivery') {
            $pickupMethod = 'Diantar';
        }

        $addons = $transaction->dropOffTransaction->addons;
        if (is_string($addons)) {
            $addons = json_decode($addons, true);
        }
        if (is_array($addons)) {
            $addonsData = $addons;
        }
    }

    // Mengatur Metode Pembayaran berdasarkan Channel Type dan status Member
    $paymentMethod = 'N/A';
    if ($transaction->channel_type == 'self_service') {
        if ($transaction->member) {
            $paymentMethod = 'Saldo Member';
        } else {
            $paymentMethod = 'QRIS';
        }
    } elseif ($transaction->channel_type == 'drop_off') {
        if ($transaction->member) {
            $paymentMethod = 'Saldo Member';
        } else {
            $paymentType = $transaction->dropOffTransaction->payment_type ?? 'N/A';
            if ($paymentType == 'cash') {
                $paymentMethod = 'Tunai';
            } elseif ($paymentType == 'non_cash') {
                $paymentMethod = 'Non-Tunai';
            } else {
                $paymentMethod = 'N/A';
            }
        }
    }

    // Menghitung total jumlah pembayaran dari relasi payments
    $amountPaid = $transaction->payments->sum('amount');

    // Menghitung sisa saldo yang perlu dibayar
    $balance = $transaction->amount - $amountPaid;

    // Mengambil timezone dari outlet dan mengonversi waktu transaksi
    $outletTimezone = $transaction->outlet->timezone ?? 'Asia/Jakarta';
    $transactionTime = \Carbon\Carbon::parse($transaction->created_at)->timezone($outletTimezone);

    // NEW: Mengambil waktu dan timezone pembayaran
    $paymentTime = null;
    $paymentTimezone = null;
    if ($transaction->payments->first() && $transaction->payments->first()->payment_time) {
        $payment = $transaction->payments->first();
        $paymentTime = \Carbon\Carbon::parse($payment->payment_time)->timezone($outletTimezone); // Menggunakan timezone outlet untuk konsistensi
    }

    // --- Helper untuk menentukan visibilitas garis putus-putus ---
    $headerContentRendered = $config['show_brand_name'] && ($transaction->owner->brand_name || $transaction->owner->brand_logo);
    $transactionInfoRendered = ($config['show_nota_id'] && ($transaction->order_id ?? false)) || ($config['show_customer_info'] && $customerName) || ($transaction->channel_type == 'self_service' && !$transaction->member && ($transaction->selfServiceTransaction->device_code ?? false));
    $paymentInfoRendered = $config['show_payment_method'] || ($config['show_cashier_name'] && $cashierName) || $pickupMethod;
    $hasAddonsContent = $config['show_addons'] && !empty($addonsData);
    $hasNotesContent = $config['show_notes'] && ($notes ?? false);
    $serviceDetailsActive = $config['show_service_type'] || $hasAddonsContent || $hasNotesContent;
    $totalsActive = true;
    $dateTimeRendered = $config['show_datetime'];
    $qrCodeRendered = $config['show_qr_code'];
    $footerMessageRendered = !empty($config['thank_you_message']) || !empty($config['instruction_message']);

    $showHeaderLine = $headerContentRendered && ($transactionInfoRendered || $paymentInfoRendered || $serviceDetailsActive || $totalsActive || $dateTimeRendered || $qrCodeRendered || $footerMessageRendered);
    $showPaymentLine = ($transactionInfoRendered || $paymentInfoRendered) && ($serviceDetailsActive || $totalsActive || $dateTimeRendered || $qrCodeRendered || $footerMessageRendered);
    $showServiceLine = $serviceDetailsActive && ($totalsActive || $dateTimeRendered || $qrCodeRendered || $footerMessageRendered);
    $showTotalSummaryLine = $totalsActive && ($dateTimeRendered || $qrCodeRendered || $footerMessageRendered);
    $showDatetimeLine = $dateTimeRendered && ($qrCodeRendered || $footerMessageRendered);
    $showPaymentTimeLine = $paymentTime && ($dateTimeRendered || $qrCodeRendered || $footerMessageRendered);


    // Tentukan ukuran piksel logo berdasarkan pilihan logo_size
    $logoPxSize = '50px';
    switch ($config['logo_size']) {
        case 'small': $logoPxSize = '25px'; break;
        case 'normal': $logoPxSize = '50px'; break;
        case 'large': $logoPxSize = '75px'; break;
    }
    $brandLogoSrc = $transaction->owner->brand_logo
        ? asset($transaction->owner->brand_logo)
        : 'https://placehold.co/50x50/cccccc/000000?text=LOGO';
@endphp

<a href="#modal-print{{ $transaction->id }}" class="btn btn-primary" data-bs-toggle="modal">
    <i class="fa fa-print"></i>
</a>
<div class="modal fade" id="modal-print{{ $transaction->id }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Print </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body">
                <div id="invoice-content-{{ $transaction->id }}" class="display: none;">

                    {{-- Header Section --}}
                    <div style="margin: 20px 0; {{ $config['header_style'] == 'centered' ? 'text-align: center;' : 'text-align: left;' }}">
                        @if ($config['show_brand_name'])
                            @if ($config['header_style'] == 'centered')
                                @if ($transaction->owner->brand_logo)
                                    <img src="{{ $brandLogoSrc }}" alt="Brand Logo"
                                        style="width: {{ $logoPxSize }}; height: {{ $logoPxSize }}; object-fit: contain; margin: 0 auto 5px auto; display: block;">
                                @endif
                                <h4 style="margin: 0;">{{ strtoupper($transaction->owner->brand_name ?? 'N/A') }}</h4>
                            @else
                                {{-- left_aligned --}}
                                <div style="display: flex; align-items: flex-start; margin-bottom: 5px; justify-content: flex-start;">
                                    @if ($transaction->owner->brand_logo)
                                        <img src="{{ $brandLogoSrc }}" alt="Brand Logo"
                                            style="height: {{ $logoPxSize }}; width: auto; object-fit: contain; margin-right: 5px;">
                                    @endif
                                    <div style="display: flex; flex-direction: column; justify-content: center;">
                                        <h4 style="margin: 0;">{{ strtoupper($transaction->owner->brand_name ?? 'N/A') }}</h4>
                                        @if ($config['show_outlet_address'] && ($transaction->outlet->address ?? false))
                                            <small style="font-size: inherit; display: block; text-align: left;">{{ $transaction->outlet->address }}</small>
                                        @endif
                                        @if ($config['show_outlet_phone'] && ($transaction->outlet->phone_number ?? false))
                                            <small style="font-size: inherit; display: block; text-align: left;">{{ $transaction->outlet->phone_number }}</small>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endif
                        @if ($config['show_brand_name'] && $config['header_style'] == 'centered')
                            @if ($config['show_outlet_address'] && ($transaction->outlet->address ?? false))
                                <small style="font-size: inherit; display: block;">{{ $transaction->outlet->address }}</small>
                            @endif
                            @if ($config['show_outlet_phone'] && ($transaction->outlet->phone_number ?? false))
                                <small style="font-size: inherit; display: block;">{{ $transaction->outlet->phone_number }}</small>
                            @endif
                        @endif
                    </div>

                    {{-- Line separator for header --}}
                    @if ($showHeaderLine)
                        <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
                    @endif

                    {{-- NEW: Wrapper for Body Content (Font Size Applied Here) --}}
                    <div style="font-size: {{ $config['font_size'] }};">
                        {{-- Transaction Details --}}
                        @if ($config['show_nota_id'])
                            <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                                <span style="font-weight: bold; flex-shrink: 0; margin-right: 10px;">Nota:</span>
                                <span style="text-align: right; flex-grow: 1;">{{ $transaction->order_id ?? 'N/A' }}</span>
                            </div>
                        @endif

                        {{-- Menampilkan Tipe Layanan (Channel Type) --}}
                        <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                            <span style="font-weight: bold; flex-shrink: 0; margin-right: 10px;">Tipe Layanan:</span>
                            <span style="text-align: right; flex-grow: 1;">
                                {{ strtoupper(str_replace('_', ' ', $transaction->channel_type ?? 'N/A')) }}
                            </span>
                        </div>

                        {{-- Customer Info or Device Code --}}
                        @if ($config['show_customer_info'])
                            @if ($transaction->channel_type == 'self_service' && !$transaction->member)
                                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                                    <span style="font-weight: bold; flex-shrink: 0; margin-right: 10px;">Device Code:</span>
                                    <span style="text-align: right; flex-grow: 1;">{{ $transaction->selfServiceTransaction->device_code ?? 'N/A' }}</span>
                                </div>
                            @else
                                @if ($customerName)
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                                        <span style="font-weight: bold; flex-shrink: 0; margin-right: 10px;">Customer:</span>
                                        <span style="text-align: right; flex-grow: 1;">{{ $customerName }}</span>
                                    </div>
                                @endif
                                @if ($customerPhone)
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                                        <span style="font-weight: bold; flex-shrink: 0; margin-right: 10px;">Telepon:</span>
                                        <span style="text-align: right; flex-grow: 1;">{{ $customerPhone }}</span>
                                    </div>
                                @endif
                            @endif
                        @endif

                        {{-- Payment Method and Cashier Name --}}
                        @if ($config['show_payment_method'])
                            <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                                <span style="font-weight: bold; flex-shrink: 0; margin-right: 10px;">Metode Pembayaran:</span>
                                <span style="text-align: right; flex-grow: 1;">
                                    {{ $paymentMethod }}
                                </span>
                            </div>
                        @endif

                        @if ($config['show_cashier_name'] && $transaction->channel_type == 'drop_off')
                            <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                                <span style="font-weight: bold; flex-shrink: 0; margin-right: 10px;">Kasir:</span>
                                <span style="text-align: right; flex-grow: 1;">{{ $cashierName }}</span>
                            </div>
                        @endif

                        {{-- Metode Pengambilan (khusus drop_off) --}}
                        @if ($transaction->channel_type == 'drop_off' && $pickupMethod)
                            <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                                <span style="font-weight: bold; flex-shrink: 0; margin-right: 10px;">Metode Pengambilan:</span>
                                <span style="text-align: right; flex-grow: 1;">{{ $pickupMethod }}</span>
                            </div>
                        @endif

                        {{-- Line separator after payment info --}}
                        @if ($showServiceLine)
                            <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
                        @endif

                        {{-- Service and Addons Details --}}
                        @if ($config['show_service_type'])
                            <div style="display: flex; justify-content: flex-start; margin-bottom: 5px;">
                                <span style="font-weight: bold;">Nama Layanan:</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 3px; margin-left: 10px;">
                                <span>- {{ $serviceDetails }}</span>
                                <span style="text-align: right; flex-grow: 1;">Rp{{ number_format($servicePrice ?? 0, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        {{-- Addons (Only if drop_off transaction and addons exist and configured to show) --}}
                        @if ($config['show_addons'] && $transaction->channel_type == 'drop_off' && !empty($addonsData))
                            <div style="display: flex; justify-content: flex-start; margin-bottom: 3px; margin-top: 5px;">
                                <span style="font-weight: bold;">Tambahan:</span>
                            </div>
                            <ul style="margin: 0; padding: 0; list-style-type: none;">
                                @foreach ($addonsData as $addon)
                                    <li style="display: flex; justify-content: space-between; margin-bottom: 2px; margin-left: 10px;">
                                        <span>- {{ $addon['name'] ?? 'N/A' }}</span>
                                        <span>Rp{{ number_format($addon['price'] ?? 0, 0, ',', '.') }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        {{-- Notes (Only if drop_off transaction and notes exist and configured to show) --}}
                        @if ($hasNotesContent)
                            <div style="display: flex; flex-direction: column; margin-top: 10px;">
                                <span style="font-weight: bold; margin-bottom: 3px;">Catatan:</span>
                                <span style="text-align: justify;">{{ $notes }}</span>
                            </div>
                        @endif

                        {{-- Line separator before Total --}}
                        @if ($showTotalSummaryLine)
                            <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
                        @endif

                        {{-- Total --}}
                        <div style="display: flex; justify-content: space-between; margin-bottom: 3px; font-weight: bold;">
                            <span>TOTAL:</span>
                            <span>Rp{{ number_format($transaction->amount, 0, ',', '.') }}</span>
                        </div>

                        {{-- Status Pembayaran --}}
                        @if($balance <= 0)
                            <div style="display: flex; justify-content: flex-end; margin-bottom: 3px; font-weight: bold; color: green; font-size: 1.1em; text-align: right;">
                                <span>LUNAS</span>
                            </div>
                        @endif

                        {{-- Line separator before datetime --}}
                        @if ($showDatetimeLine)
                            <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
                        @endif

                        {{-- Date & Time --}}
                        @if ($config['show_datetime'])
                            <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                                <span style="font-weight: bold; flex-shrink: 0; margin-right: 10px;">Tanggal:</span>
                                <span style="text-align: right; flex-grow: 1;">
                                    {{ $transactionTime->format('d-m-Y H:i') }}
                                    {{ $transactionTime->format('T') }}
                                </span>
                            </div>
                            @if ($paymentTime)
                                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                                    <span style="font-weight: bold; flex-shrink: 0; margin-right: 10px;">Waktu Pembayaran:</span>
                                    <span style="text-align: right; flex-grow: 1;">
                                        {{ $paymentTime->format('d-m-Y H:i') }}
                                        {{ $paymentTime->format('T') }}
                                    </span>
                                </div>
                            @endif
                        @endif

                        {{-- Line separator before QR Code --}}
                        @if ($qrCodeRendered && ($dateTimeRendered || $serviceDetailsActive || $totalsActive))
                            <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
                        @endif

                        {{-- QR Code Section --}}
                        @if ($config['show_qr_code'])
                            <div style="text-align: center; margin: 25px auto;">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode(route('home.transaction', ['order_id' => $transaction->order_id])) }}"
                                    alt="QR Code" style="width: 130px; height: 130px; display: block; margin: 0 auto;">
                                <p style="font-size: 0.8em; margin-top: 5px; color: #555;">Scan untuk detail transaksi</p>
                            </div>
                        @endif
                    </div>
                    {{-- END NEW: Wrapper for Body Content --}}

                    {{-- Line separator before footer messages --}}
                    @if ($footerMessageRendered && ($qrCodeRendered || $dateTimeRendered || $serviceDetailsActive || $totalsActive))
                        <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
                    @endif

                    {{-- Footer Messages --}}
                    <div style="text-align: center; font-size: inherit;">
                        @if (!empty($config['thank_you_message']))
                            <p style="margin: 10px 0;">{{ $config['thank_you_message'] }}</p>
                        @endif
                        @if (!empty($config['instruction_message']))
                            <p style="margin: 1px 0;">{{ $config['instruction_message'] }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="javascript:;" class="btn btn-white" data-bs-dismiss="modal">Close</a>
                <button class="btn btn-sm btn-secondary" onclick="printInvoice('{{ $transaction->id }}')"
                    title="Cetak Nota">
                    Print
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function printInvoice(transactionId) {
            const printContents = document.getElementById('invoice-content-' + transactionId).innerHTML;
            const printWindow = window.open('', '_blank');

            printWindow.document.open();
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Nota Transaksi</title>
                    <style>
                        body {
                            font-family: 'Courier New', Courier, monospace;
                            margin: 0;
                            padding: 0;
                            color: #000;
                        }
                        #invoice-content-print {
                            width: 80mm;
                            padding: 0;
                            box-sizing: border-box;
                            margin: 0;
                        }

                        #invoice-content-print h4,
                        #invoice-content-print p,
                        #invoice-content-print ul {
                            margin: 0;
                            padding: 0;
                        }

                        #invoice-content-print ul li {
                            margin-left: 0;
                            list-style-type: none;
                        }

                        #invoice-content-print .dashed-line {
                            border-top: 1px dashed #000;
                            margin: 10px 0;
                        }

                        .receipt-item-line {
                            display: flex;
                            justify-content: space-between;
                            margin-bottom: 3px;
                            align-items: flex-start;
                        }
                        .receipt-item-line .label {
                            font-weight: bold;
                            flex-shrink: 0;
                            margin-right: 10px;
                        }
                        .receipt-item-line .value {
                            text-align: right;
                            flex-grow: 1;
                        }

                        @media print {
                            body {
                                margin: 0;
                                padding: 0;
                            }
                            #invoice-content-print {
                                width: 80mm;
                                padding: 0;
                                margin: 0;
                            }
                        }
                    </style>
                </head>
                <body>
                    <div id="invoice-content-print">
                        ${printContents}
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();

            printWindow.onload = function() {
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            };

            setTimeout(() => {
                if (!printWindow.closed) {
                    printWindow.focus();
                    printWindow.print();
                    printWindow.close();
                }
            }, 1500);
        }
    </script>
@endpush
