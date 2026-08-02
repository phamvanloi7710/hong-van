<?php

namespace App\Domain\Media;

use App\Domain\Audit\AuditTrail;
use App\Exceptions\ConflictException;
use App\Jobs\Media\GenerateMediaVariants;
use App\Models\Media;
use App\Models\MediaFolder;
use App\Models\MediaOperation;
use App\Models\MediaTag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final readonly class MediaLibraryService
{
    public function __construct(private MediaStorage $storage, private AuditTrail $auditTrail) {}

    /** @param array<string, mixed> $data */
    public function updateMetadata(Media $media, array $data, User $actor, Request $request): Media
    {
        $before = $media->only(['title', 'alt_text', 'caption']);
        $media->forceFill([
            'title' => $data['title'] ?? null,
            'alt_text' => $data['alt_text'] ?? null,
            'caption' => $data['caption'] ?? null,
            'updated_by' => $actor->getKey(),
        ])->save();

        if (array_key_exists('tag_ids', $data)) {
            $tagIds = MediaTag::query()->whereIn('public_id', (array) $data['tag_ids'])->pluck('id')->all();
            $media->tags()->syncWithPivotValues($tagIds, ['created_at' => now('UTC')]);
        }

        $this->auditTrail->record('media.metadata.updated', $actor, 'media', $media->public_id, before: $before, after: $media->only(['title', 'alt_text', 'caption']), request: $request);

        return $this->reload($media);
    }

    public function move(Media $media, ?MediaFolder $folder, User $actor, Request $request): Media
    {
        $beforeFolder = $media->folder?->public_id;
        $media->forceFill(['folder_id' => $folder?->getKey(), 'updated_by' => $actor->getKey()])->save();
        $this->auditTrail->record('media.moved', $actor, 'media', $media->public_id, before: ['folder_public_id' => $beforeFolder], after: ['folder_public_id' => $folder?->public_id], request: $request);

        return $this->reload($media);
    }

    public function trash(Media $media, User $actor, Request $request): Media
    {
        $this->guardUnused($media);
        $media->forceFill(['status' => 'trashed', 'deleted_by' => $actor->getKey(), 'updated_by' => $actor->getKey()])->save();
        $media->delete();
        $this->auditTrail->record('media.trashed', $actor, 'media', $media->public_id, after: ['status' => 'trashed'], request: $request);

        return $this->reload($media);
    }

    public function restore(Media $media, User $actor, Request $request): Media
    {
        $media->restore();
        $status = $media->variants()->where('status', 'ready')->exists() || ! str_starts_with($media->mime_type, 'image/') ? 'ready' : 'failed';
        $media->forceFill(['status' => $status, 'deleted_by' => null, 'updated_by' => $actor->getKey()])->save();
        $this->auditTrail->record('media.restored', $actor, 'media', $media->public_id, after: ['status' => $status], request: $request);

        return $this->reload($media);
    }

    public function deletePermanently(Media $media, ?User $actor, Request $request): void
    {
        if (! $media->trashed()) {
            throw new ConflictException(__('media.trash_before_delete'));
        }

        $this->guardUnused($media);
        $variants = $media->variants()->get(['disk', 'path']);
        $this->storage->delete($media->disk, $media->path);
        foreach ($variants as $variant) {
            $this->storage->delete($variant->disk, $variant->path);
        }

        DB::transaction(function () use ($actor, $media, $request): void {
            MediaOperation::query()->create([
                'media_id' => $media->getKey(),
                'media_public_id' => $media->public_id,
                'operation' => 'cleanup',
                'status' => 'completed',
                'attempts' => 1,
                'metadata' => ['reason' => $actor === null ? 'retention' : 'manual'],
                'started_at' => now('UTC'),
                'finished_at' => now('UTC'),
            ]);
            $this->auditTrail->record('media.deleted', $actor, 'media', $media->public_id, before: ['status' => 'trashed'], request: $request);
            $media->forceDelete();
        });
    }

    public function retry(Media $media, ?User $actor, Request $request): Media
    {
        if ($media->trashed() || ! str_starts_with($media->mime_type, 'image/')) {
            throw new ConflictException(__('media.retry_unavailable'));
        }

        $operation = MediaOperation::query()->create([
            'media_id' => $media->getKey(),
            'media_public_id' => $media->public_id,
            'operation' => 'variant_generation',
            'status' => 'queued',
            'attempts' => 0,
            'queue' => (string) config('media.queue', 'media'),
            'metadata' => ['retry' => true],
        ]);
        $media->forceFill(['status' => 'processing'])->save();
        GenerateMediaVariants::dispatch($media->getKey(), $operation->getKey())->onQueue((string) config('media.queue', 'media'));
        $this->auditTrail->record('media.retry.queued', $actor, 'media', $media->public_id, after: ['operation_public_id' => $operation->public_id], request: $request);

        return $this->reload($media);
    }

    private function guardUnused(Media $media): void
    {
        if ($media->usages()->exists()) {
            throw new ConflictException(__('media.in_use'));
        }
    }

    private function reload(Media $media): Media
    {
        return $media->load(['folder', 'variants', 'tags', 'usages'])->loadCount('usages');
    }
}
