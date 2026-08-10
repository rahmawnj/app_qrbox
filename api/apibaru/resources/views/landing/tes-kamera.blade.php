<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tes Kamera & QR Scanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        #reader {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background-color: #fff;
        }
        #result {
            margin-top: 20px;
            font-size: 1.25rem;
            font-weight: bold;
            color: #0d6efd;
        }
        .controls {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row text-center">
        <div class="col-md-8 mx-auto">
            <h1 class="mb-4">Tes Kamera & Scan QR</h1>
            <p class="lead">Pilih metode scan: langsung dengan kamera atau unggah gambar.</p>

            <div class="controls mb-3">
                <button id="start-scan-btn" class="btn btn-primary me-2">Mulai Scan Kamera</button>
                <button id="stop-scan-btn" class="btn btn-danger me-2" style="display: none;">Hentikan Scan</button>
                
                <label for="qr-file-input" class="btn btn-success">Unggah Gambar QR</label>
                <input type="file" id="qr-file-input" accept="image/*" style="display: none;">
            </div>

            <div id="reader" class="mb-4"></div>

            <div class="mt-4 p-3 bg-light border rounded">
                <h5>Hasil Scan:</h5>
                <div id="result">Menunggu QR code...</div>
            </div>
            
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const qrCodeScanner = new Html5Qrcode("reader");
        const resultElement = document.getElementById('result');
        const startBtn = document.getElementById('start-scan-btn');
        const stopBtn = document.getElementById('stop-scan-btn');
        const fileInput = document.getElementById('qr-file-input');
        let isScanning = false;

        const config = {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            supportedScanFormats: [
                Html5QrcodeSupportedFormats.QR_CODE
            ]
        };

        const onScanSuccess = (decodedText, decodedResult) => {
            if (decodedText) {
                console.log(`Scan berhasil: ${decodedText}`);
                resultElement.innerText = decodedText;
                // Untuk scan kamera, kita hentikan. Untuk file, tidak perlu.
                if (isScanning) {
                    stopScanner();
                }
            }
        };

        const onScanFailure = (error) => {
            // Error ini akan terus muncul jika tidak ada QR code, tidak perlu di log
        };

        const startScanner = () => {
            if (isScanning) return;
            
            Html5Qrcode.getCameras().then(devices => {
                if (devices && devices.length) {
                    let rearCameraId = null;
                    for (const device of devices) {
                        const label = device.label.toLowerCase();
                        if (label.includes('back') || label.includes('rear')) {
                            rearCameraId = device.id;
                            break;
                        }
                    }
                    const cameraId = rearCameraId || devices[0].id;
                    
                    qrCodeScanner.start(
                        cameraId,
                        config,
                        onScanSuccess,
                        onScanFailure
                    ).then(() => {
                        console.log("Scanner berhasil dimulai.");
                        isScanning = true;
                        startBtn.style.display = 'none';
                        stopBtn.style.display = 'inline-block';
                        resultElement.innerText = 'Mulai scanning... Arahkan kamera ke QR code.';
                    }).catch(err => {
                        console.error('Gagal memulai scanner:', err);
                        alert('Gagal memulai scanner. Pastikan halaman menggunakan HTTPS atau localhost, atau gunakan fitur Unggah Gambar QR.');
                    });
                } else {
                    alert('Tidak ada kamera yang terdeteksi pada perangkat ini.');
                }
            }).catch(err => {
                console.error('Terjadi kesalahan saat mendapatkan daftar kamera:', err);
                alert('Terjadi kesalahan saat mendapatkan daftar kamera: ' + err.message + '. Silakan coba unggah gambar QR sebagai alternatif.');
            });
        };

        const stopScanner = () => {
            if (!isScanning) return;
            qrCodeScanner.stop().then(() => {
                console.log("Scanner berhasil dihentikan.");
                isScanning = false;
                startBtn.style.display = 'inline-block';
                stopBtn.style.display = 'none';
                resultElement.innerText = 'Menunggu QR code...';
            }).catch(err => {
                console.error("Gagal menghentikan scanner:", err);
            });
        };

        // Event listener untuk tombol Start
        startBtn.addEventListener('click', startScanner);
        // Event listener untuk tombol Stop
        stopBtn.addEventListener('click', stopScanner);

        // Event listener untuk input file
        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const imageFile = e.target.files[0];
                resultElement.innerText = 'Memindai gambar...';
                qrCodeScanner.scanFile(imageFile, true)
                    .then(decodedText => {
                        console.log('Scan file berhasil:', decodedText);
                        resultElement.innerText = `Hasil scan dari file: ${decodedText}`;
                    })
                    .catch(err => {
                        console.error('Gagal memindai file:', err);
                        resultElement.innerText = 'Gagal memindai QR code dari gambar.';
                    });
            }
        });
    });
</script>
</body>
</html>