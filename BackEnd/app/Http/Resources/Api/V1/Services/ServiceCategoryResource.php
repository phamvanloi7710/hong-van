<?php

namespace App\Http\Resources\Api\V1\Services;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ServiceCategoryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->resource->public_id,
            'parent_id' => $this->resource->parent?->public_id,
            'code' => $this->resource->code,
            'is_active' => (bool) $this->resource->is_active,
            'sort_order' => (int) $this->resource->sort_order,
            'translations' => $this->whenLoaded('translations', fn (): array => $this->resource->translations->map(static fn ($translation): array => [
                'locale' => $translation->locale,
                'name' => $translation->name,
                'slug' => $translation->slug,
                'summary' => $translation->summary,
                'meta_title' => $translation->meta_title,
                'meta_description' => $translation->meta_description,
            ])->values()->all()),
            'services_count' => (int) ($this->resource->services_count ?? 0),
            'deleted_at' => $this->resource->deleted_at?->utc()->toISOString(),
            'updated_at' => $this->resource->updated_at?->utc()->toISOString(),
        ];
    }
}
