<?php

namespace App\Http\Resources\Api\V1\PageBuilder;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PageTemplateResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->resource->public_id, 'key' => $this->resource->key,
            'name' => $this->resource->name, 'description' => $this->resource->description,
            'category' => $this->resource->relationLoaded('category') && $this->resource->category !== null
                ? ['public_id' => $this->resource->category->public_id, 'key' => $this->resource->category->key, 'name' => $this->resource->category->name] : null,
            'version' => $this->resource->relationLoaded('publishedVersion') && $this->resource->publishedVersion !== null
                ? ['public_id' => $this->resource->publishedVersion->public_id, 'version_number' => $this->resource->publishedVersion->version_number, 'checksum' => $this->resource->publishedVersion->checksum] : null,
        ];
    }
}
