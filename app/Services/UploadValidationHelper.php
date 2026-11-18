<?php

namespace App\Services;

use Closure;
use Illuminate\Http\UploadedFile;

class UploadValidationHelper
{
    /**
     * Aturan validasi gambar/GIF yang tetap aman meskipun ekstensi fileinfo belum aktif di PHP lokal.
     */
    public static function imageRules(int $maxKb): array
    {
        if (extension_loaded('fileinfo')) {
            return ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:' . $maxKb];
        }

        return [
            'nullable',
            'file',
            'max:' . $maxKb,
            function (string $attribute, mixed $value, Closure $fail): void {
                if (! $value instanceof UploadedFile) {
                    $fail('File tidak valid.');

                    return;
                }

                $ext = strtolower($value->getClientOriginalExtension());
                $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                $info = @getimagesize($value->getRealPath());
                $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF];

                if (! in_array($ext, $allowedExts, true) || $info === false || ! in_array($info[2], $allowedTypes, true)) {
                    $fail('Format file harus berupa gambar JPG, PNG, WEBP, atau GIF yang valid.');
                }
            },
        ];
    }
}

