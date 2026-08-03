<?php

namespace App\Domain\PageBuilder;

use App\Domain\Media\MediaUsageTracker;
use App\Models\Media;
use App\Models\MediaUsage;
use App\Models\PageVersion;

final readonly class PageBuilderMediaUsageSynchronizer
{
    public function __construct(private PageBuilderMediaResolver $resolver, private MediaUsageTracker $tracker) {}

    /** @param array<string, mixed> $document */
    public function sync(PageVersion $version, array $document): void
    {
        $references = $this->resolver->references($document);
        $ids = array_values(array_unique(array_column($references, 'publicId')));
        $media = $ids === [] ? collect() : Media::query()->whereIn('public_id', $ids)->get()->keyBy('public_id');

        MediaUsage::query()
            ->where('owner_type', 'page_version')
            ->where('owner_public_id', $version->public_id)
            ->delete();

        foreach ($references as $reference) {
            $item = $media->get($reference['publicId']);
            if ($item instanceof Media) {
                $this->tracker->track($item, 'page_version', $version->public_id, $reference['field']);
            }
        }
    }
}
