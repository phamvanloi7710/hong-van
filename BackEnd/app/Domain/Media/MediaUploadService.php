<?php

namespace App\Domain\Media;

use App\Domain\Audit\AuditTrail;
use App\Jobs\Media\GenerateMediaVariants;
use App\Models\Media;
use App\Models\MediaFolder;
use App\Models\MediaOperation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final readonly class MediaUploadService
{
    public function __construct(
        private MediaUploadInspector $inspector,
        private MediaStorage $storage,
        private AuditTrail $auditTrail,
    ) {}

    /** @param array<string, mixed> $attributes */
    public function upload(UploadedFile $file, array $attributes, User $actor, Request $request): Media
    {
        $inspected = $this->inspector->inspect($file);
        $disk = (string) config('media.disk', 'public');
        $visibility = (string) config('media.visibility', 'public');
        $publicId = (string) Str::ulid();
        $path = sprintf('media/originals/%s/%s/%s.%s', now('UTC')->format('Y'), now('UTC')->format('m'), $publicId, $inspected->extension);
        $folder = $this->folder($attributes['folder_id'] ?? null);

        $this->storage->putUploadedFile($disk, $path, $file, $visibility);

        try {
            [$media, $operation] = DB::transaction(function () use ($attributes, $actor, $disk, $folder, $inspected, $path, $publicId, $visibility): array {
                $media = Media::query()->create([
                    'public_id' => $publicId,
                    'folder_id' => $folder?->getKey(),
                    'disk' => $disk,
                    'path' => $path,
                    'original_filename' => $inspected->originalFilename,
                    'normalized_filename' => $inspected->normalizedFilename,
                    'extension' => $inspected->extension,
                    'mime_type' => $inspected->mimeType,
                    'size_bytes' => $inspected->sizeBytes,
                    'checksum_sha256' => $inspected->checksumSha256,
                    'width' => $inspected->width,
                    'height' => $inspected->height,
                    'status' => str_starts_with($inspected->mimeType, 'image/') ? 'processing' : 'ready',
                    'visibility' => in_array($visibility, ['private', 'public'], true) ? $visibility : 'private',
                    'title' => $attributes['title'] ?? null,
                    'alt_text' => $attributes['alt_text'] ?? null,
                    'caption' => $attributes['caption'] ?? null,
                    'metadata' => ['source' => 'admin_upload'],
                    'uploaded_by' => $actor->getKey(),
                    'updated_by' => $actor->getKey(),
                ]);

                $operation = MediaOperation::query()->create([
                    'media_id' => $media->getKey(),
                    'media_public_id' => $media->public_id,
                    'operation' => 'variant_generation',
                    'status' => str_starts_with($inspected->mimeType, 'image/') ? 'queued' : 'completed',
                    'attempts' => 0,
                    'queue' => (string) config('media.queue', 'media'),
                    'metadata' => ['mime_type' => $inspected->mimeType],
                    'finished_at' => str_starts_with($inspected->mimeType, 'image/') ? null : now('UTC'),
                ]);

                return [$media, $operation];
            });
        } catch (Throwable $exception) {
            $this->storage->delete($disk, $path);
            throw $exception;
        }

        if ($media->status === 'processing') {
            GenerateMediaVariants::dispatch($media->getKey(), $operation->getKey())
                ->onQueue((string) config('media.queue', 'media'));
        }

        $this->auditTrail->record(
            'media.uploaded',
            $actor,
            'media',
            $media->public_id,
            after: [
                'mime_type' => $media->mime_type,
                'size_bytes' => $media->size_bytes,
                'status' => $media->status,
                'folder_public_id' => $folder?->public_id,
            ],
            request: $request,
        );

        return $media->load(['folder', 'variants', 'tags', 'usages']);
    }

    private function folder(mixed $publicId): ?MediaFolder
    {
        return is_string($publicId) && $publicId !== ''
            ? MediaFolder::query()->where('public_id', $publicId)->firstOrFail()
            : null;
    }
}
