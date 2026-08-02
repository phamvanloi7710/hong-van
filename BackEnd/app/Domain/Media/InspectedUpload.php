<?php

namespace App\Domain\Media;

final readonly class InspectedUpload
{
    public function __construct(
        public string $originalFilename,
        public string $normalizedFilename,
        public string $extension,
        public string $mimeType,
        public int $sizeBytes,
        public string $checksumSha256,
        public ?int $width,
        public ?int $height,
    ) {}
}
