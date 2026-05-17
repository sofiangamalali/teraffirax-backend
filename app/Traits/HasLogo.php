<?php

namespace App\Traits;

trait HasLogo
{
    /**
     * Resolve any image or logo URL dynamically and robustly.
     *
     * @param string|null $filename The filename or reference (e.g. 'amazon.png', 'wallet', 'telegram')
     * @param string $folder The subfolder under public/uploads/ (e.g. 'vendors', 'platforms', 'withdraw-methods')
     * @return string|null
     */
    protected function resolveLogoUrl(?string $filename, string $folder): ?string
    {
        if (!$filename) {
            return null;
        }

        // 1. If it's already a full URL, return it directly
        if (filter_var($filename, FILTER_VALIDATE_URL)) {
            return $filename;
        }

        // 2. If the filename already contains an extension (e.g. "amazon.png")
        if (str_contains($filename, '.')) {
            return asset("uploads/{$folder}/{$filename}");
        }

        // 3. If it's just a key/name (e.g. "telegram" or "wallet"), detect the extension on disk dynamically
        $basePath = "uploads/{$folder}/{$filename}";
        $extensions = ['png', 'svg', 'webp', 'jpg', 'jpeg'];

        foreach ($extensions as $ext) {
            if (file_exists(public_path("{$basePath}.{$ext}"))) {
                return asset("{$basePath}.{$ext}");
            }
        }

        // Fallback to default png if no file is found
        return asset("{$basePath}.png");
    }
}
