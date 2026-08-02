<?php

namespace App\Http\Resources\Api\V1\Media;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class MediaFolderResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->resource->public_id,
            'parent_id' => $this->resource->parent?->public_id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'sort_order' => $this->resource->sort_order,
            'is_locked' => (bool) $this->resource->is_locked,
            'media_count' => (int) ($this->resource->media_count ?? 0),
            'children_count' => (int) ($this->resource->children_count ?? 0),
        ];
    }
}
