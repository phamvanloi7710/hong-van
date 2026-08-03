<?php

namespace App\Domain\Seo;

use App\Domain\Audit\AuditTrail;
use App\Domain\Media\MediaUsageTracker;
use App\Models\Media;
use App\Models\SeoMeta;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final readonly class SeoMetaManager
{
    public function __construct(
        private MediaUsageTracker $mediaUsage,
        private AuditTrail $auditTrail,
        private SitemapCache $sitemaps,
    ) {}

    /** @param array<string, mixed> $payload */
    public function save(string $type, Model $entity, User $actor, array $payload): SeoMeta
    {
        return DB::transaction(function () use ($type, $entity, $actor, $payload): SeoMeta {
            $meta = SeoMeta::query()->firstOrNew([
                'seoable_type' => $type,
                'seoable_id' => $entity->getKey(),
                'locale' => $payload['locale'],
            ]);
            $before = $meta->exists ? $meta->toArray() : [];
            $oldImage = $meta->ogImage()->first();
            $newImage = isset($payload['og_image_media_id'])
                ? Media::query()->where('public_id', $payload['og_image_media_id'])->firstOrFail()
                : null;

            $meta->fill(Arr::except($payload, ['og_image_media_id']));
            $meta->og_image_media_id = $newImage?->getKey();
            $meta->updated_by = $actor->getKey();
            if (! $meta->exists) {
                $meta->created_by = $actor->getKey();
            }
            $meta->save();

            if ($oldImage instanceof Media && $oldImage->getKey() !== $newImage?->getKey()) {
                $this->mediaUsage->release($oldImage, 'seo_meta', $meta->public_id, 'og_image');
            }
            if ($newImage instanceof Media) {
                $this->mediaUsage->track($newImage, 'seo_meta', $meta->public_id, 'og_image', ['locale' => $meta->locale]);
            }

            $meta->load(['ogImage.variants']);
            $this->auditTrail->record(
                $before === [] ? 'seo.meta.created' : 'seo.meta.updated',
                $actor,
                'seo_meta',
                $meta->public_id,
                before: $before,
                after: $meta->toArray(),
                metadata: ['seoable_type' => $type, 'seoable_public_id' => $entity->getAttribute('public_id'), 'locale' => $meta->locale],
            );
            $this->sitemaps->invalidate();

            return $meta;
        });
    }
}
