<?php

namespace App\Domain\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class MediaUploadInspector
{
    public function inspect(UploadedFile $file): InspectedUpload
    {
        $originalFilename = trim($file->getClientOriginalName());
        $extension = Str::lower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();
        $sizeBytes = $file->getSize();
        $allowed = config('media.allowed_extensions', []);

        if ($extension === 'svg' || $mimeType === 'image/svg+xml') {
            throw ValidationException::withMessages(['file' => [__('media.svg_blocked')]]);
        }

        if (! is_string($mimeType)
            || ! is_int($sizeBytes)
            || ! is_array($allowed)
            || ! isset($allowed[$extension])
            || ! is_array($allowed[$extension])
            || ! in_array($mimeType, $allowed[$extension], true)) {
            throw ValidationException::withMessages(['file' => [__('media.invalid_type')]]);
        }

        $maxBytes = max(1, (int) config('media.max_upload_kb', 10240)) * 1024;
        if ($sizeBytes < 1 || $sizeBytes > $maxBytes) {
            throw ValidationException::withMessages(['file' => [__('media.invalid_size')]]);
        }

        $prefix = file_get_contents($file->getPathname(), false, null, 0, 512);
        if (! is_string($prefix) || preg_match('/^(?:MZ|#!)|<\?(?:php|=)/i', ltrim($prefix)) === 1) {
            throw ValidationException::withMessages(['file' => [__('media.executable_blocked')]]);
        }

        [$width, $height] = $this->dimensions($file, $mimeType);
        $checksum = hash_file('sha256', $file->getPathname());
        if (! is_string($checksum)) {
            throw ValidationException::withMessages(['file' => [__('media.invalid_file')]]);
        }

        $baseName = Str::slug(pathinfo($originalFilename, PATHINFO_FILENAME));
        $baseName = Str::limit($baseName !== '' ? $baseName : 'media', 180, '');

        return new InspectedUpload(
            originalFilename: Str::limit($originalFilename !== '' ? $originalFilename : 'media.'.$extension, 255, ''),
            normalizedFilename: $baseName.'.'.$extension,
            extension: $extension,
            mimeType: $mimeType,
            sizeBytes: $sizeBytes,
            checksumSha256: $checksum,
            width: $width,
            height: $height,
        );
    }

    /** @return array{?int, ?int} */
    private function dimensions(UploadedFile $file, string $mimeType): array
    {
        if (! str_starts_with($mimeType, 'image/')) {
            return [null, null];
        }

        $image = getimagesize($file->getPathname());
        if ($image === false || $image[0] < 1 || $image[1] < 1) {
            throw ValidationException::withMessages(['file' => [__('media.invalid_image')]]);
        }

        return [(int) $image[0], (int) $image[1]];
    }
}
