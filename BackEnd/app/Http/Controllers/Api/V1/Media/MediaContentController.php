<?php

namespace App\Http\Controllers\Api\V1\Media;

use App\Domain\Media\MediaStorage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Media\ShowMediaContentRequest;
use App\Models\Media;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class MediaContentController extends Controller
{
    public function __invoke(ShowMediaContentRequest $request, Media $media, MediaStorage $storage): StreamedResponse
    {
        Gate::authorize('view', $media);
        $variantKey = $request->validated('variant');
        $variant = is_string($variantKey) ? $media->variants()->where('variant_key', $variantKey)->where('status', 'ready')->firstOrFail() : null;
        $disk = $variant->disk ?? $media->disk;
        $path = $variant->path ?? $media->path;
        $mimeType = $variant->mime_type ?? $media->mime_type;
        $size = $variant->size_bytes ?? $media->size_bytes;
        $filename = $variant === null ? $media->normalized_filename : $variant->variant_key.'.'.$variant->extension;
        $stream = $storage->readStream($disk, $path);

        return response()->stream(static function () use ($stream): void {
            try {
                fpassthru($stream);
            } finally {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Length' => (string) $size,
            'Content-Disposition' => 'inline; filename="'.addcslashes($filename, '"\\').'"',
            'Cache-Control' => 'private, max-age=300, nosniff',
        ]);
    }
}
