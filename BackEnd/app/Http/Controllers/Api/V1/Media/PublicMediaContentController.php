<?php

namespace App\Http\Controllers\Api\V1\Media;

use App\Domain\Media\MediaStorage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Media\ShowMediaContentRequest;
use App\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PublicMediaContentController extends Controller
{
    public function __invoke(ShowMediaContentRequest $request, Media $media, MediaStorage $storage): StreamedResponse
    {
        abort_unless($media->status === 'ready' && $media->visibility === 'public' && str_starts_with($media->mime_type, 'image/'), 404);
        $variantKey = $request->validated('variant');
        $variant = is_string($variantKey) ? $media->variants()->where('variant_key', $variantKey)->where('status', 'ready')->firstOrFail() : null;
        $stream = $storage->readStream($variant->disk ?? $media->disk, $variant->path ?? $media->path);

        return response()->stream(static function () use ($stream): void {
            try {
                fpassthru($stream);
            } finally {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $variant->mime_type ?? $media->mime_type,
            'Content-Length' => (string) ($variant->size_bytes ?? $media->size_bytes),
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'public, max-age=86400, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
