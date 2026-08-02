<?php

namespace App\Domain\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class MediaStorage
{
    public function putUploadedFile(string $disk, string $path, UploadedFile $file, string $visibility): void
    {
        $stream = fopen($file->getPathname(), 'rb');

        if ($stream === false) {
            throw new RuntimeException('Unable to open the validated upload stream.');
        }

        try {
            $stored = Storage::disk($disk)->put($path, $stream, ['visibility' => $visibility]);
        } finally {
            fclose($stream);
        }

        if (! $stored) {
            throw new RuntimeException('Unable to store the validated upload.');
        }
    }

    public function put(string $disk, string $path, string $contents, string $visibility): void
    {
        if (! Storage::disk($disk)->put($path, $contents, ['visibility' => $visibility])) {
            throw new RuntimeException('Unable to store the generated media object.');
        }
    }

    public function get(string $disk, string $path): string
    {
        return Storage::disk($disk)->get($path);
    }

    /** @return resource */
    public function readStream(string $disk, string $path)
    {
        $stream = Storage::disk($disk)->readStream($path);

        if (! is_resource($stream)) {
            throw new RuntimeException('Unable to open the media object stream.');
        }

        return $stream;
    }

    public function delete(string $disk, string $path): void
    {
        if (Storage::disk($disk)->exists($path) && ! Storage::disk($disk)->delete($path)) {
            throw new RuntimeException('Unable to delete the media object.');
        }
    }
}
