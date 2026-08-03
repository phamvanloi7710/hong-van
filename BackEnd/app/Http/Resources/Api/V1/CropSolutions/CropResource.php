<?php

namespace App\Http\Resources\Api\V1\CropSolutions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CropResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->resource->public_id,
            'category_id' => $this->resource->category?->public_id,
            'code' => $this->resource->code,
            'image_media_id' => $this->resource->image?->public_id,
            'is_active' => (bool) $this->resource->is_active,
            'sort_order' => (int) $this->resource->sort_order,
            'translations' => $this->whenLoaded('translations', fn (): array => $this->resource->translations->map(static fn ($translation): array => [
                'locale' => $translation->locale,
                'name' => $translation->name,
                'slug' => $translation->slug,
                'summary' => $translation->summary,
                'description' => $translation->description,
                'meta_title' => $translation->meta_title,
                'meta_description' => $translation->meta_description,
            ])->values()->all()),
            'stages' => $this->whenLoaded('stages', fn (): array => CropStageResource::collection($this->resource->stages)->resolve($request)),
            'stages_count' => $this->whenCounted('stages'),
            'solutions_count' => $this->whenCounted('solutions'),
            'deleted_at' => $this->resource->deleted_at?->utc()->toISOString(),
            'created_at' => $this->resource->created_at?->utc()->toISOString(),
            'updated_at' => $this->resource->updated_at?->utc()->toISOString(),
        ];
    }
}
