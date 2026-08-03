<?php

namespace App\Http\Resources\Api\V1\Posts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PostTaxonomyResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->resource->public_id,
            'parent_id' => $this->resource->relationLoaded('parent') ? $this->resource->parent?->public_id : null,
            'code' => $this->resource->code,
            'is_active' => (bool) $this->resource->is_active,
            'sort_order' => (int) $this->resource->sort_order,
            'translations' => $this->resource->translations->map(static fn ($translation): array => array_filter([
                'locale' => $translation->locale,
                'name' => $translation->name,
                'slug' => $translation->slug,
                'description' => $translation->description ?? null,
                'meta_title' => $translation->meta_title ?? null,
                'meta_description' => $translation->meta_description ?? null,
            ], static fn (mixed $value): bool => $value !== null))->values()->all(),
            'posts_count' => (int) ($this->resource->posts_count ?? 0),
            'updated_at' => $this->resource->updated_at?->utc()->toISOString(),
        ];
    }
}
