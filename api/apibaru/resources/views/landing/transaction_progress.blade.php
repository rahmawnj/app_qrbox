<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progres Layanan Transaksi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-blue: #007bff;
            --dark-blue: #0056b3;
            --light-gray: #e9ecef;
            --medium-gray: #adb5bd;
            --dark-gray: #495057;
            --green-success: #28a745;
            --red-danger: #dc3545;
            --line-gray: #dee2e6;
            --text-color-light: #6c757d;
            --text-color-dark: #212529;
        }

        body {
            background: linear-gradient(45deg, #f0f2f5, #e0e5ec);
            /* Soft gradient background */
            background-size: 400% 400%;
            animation: gradientBackground 15s ease infinite;
            /* Subtle animation */
            font-family: 'Inter', 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            /* Prefer Inter font */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }

        @keyframes gradientBackground {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .card {
            border-radius: 16px;
            /* More rounded corners */
            overflow: hidden;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.25);
            /* Deeper shadow */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            /* Smooth transition for hover */
            border: none;
            /* Remove default border */
        }

        .card:hover {
            transform: translateY(-5px);
            /* Lift effect on hover */
            box-shadow: 0 18px 36px rgba(0, 0, 0, 0.3);
            /* Deeper shadow on hover */
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
            /* Gradient biru */
            color: white;
            font-weight: 700;
            /* Bolder font */
            padding: 25px;
            text-align: center;
            border-bottom: none;
            font-size: 1.8rem;
            /* Larger font size */
            letter-spacing: 0.5px;
            position: relative;
            z-index: 1;
        }

        .card-body {
            padding: 30px;
            background-color: #ffffff;
            /* White background for body */
        }

        .brand-logo {
            max-width: 90px;
            /* Slightly larger logo */
            height: auto;
            margin-bottom: 20px;
            border-radius: 12px;
            /* More rounded logo */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .info-section {
            border-bottom: 1px dashed var(--line-gray);
            /* Garis putus-putus lebih halus */
            padding-bottom: 18px;
            margin-bottom: 18px;
        }

        .info-section:last-of-type {
            border-bottom: none;
            margin-bottom: 0;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            /* Increased spacing */
            font-size: 1rem;
            /* Slightly larger font */
            align-items: baseline;
        }

        .info-item strong {
            color: var(--dark-gray);
            font-weight: 600;
        }

        .info-item span {
            text-align: right;
            color: var(--text-color-light);
            /* Slightly darker text for better contrast */
            font-weight: 500;
        }

        .progress-indicator {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 40px;
            /* More space */
            margin-bottom: 40px;
            position: relative;
            padding: 0 10px;
            /* Adjusted padding */
        }

        .progress-indicator::before {
            content: '';
            position: absolute;
            top: 40px;
            left: 25px;
            /* Adjusted to align with circle center */
            right: 25px;
            /* Adjusted to align with circle center */
            height: 4px;
            background-color: var(--line-gray);
            z-index: 1;
            border-radius: 2px;
        }

        .progress-step {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 2;
            padding: 0 5px;
            /* Added padding to steps */
        }

        .progress-circle {
            width: 55px;
            /* Slightly larger circle */
            height: 55px;
            border-radius: 50%;
            background-color: var(--medium-gray);
            margin: 0 auto 12px;
            /* Adjusted margin */
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.4rem;
            /* Larger icon/number */
            font-weight: bold;
            transition: background-color 0.5s ease, transform 0.3s ease, box-shadow 0.3s ease;
            /* Smoother transitions */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            /* More prominent shadow */
            border: 3px solid rgba(255, 255, 255, 0.3);
            /* Subtle white border */
            cursor: pointer;
            /* Indicate interactivity */
        }

        .progress-circle:hover {
            transform: scale(1.05);
            /* Slight scale on hover */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        }

        .progress-step.active .progress-circle {
            background-color: var(--primary-blue);
            transform: scale(1.1);
            /* Initial scale for active state */
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.4);
            border-color: rgba(255, 255, 255, 0.8);
            animation: pulse 1.5s infinite alternate;
            /* Pulse animation for active */
        }

        /* Adjusted pulse animation for more noticeable effect */
        @keyframes pulse {
            0% {
                transform: scale(1.1);
                /* Start from this scale */
                box-shadow: 0 6px 15px rgba(0, 0, 0, 0.4);
            }

            100% {
                transform: scale(1.25);
                /* Animate to a larger scale */
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.6);
                /* More pronounced shadow */
            }
        }

        .progress-step.completed .progress-circle {
            background-color: var(--green-success);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.4);
            border-color: rgba(255, 255, 255, 0.8);
        }

        .progress-step.cancelled .progress-circle {
            background-color: var(--red-danger);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.4);
            border-color: rgba(255, 255, 255, 0.8);
        }

        .progress-text {
            font-size: 0.9em;
            /* Slightly larger text */
            color: var(--dark-gray);
            margin-top: 8px;
            /* Adjusted margin */
            line-height: 1.4;
            transition: color 0.3s ease, font-weight 0.3s ease;
            /* Smooth text transition */
        }

        .progress-step.active .progress-text,
        .progress-step.completed .progress-text,
        .progress-step.cancelled .progress-text {
            font-weight: 700;
            /* Bolder */
            color: var(--text-color-dark);
            /* Black for emphasis */
        }

        .status-alert {
            font-size: 1.1rem;
            font-weight: bold;
            padding: 18px;
            /* More padding */
            border-radius: 10px;
            /* More rounded */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            /* Deeper shadow */
            animation: fadeIn 0.5s ease-out;
            /* Fade in animation */
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-info-custom {
            background-color: #e0f7fa;
            border-color: #00bcd4;
            color: #005662;
        }

        .alert-danger-custom {
            background-color: #fce4ec;
            border-color: #e91e63;
            color: #880e4f;
        }

        .list-unstyled li {
            margin-bottom: 6px;
            /* Slightly more space */
        }

        .footer-text {
            font-size: 0.85rem;
            color: #6c757d;
            padding-top: 20px;
            /* More padding */
        }

        /* Styling for the select dropdown */
        .form-select {
            border-radius: 8px;
            padding: 8px 15px;
            font-size: 0.95rem;
            border: 1px solid var(--medium-gray);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        .form-select:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
            outline: none;
        }

        .form-select:disabled {
            background-color: #e9ecef;
            cursor: not-allowed;
            opacity: 0.7;
        }

        /* Ikon untuk progres */
        .progress-circle i {
            font-size: 1.3rem;
            /* Ukuran ikon */
        }
    </style>

</head>

<body>
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-header">
                        Progres Layanan
                    </div>
                    <div class="card-body">
                        @if (isset($error))
                            <div class="alert alert-danger text-center status-alert" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i> {{ $error }}
                            </div>
                        @elseif ($transaction)
                            <div class="text-center mb-4">
                                @if ($transaction->owner->brand_logo)
                                    <img src="{{ asset( $transaction->owner->brand_logo) }}"
                                        alt="Brand Logo" class="brand-logo">
                                @endif
                                <h4 class="card-title mb-1">
                                    {{ $transaction->owner->brand_name ?? 'Layanan Bisnis' }}
                                    @if ($transaction->outlet->outlet_name) {{-- Changed to outlet_name --}}
                                        <br><small class="text-muted">{{ $transaction->outlet->outlet_name }}</small>
                                    @endif
                                </h4>
                                {{-- Menampilkan alamat outlet di bawah nama brand --}}
                                <p class="text-muted small">{{ $transaction->outlet->address ?? 'Alamat tidak tersedia' }}
                                </p>
                            </div>

                            <div class="info-section">
                                <h5 class="mb-3">Detail Transaksi</h5>
                                <div class="info-item">
                                    <strong>Order ID:</strong>
                                    <span>{{ $transaction->order_id ?? 'N/A' }}</span>
                                </div>
                                <div class="info-item">
                                    <strong>Tanggal Transaksi:</strong>
                                    <span>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d F Y H:i') }}
                                        WIB</span>
                                </div>
                                <div class="info-item">
                                    <strong>Jenis Transaksi:</strong>
                                    <span>{{ ucfirst(str_replace('_', ' ', $transaction->channel_type)) }}</span>
                                </div>
                                {{-- Customer Name and Phone --}}
                                <div class="info-item">
                                    <strong>Nama Pelanggan:</strong>
                                    <span>{{ $transaction->customer_display_name ?? 'N/A' }}</span>
                                </div>
                                <div class="info-item">
                                    <strong>Telepon Pelanggan:</strong>
                                    <span>{{ $transaction->customer_display_phone ?? 'N/A' }}</span>
                                </div>
                                @if ($transaction->estimated_completion_display_at)
                                    <div class="info-item">
                                        <strong>Estimasi Selesai:</strong>
                                        {{-- Menggunakan text-danger untuk warna merah sesuai permintaan --}}
                                        <span
                                            class="text-danger fw-bold">{{ \Carbon\Carbon::parse($transaction->estimated_completion_display_at)->format('d F Y H:i') }}
                                            WIB</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Outlet Contact Information --}}
                            <div class="info-section">
                                <h5 class="mb-3">Informasi Kontak Outlet</h5>
                                <div class="info-item">
                                    <strong>Alamat Outlet:</strong>
                                    <span>{{ $transaction->outlet->address ?? 'N/A' }}</span>
                                </div>
                                <div class="info-item">
                                    <strong>Telepon Outlet:</strong>
                                    <span>{{ $transaction->outlet->phone_number ?? 'N/A' }}</span>
                                </div>
                            </div>

                            {{-- Detail Layanan (Drop-off) --}}
                            @if ($transaction->dropOffTransaction)
                                <div class="info-section">
                                    <h5 class="mb-3">Detail Layanan</h5>
                                    <div class="info-item">
                                        <strong>Layanan:</strong>
                                        <span>{{ $transaction->dropOffTransaction->service->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <strong>Harga Layanan:</strong>
                                        <span>Rp{{ number_format($transaction->dropOffTransaction->service_price ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    @if (!empty($transaction->addons_data))
                                        <h6 class="mt-3">Tambahan:</h6>
                                        <ul class="list-unstyled">
                                            @foreach ($transaction->addons_data as $addon)
                                                <li class="d-flex justify-content-between">
                                                    <span>- {{ $addon['name'] ?? 'N/A' }}</span>
                                                    <span>Rp{{ number_format($addon['price'] ?? 0, 0, ',', '.') }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    <div class="info-item mt-3">
                                        <strong>Total Pembayaran:</strong>
                                        <span
                                            class="text-success fw-bold">Rp{{ number_format($transaction->total_amount_before_paid ?? 0, 0, ',', '.') }}</span>
                                    </div>

                                    @if ($transaction->dropOffTransaction->notes)
                                        <p class="mt-3 mb-0"><strong>Catatan:</strong></p>
                                        <p class="text-muted fst-italic">{{ $transaction->dropOffTransaction->notes }}
                                        </p>
                                    @endif
                                </div>
                            @endif

                            {{-- Detail Member (Self-service) --}}
                            @if ($transaction->channel_type == 'self_service' && $transaction->member)
                                <div class="info-section">
                                    <h5 class="mb-3">Detail Member</h5>
                                    <div class="info-item">
                                        <strong>Member:</strong>
                                        <span>{{ $transaction->member->user->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <strong>Metode Pembayaran:</strong>
                                        <span>Saldo Member</span>
                                    </div>
                                    <div class="info-item">
                                        <strong>Jumlah Transaksi:</strong>
                                        <span>Rp{{ number_format($transaction->amount ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            @endif

                            {{-- Progress Indicator (Hanya untuk Drop-off) --}}
                            @if ($transaction->dropOffTransaction)
                                <h5 class="mt-4 mb-4 text-center">Status Progres Layanan</h5>
                                <div class="progress-indicator">
                                    {{-- Mapping ikon untuk setiap status --}}
                                    @php
                                        $icons = [
                                            'received' => 'fas fa-clipboard-list',
                                            'processing' => 'fas fa-cogs', // Menggunakan 'processing' sesuai OrderStatus
                                            'ready_for_pickup' => 'fas fa-box-open',
                                            'completed' => 'fas fa-check-circle',
                                            'cancelled' => 'fas fa-times-circle',
                                        ];
                                        $currentProgress = $transaction->dropOffTransaction->progress; // Mengambil progress dari dropOffTransaction
                                        $isCurrentReached = false; // Flag untuk menandai apakah status saat ini sudah dilewati
                                    @endphp

                                    {{-- Iterasi melalui semua status yang didefinisikan di OrderStatus --}}
                                    @foreach ($progressSteps as $key => $statusData)
                                        @php
                                            $stepClass = '';
                                            $icon = $icons[$key] ?? 'fas fa-question-circle'; // Default icon jika tidak ditemukan

                                            // Logika untuk menentukan kelas status
                                            if ($currentProgress == 'cancelled' && $key == 'cancelled') {
                                                $stepClass = 'cancelled';
                                            } elseif ($currentProgress == 'completed') {
                                                // Jika status saat ini adalah 'completed', semua status sebelumnya dan 'completed' itu sendiri adalah 'completed'
                                                if (!$isCurrentReached) {
                                                    $stepClass = 'completed';
                                                }
                                            } elseif ($key == $currentProgress) {
                                                $stepClass = 'active';
                                                $isCurrentReached = true; // Set flag karena status saat ini sudah tercapai
                                            } elseif (!$isCurrentReached) {
                                                // Jika status ini belum mencapai status aktif, itu berarti sudah selesai
                                                $stepClass = 'completed';
                                            }
                                        @endphp

                                        <div class="progress-step {{ $stepClass }}">
                                            <div class="progress-circle">
                                                <i class="{{ $icon }}"></i>
                                            </div>
                                            <div class="progress-text">{{ $statusData['label'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif


                            <p class="text-center mt-4 text-muted">Terima kasih atas kepercayaan Anda.</p>
                        @else
                            <div class="alert alert-warning text-center status-alert" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i> Transaksi tidak ditemukan atau terjadi
                                kesalahan.
                            </div>
                        @endif
                    </div>
                    <div class="card-footer text-center footer-text bg-light">
                        &copy; {{ date('Y') }} {{ $transaction->owner->brand_name ?? 'Layanan Bisnis Anda' }}.
                        Semua Hak Cipta Dilindungi.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
