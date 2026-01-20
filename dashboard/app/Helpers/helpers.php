<?php

use App\Helpers\DataFetcher;
use App\Helpers\ImageHelper;
use Illuminate\Support\Facades\Auth;

// --- Helper untuk mendapatkan Root Entity ---
if (! function_exists('getUserDataRootEntity')) {
    /**
     * Mengembalikan entity utama yang bertanggung jawab atas data berdasarkan user yang login.
     * Ini bisa Owner untuk role 'owner', atau Outlet untuk role 'cashier'.
     *
     * @param \App\Models\User $user Instance user yang sedang login.
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    function getUserDataRootEntity()
    {
        $user = Auth::user();
        // Eager load relasi yang mungkin dibutuhkan untuk menghindari N+1 problem
        if ($user->role === 'owner') {
            // Load relasi 'owner' jika belum dimuat
            $user->loadMissing('owner');
            return $user->owner; // Mengembalikan instance Owner dari relasi
        } elseif ($user->role === 'cashier') {
            // Load relasi 'cashier' dan chain ke 'outlet' dan 'owner' jika belum dimuat
            $user->loadMissing('cashier.outlet.owner'); // eager load nested relations
            $cashier = $user->cashier;
            return $cashier ? $cashier->outlet : null; // Mengembalikan instance Outlet dari relasi Cashier
        }
        return null; // Untuk admin atau member, tidak ada root entity spesifik untuk data terfilter
    }
}


if (! function_exists('getData')) {
    /**
     * Mengembalikan instance DataFetcher untuk user yang sedang login,
     * memungkinkan chaining properti untuk mengakses data yang difilter.
     * Contoh: `getData()->devices` atau `getData()->transactions`.
     *
     * @return \App\Helpers\DataFetcher
     */
    function getData(): DataFetcher
    {
        return DataFetcher::forCurrentUser();
    }
}


if (! function_exists('generateOrderId')) {
    /**
     * Menghasilkan order ID yang unik.
     */
    function generateOrderId($outletCode, $paymentType, $time, $serviceType = null)
    {
        // Hapus prefix OUT-
        $outletCode = preg_replace('/^(OUT-)/i', '', $outletCode);

        // Map tipe pembayaran
        $paymentMap = [
            'manual' => 'ML',
            'member' => 'MB',
            'qris'   => 'QR',
        ];

        // Ambil singkatan pembayaran
        $paymentCode = $paymentMap[strtolower($paymentType)] ?? strtoupper($paymentType);

        // Ambil huruf depan serviceType (default: N untuk none)
        $serviceInitial = strtoupper(substr($serviceType ?? 'none', 0, 1));

        // Format waktu + microseconds
        $timeStr = $time->format('YmdHis') . substr($time->format('u'), 0, 4);

        // Gabungkan jadi Order ID
        return strtoupper("{$outletCode}-{$paymentCode}-{$serviceInitial}{$timeStr}");
    }

    if (! function_exists('getBrand')) {
        /**
         * Mengembalikan instance Owner (brand) berdasarkan user yang sedang login.
         *
         * - Jika login sebagai outlet, brand diambil dari data outlet yang tersimpan di session.
         * - Jika login sebagai owner, brand diambil dari properti owner pada user.
         *
         * @param mixed $user (opsional) Instance user yang sedang login.
         * @return \App\Models\Owner
         */
        if (! function_exists('getBrand')) {
            /**
             * Mengembalikan instance Owner berdasarkan user yang sedang login.
             *
             * @return \App\Models\Owner|null
             */
            function getBrand()
            {
                $user = Auth::user();

                if (!$user) {
                    return null;
                }

                if ($user->role === 'owner') {
                    return $user->owner;
                } elseif ($user->role === 'cashier') {
                    $cashier = $user->cashier;
                    if ($cashier && $cashier->outlet) {
                        return $cashier->outlet->owner;
                    }
                }
                return null;
            }
        }
    }

    // app/helpers.php

    if (!function_exists('getContrastColor')) {
        function getContrastColor($hexColor)
        {
            // Menghapus '#' jika ada
            $hexColor = str_replace('#', '', $hexColor);

            // Ubah warna hex ke RGB
            if (strlen($hexColor) == 3) {
                $r = hexdec(substr($hexColor, 0, 1) . substr($hexColor, 0, 1));
                $g = hexdec(substr($hexColor, 1, 1) . substr($hexColor, 1, 1));
                $b = hexdec(substr($hexColor, 2, 1) . substr($hexColor, 2, 1));
            } else {
                $r = hexdec(substr($hexColor, 0, 2));
                $g = hexdec(substr($hexColor, 2, 2));
                $b = hexdec(substr($hexColor, 4, 2));
            }

            // Hitung Luminance (kecerahan)
            // YIQ equation from http://en.wikipedia.org/wiki/YIQ
            $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

            // Kembalikan warna teks yang paling kontras (hitam atau putih)
            return ($yiq >= 128) ? '#000000' : '#ffffff';
        }
    }

    if (! function_exists('uploadImage')) {
        function uploadImage(\Illuminate\Http\UploadedFile $file, string $folder): ?string
        {
            return ImageHelper::uploadImage($file, $folder);
        }
    }

    if (! function_exists('deleteImage')) {
        function deleteImage(?string $path): bool
        {
            return ImageHelper::deleteImage($path);
        }
    }
}
