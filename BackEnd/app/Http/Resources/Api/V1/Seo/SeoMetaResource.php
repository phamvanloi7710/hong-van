<?php

namespace App\Http\Resources\Api\V1\Seo;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class SeoMetaResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $image = $this->resource->ogImage;

        return [
            'public_id' => $this->resource->public_id,
            'locale' => $this->resource->locale,
            'meta_title' => $this->resource->meta_title,
            'meta_description' => $this->resource->meta_description,
            'canonical_url' => $this->resource->canonical_url,
            'robots_index' => (bool) $this->resource->robots_index,
            'robots_follow' => (bool) $this->resource->robots_follow,
            'og_title' => $this->resource->og_title,
            'og_description' => $this->resource->og_description,
            'og_image' => $image === null ? null : [
                'public_id' => $image->public_id,
                'original_filename' => $image->original_filename,
                'mime_type' => $image->mime_type,
                'width' => $image->width,
                'height' => $image->height,
                'alt_text' => $image->alt_text,
                'content_url' => '/api/admin/v1/media/'.$image->public_id.'/content',
                'variants' => $image->relationLoaded('variants') ? $image->variants->where('status', 'ready')->map(static fn ($variant): array => [
                    'key' => $variant->variant_key,
                    'width' => $variant->width,
                    'height' => $variant->height,
                    'content_url' => '/api/admin/v1/media/'.$image->public_id.'/content?variant='.$variant->variant_key,
                ])->values()->all() : [],
            ],
            'og_type' => $this->resource->og_type,
            'twitter_card' => $this->resource->twitter_card,
            'twitter_title' => $this->resource->twitter_title,
            'twitter_description' => $this->resource->twitter_description,
            'focus_keywords' => $this->resource->focus_keywords ?? [],
            'updated_at' => $this->resource->updated_at?->utc()->toISOString(),
        ];
    }
}
