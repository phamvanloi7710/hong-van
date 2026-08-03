<?php

namespace App\Http\Resources\Api\V1\CropSolutions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CropStageResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->resource->public_id,
            'crop_id' => $this->resource->crop->public_id,
            'code' => $this->resource->code,
            'image_media_id' => $this->resource->image?->public_id,
            'is_active' => (bool) $this->resource->is_active,
            'sort_order' => (int) $this->resource->sort_order,
            'translations' => $this->whenLoaded('translations', fn (): array => $this->resource->translations->map(static fn ($translation): array => [
                'locale' => $translation->locale,
                'name' => $translation->name,
                'summary' => $translation->summary,
                'content' => $translation->content,
            ])->values()->all()),
            'solutions_count' => $this->whenCounted('solutions'),
            'deleted_at' => $this->resource->deleted_at?->utc()->toISOString(),
            'created_at' => $this->resource->created_at?->utc()->toISOString(),
            'updated_at' => $this->resource->updated_at?->utc()->toISOString(),
        ];
    }
}
