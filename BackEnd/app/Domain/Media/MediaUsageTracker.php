<?php

namespace App\Domain\Media;

use App\Models\Media;
use App\Models\MediaUsage;
use InvalidArgumentException;

final class MediaUsageTracker
{
    /** @param array<string, bool|int|string|null> $metadata */
    public function track(Media $media, string $ownerType, string $ownerPublicId, string $field, array $metadata = []): MediaUsage
    {
        if (! in_array($ownerType, (array) config('media.usage_owner_types', []), true)
            || preg_match('/^[A-Za-z0-9_.:-]{1,100}$/', $field) !== 1
            || preg_match('/^[A-Za-z0-9_.:-]{1,26}$/', $ownerPublicId) !== 1) {
            throw new InvalidArgumentException('Invalid media usage owner contract.');
        }

        return MediaUsage::query()->updateOrCreate(
            [
                'media_id' => $media->getKey(),
                'owner_type' => $ownerType,
                'owner_public_id' => $ownerPublicId,
                'field' => $field,
            ],
            ['metadata' => $metadata === [] ? null : $metadata],
        );
    }

    public function release(Media $media, string $ownerType, string $ownerPublicId, string $field): void
    {
        MediaUsage::query()
            ->where('media_id', $media->getKey())
            ->where('owner_type', $ownerType)
            ->where('owner_public_id', $ownerPublicId)
            ->where('field', $field)
            ->delete();
    }
}
