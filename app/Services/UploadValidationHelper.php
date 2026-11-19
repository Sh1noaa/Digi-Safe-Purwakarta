<?php

namespace App\Services;

use Closure;
use Illuminate\Http\UploadedFile;

class UploadValidationHelper
{
    private const MIME_TO_EXT = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    private const IMAGETYPE_TO_EXT = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_WEBP => 'webp',
        IMAGETYPE_GIF => 'gif',
    ];

    /**
     * Aturan validasi gambar/GIF yang memverifikasi magic bytes (getimagesize) sekaligus MIME.
     */
    public static function imageRules(int $maxKb): array
    {
        $rules = ['nullable', 'file', 'max:' . $maxKb];

        if (extension_loaded('fileinfo')) {
            $rules[] = 'mimes:jpg,jpeg,png,webp,gif';
        }

        $rules[] = function (string $attribute, mixed $value, Closure $fail): void {
            if (! $value instanceof UploadedFile) {
                $fail('File tidak valid.');

                return;
            }

            $info = @getimagesize($value->getRealPath());
            if ($info === false || ! isset(self::IMAGETYPE_TO_EXT[$info[2]])) {
                $fail('Format file harus berupa gambar JPG, PNG, WEBP, atau GIF yang valid.');
            }
        };

        return $rules;
    }

    /**
     * Tentukan ekstensi file yang 100% aman dari tipe gambar aktual (tidak pernah percaya ekstensi dari client).
     */
    public static function safeExtension(UploadedFile $file): string
    {
        $info = @getimagesize($file->getRealPath());
        if ($info !== false && isset(self::IMAGETYPE_TO_EXT[$info[2]])) {
            return self::IMAGETYPE_TO_EXT[$info[2]];
        }

        $mime = strtolower((string) $file->getMimeType());
        if (isset(self::MIME_TO_EXT[$mime])) {
            return self::MIME_TO_EXT[$mime];
        }

        return 'jpg';
    }

    /**
     * Hapus file gambar lama di public/images/{subDir} dan storage/app/public/{subDir} untuk mencegah penumpukan disk.
     */
    public static function deleteOldAsset(?string $relativePath, string $subDir): void
    {
        if (! $relativePath || str_starts_with($relativePath, 'http://') || str_starts_with($relativePath, 'https://')) {
            return;
        }

        $prefix = 'images/' . trim($subDir, '/') . '/';
        if (! str_starts_with($relativePath, $prefix)) {
            return;
        }

        $filename = basename($relativePath);
        if ($filename === '' || str_contains($filename, '..')) {
            return;
        }

        $publicFile = public_path($prefix . $filename);
        if (is_file($publicFile)) {
            @unlink($publicFile);
        }

        $storageFile = storage_path('app/public/' . trim($subDir, '/') . '/' . $filename);
        if (is_file($storageFile)) {
            @unlink($storageFile);
        }
    }
}
