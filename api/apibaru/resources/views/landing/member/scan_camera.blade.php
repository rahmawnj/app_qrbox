@extends('layouts.landingpage.dashboard-app')

@section('title', 'Scan & Pilih Layanan')
@section('header_title', 'Self Service')

@push('styles')
    <style>
        :root {
            --card-background: #ffffff;
            --text-color: #333;
            --shadow-light: 0 4px 15px rgba(0, 0, 0, 0.08);
            --border-radius: 12px;
        }

        
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            transition: all 0.3s ease;
        }
        
        .service-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .service-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1.5rem 1rem;
            border: 2px solid #ddd;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all 0.3s ease;
            user-select: none;
        }

        .service-card:hover {
            border-color: var(--primary-color);
            background-color: #e8f5e9;
        }
        .service-card.active {
            border-color: var(--primary-color);
            background-color: #e8f5e9;
            box-shadow: 0 0 0 3px var(--primary-color);
        }

        .service-card-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .service-card-title {
            font-weight: 600;
            font-size: 1rem;
            text-align: center;
            margin-bottom: 0;
        }

        /* Styling untuk container scanner */
        #qr-and-manual-input-container {
            display: none; /* Sembunyikan secara default */
            margin-top: 2rem;
            transition: all 0.5s ease-in-out;
            opacity: 0;
            max-height: 0;
            overflow: hidden;
        }
        #qr-and-manual-input-container.show {
            display: block;
            opacity: 1;
            max-height: 1000px; /* Cukup besar untuk transisi */
        }

        .tab-content {
            padding: 1rem;
        }
        
        .nav-tabs {
            border-bottom: 1px solid #ddd;
        }
        
        .nav-link {
            color: #555;
            font-weight: 500;
            padding: 1rem;
            border: none;
            border-radius: 0;
            position: relative;
        }
        
        .nav-link.active {
            color: var(--primary-color);
            background-color: transparent;
            border-color: transparent;
        }
        
        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: var(--primary-color);
        }

        #qr-reader-container {
            width: 100%;
            max-width: 500px;
            margin: 20px auto;
            border-radius: var(--border-radius);
            overflow: hidden;
            background-color: #fff;
            box-shadow: var(--shadow-light);
            position: relative;
        }

        #qr-reader {
            width: 100%;
            border-radius: calc(var(--border-radius) - 2px);
            overflow: hidden;
        }

        #qr-result {
            margin-top: 15px;
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--primary-color);
            text-align: center;
            padding: 1rem;
            background-color: #e8f5e9;
            border-radius: var(--border-radius);
            display: none;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .qr-result-loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
@endpush

@section('content')
    @if (session()->has('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    showConfirmButton: false,
                    timer: 2500
                });
            });
        </script>
    @endif

    @if (session()->has('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: '{{ session('error') }}',
                    showConfirmButton: false,
                    timer: 3000
                });
            });
        </script>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header text-center">
                    <h4 class="card-title mb-0">Pilih Tipe Layanan</h4>
                </div>
                <div class="card-body">
                    <p class="text-center text-muted mb-4">Pilih layanan yang Anda inginkan untuk melanjutkan.</p>
                    <div id="service-options" class="service-list">
                        @foreach ($service_types as $service)
                            <div class="service-card" data-service-name="{{ $service->name }}">
                                <i class="fas fa-{{ $service->name === 'Dryer' ? 'tshirt' : 'water' }} service-card-icon"></i>
                                <span class="service-card-title">{{ $service->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bagian ini akan ditampilkan setelah layanan dipilih --}}
    <div class="row" id="qr-and-manual-input-container">
        <div class="col-12">
            <div class="card">
                <div class="card-header text-center">
                    <h4 class="card-title mb-0">Scan atau Masukkan Kode Perangkat</h4>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs justify-content-center" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="scan-tab" data-bs-toggle="tab" href="#scan" role="tab" aria-controls="scan" aria-selected="true">
                                <i class="fas fa-qrcode me-2"></i>Scan QR
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="manual-tab" data-bs-toggle="tab" href="#manual" role="tab" aria-controls="manual" aria-selected="false">
                                <i class="fas fa-keyboard me-2"></i>Input Manual
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="myTabContent">
                        <!-- Tab Konten untuk Scan QR -->
                        <div class="tab-pane fade show active" id="scan" role="tabpanel" aria-labelledby="scan-tab">
                            <p class="text-center text-muted mt-4 mb-4">Arahkan kamera Anda ke QR code pada mesin.</p>
                            <div id="qr-reader-container">
                                <div id="qr-reader"></div>
                            </div>
                            <div id="qr-result" class="alert mt-4" role="alert">
                                <div class="qr-result-loader"></div>
                                Menunggu QR code...
                            </div>
                        </div>

                        <!-- Tab Konten untuk Input Manual -->
                        <div class="tab-pane fade" id="manual" role="tabpanel" aria-labelledby="manual-tab">
                            <p class="text-center text-muted mt-4 mb-4">Masukkan kode perangkat secara manual jika kamera tidak berfungsi.</p>
                            <form id="manual-input-form" class="mx-auto" style="max-width: 400px;">
                                <div class="form-group mb-3">
                                    <label for="device_code_input" class="form-label">Kode Perangkat</label>
                                    <input type="text" class="form-control" id="device_code_input" placeholder="Contoh: DEV-ABCD12" required>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Submit Kode</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Formulir untuk mengirimkan data ke backend --}}
    <form id="self-service-form" action="{{ route('home.member.process.qr.data') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="device_code" id="form-device-code">
        <input type="hidden" name="service_name" id="form-service-name">
    </form>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const qrCodeScanner = new Html5Qrcode("qr-reader");
            const qrResultElement = document.getElementById('qr-result');
            const qrAndManualInputContainer = document.getElementById('qr-and-manual-input-container');
            const manualInputForm = document.getElementById('manual-input-form');
            const deviceCodeInput = document.getElementById('device_code_input');
            const serviceOptionsContainer = document.getElementById('service-options');
            const formDeviceCodeInput = document.getElementById('form-device-code');
            const formServiceNameInput = document.getElementById('form-service-name');
            const selfServiceForm = document.getElementById('self-service-form');
            const scanTab = document.getElementById('scan-tab');
            const manualTab = document.getElementById('manual-tab');
            
            let isScanning = false;
            let selectedService = null;

            // Fungsi untuk memulai scanner kamera belakang
            const startScanner = () => {
                if (isScanning) return;
                qrResultElement.style.display = 'flex';
                qrResultElement.classList.remove('alert-success', 'alert-danger');
                qrResultElement.classList.add('alert-info');
                qrResultElement.innerHTML = '<div class="qr-result-loader"></div> Menunggu QR code...';

                Html5Qrcode.getCameras().then(devices => {
                    if (devices && devices.length) {
                        let cameraId = null;
                        const rearCamera = devices.find(device => device.label.toLowerCase().includes('back') || device.label.toLowerCase().includes('rear'));
                        if (rearCamera) {
                            cameraId = rearCamera.id;
                        } else {
                            cameraId = devices[0].id;
                        }
                        qrCodeScanner.start(
                            cameraId, {
                                fps: 10,
                                qrbox: {
                                    width: 250,
                                    height: 250
                                },
                                supportedScanFormats: [Html5QrcodeSupportedFormats.QR_CODE]
                            },
                            (decodedText) => onScanSuccess(decodedText),
                            (error) => onScanFailure(error)
                        ).then(() => {
                            isScanning = true;
                        }).catch(err => {
                            console.error('Gagal memulai scanner kamera:', err);
                            handleCameraError();
                        });
                    } else {
                        handleCameraError();
                    }
                }).catch(err => {
                    console.error('Terjadi kesalahan saat mendapatkan daftar kamera:', err);
                    handleCameraError();
                });
            };

            // Fungsi untuk menghentikan scanner kamera
            const stopScanner = () => {
                if (!isScanning) return;
                qrCodeScanner.stop().then(() => {
                    isScanning = false;
                }).catch(err => {
                    console.error("Gagal menghentikan scanner:", err);
                });
            };

            const handleCameraError = () => {
                qrResultElement.classList.remove('alert-info', 'alert-success');
                qrResultElement.classList.add('alert-danger');
                qrResultElement.innerText = 'Gagal memulai kamera. Silakan gunakan Input Manual.';
                qrResultElement.style.display = 'flex';
                // Alihkan otomatis ke tab input manual jika kamera gagal
                manualTab.click();
            };
            
            // Fungsi untuk mengirimkan data formulir
            const submitForm = (deviceCode) => {
                if (!selectedService) {
                    Swal.fire('Kesalahan', 'Silakan pilih layanan terlebih dahulu.', 'error');
                    return;
                }
                
                formDeviceCodeInput.value = deviceCode;
                formServiceNameInput.value = selectedService;
                
                Swal.fire({
                    title: 'Konfirmasi Pesanan',
                    html: `Anda akan memesan layanan <strong>${selectedService}</strong> untuk perangkat <strong>${deviceCode}</strong>.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#4CAF50',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Pesan Sekarang!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses Pesanan...',
                            text: 'Mohon tunggu sebentar...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        selfServiceForm.submit();
                    } else {
                        // Reset form atau state
                        formDeviceCodeInput.value = '';
                        // Jika dari scan, mulai lagi scanner
                        if (isScanning) {
                            startScanner();
                        }
                    }
                });
            };

            // Callback ketika QR code berhasil dipindai dari kamera
            const onScanSuccess = (decodedText) => {
                stopScanner();
                qrResultElement.classList.remove('alert-info', 'alert-danger');
                qrResultElement.classList.add('alert-success');
                qrResultElement.innerHTML = '<i class="fas fa-check-circle me-2"></i> QR Code terdeteksi, sedang memproses...';
                
                submitForm(decodedText);
            };

            // Callback ketika QR code gagal dipindai (untuk kamera)
            const onScanFailure = (error) => {
                // Abaikan pesan error selama live scanning
            };

            // Event listener untuk pilihan layanan
            serviceOptionsContainer.addEventListener('click', function(e) {
                const serviceCard = e.target.closest('.service-card');
                if (serviceCard) {
                    // Reset status 'active' pada semua kartu
                    document.querySelectorAll('.service-card').forEach(card => card.classList.remove('active'));
                    
                    // Tambahkan status 'active' pada kartu yang dipilih
                    serviceCard.classList.add('active');
                    selectedService = serviceCard.dataset.serviceName;
                    
                    // Tampilkan bagian scan/input manual
                    qrAndManualInputContainer.classList.add('show');
                    
                    // Alihkan ke tab Scan QR dan mulai scanner
                    new bootstrap.Tab(scanTab).show();
                    startScanner();
                }
            });

            // Event listener untuk form input manual
            manualInputForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const deviceCode = deviceCodeInput.value.trim();
                if (deviceCode) {
                    submitForm(deviceCode);
                } else {
                    Swal.fire('Input Tidak Valid', 'Kode perangkat tidak boleh kosong.', 'warning');
                }
            });

            // Pastikan scanner dihentikan saat beralih tab
            manualTab.addEventListener('shown.bs.tab', stopScanner);
            scanTab.addEventListener('shown.bs.tab', startScanner);
        });
    </script>
@endpush
