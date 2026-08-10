<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageHelper
{
    /**
     * Mengunggah file gambar dan mengembalikan path publiknya.
     * Path yang disimpan akan berbentuk 'storage/images/path/to/image.jpg'.
     *
     * @param UploadedFile $file Instance file yang diunggah.
     * @param string $folder Subdirektori tempat menyimpan gambar, relatif ke 'images/'.
     * @return string|null Path gambar yang siap untuk disimpan di database.
     */
    public static function uploadImage(UploadedFile $file, string $folder): ?string
    {
        try {
            // Gabungkan folder 'images/' dengan subfolder yang diberikan
            $path = $file->store('images/' . $folder, 'public');
            
            // Mengembalikan path yang dapat diakses publik
            return 'storage/' . $path;
        } catch (\Exception $e) {
            Log::error("Failed to upload image: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Menghapus file gambar dari penyimpanan.
     *
     * @param string|null $path Path gambar yang akan dihapus, seperti 'storage/images/path/to/image.jpg'.
     * @return bool
     */
    public static function deleteImage(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        try {
            // Hapus prefix 'storage/' untuk mendapatkan path yang benar di disk
            $diskPath = str_replace('storage/', '', $path);
            
            // Hapus file dari disk 'public'
            return Storage::disk('public')->delete($diskPath);
        } catch (\Exception $e) {
            Log::error("Failed to delete image: {$e->getMessage()}");
            return false;
        }
    }
}