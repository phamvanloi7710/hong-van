<?php

namespace App\Domain\Media;

use App\Models\Media;
use App\Models\MediaVariant;
use GdImage;
use Illuminate\Support\Str;
use RuntimeException;

final readonly class ImageVariantGenerator
{
    public function __construct(private MediaStorage $storage) {}

    public function generate(Media $media): int
    {
        if (! str_starts_with($media->mime_type, 'image/')) {
            return 0;
        }

        $sourceBytes = $this->storage->get($media->disk, $media->path);
        $source = imagecreatefromstring($sourceBytes);
        if (! $source instanceof GdImage) {
            throw new RuntimeException('Validated image could not be decoded for variant generation.');
        }

        $generated = 0;

        try {
            foreach ($this->variantDefinitions() as $name => $dimensions) {
                $resized = $this->resize($source, $dimensions['width'], $dimensions['height']);

                try {
                    foreach ($this->supportedFormats() as $format) {
                        $bytes = $this->encode($resized, $format);
                        $variantKey = $name.'_'.$format;
                        $path = sprintf('media/variants/%s/%s/%s/%s.%s', $media->created_at?->utc()->format('Y') ?? now('UTC')->format('Y'), $media->created_at?->utc()->format('m') ?? now('UTC')->format('m'), $media->public_id, $name, $format);
                        $this->storage->put($media->disk, $path, $bytes, $media->visibility);

                        MediaVariant::query()->updateOrCreate(
                            ['media_id' => $media->getKey(), 'variant_key' => $variantKey],
                            [
                                'disk' => $media->disk,
                                'path' => $path,
                                'extension' => $format,
                                'mime_type' => 'image/'.$format,
                                'size_bytes' => strlen($bytes),
                                'checksum_sha256' => hash('sha256', $bytes),
                                'width' => imagesx($resized),
                                'height' => imagesy($resized),
                                'status' => 'ready',
                                'error_message' => null,
                                'generated_at' => now('UTC'),
                            ],
                        );
                        $generated++;
                    }
                } finally {
                    imagedestroy($resized);
                }
            }
        } finally {
            imagedestroy($source);
        }

        if ($generated === 0) {
            throw new RuntimeException('No configured WebP or AVIF encoder is available.');
        }

        return $generated;
    }

    /** @return array<string, array{width: int, height: int}> */
    private function variantDefinitions(): array
    {
        $definitions = [];

        foreach ((array) config('media.variants', []) as $name => $value) {
            if (is_string($name) && is_array($value)) {
                $definitions[Str::slug($name, '_')] = [
                    'width' => max(1, (int) ($value['width'] ?? 1)),
                    'height' => max(1, (int) ($value['height'] ?? 1)),
                ];
            }
        }

        return $definitions;
    }

    /** @return list<string> */
    private function supportedFormats(): array
    {
        return array_values(array_filter(
            (array) config('media.variant_formats', []),
            static fn (mixed $format): bool => $format === 'webp' && function_exists('imagewebp')
                || $format === 'avif' && function_exists('imageavif'),
        ));
    }

    private function resize(GdImage $source, int $maxWidth, int $maxHeight): GdImage
    {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $ratio = min(1, $maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
        $width = max(1, (int) floor($sourceWidth * $ratio));
        $height = max(1, (int) floor($sourceHeight * $ratio));
        $target = imagecreatetruecolor($width, $height);

        if (! $target instanceof GdImage) {
            throw new RuntimeException('Unable to allocate an image variant canvas.');
        }

        imagealphablending($target, false);
        imagesavealpha($target, true);

        imagecopyresampled($target, $source, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

        return $target;
    }

    private function encode(GdImage $image, string $format): string
    {
        ob_start();

        try {
            $encoded = match ($format) {
                'webp' => imagewebp($image, null, 82),
                'avif' => imageavif($image, null, 70),
                default => false,
            };
            $bytes = ob_get_contents();
        } finally {
            ob_end_clean();
        }

        if (! $encoded || ! is_string($bytes) || $bytes === '') {
            throw new RuntimeException('Unable to encode the '.$format.' image variant.');
        }

        return $bytes;
    }
}
