<?php

namespace App\Http\Resources\Api\V1\PageBuilder;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PageVersionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return ['public_id' => $this->resource->public_id, 'version_number' => $this->resource->version_number,
            'status' => $this->resource->status, 'schema_version' => $this->resource->schema_version,
            'checksum' => $this->resource->checksum, 'note' => $this->resource->note,
            'author' => $this->whenLoaded('creator', fn (): ?array => $this->resource->creator ? ['public_id' => $this->resource->creator->public_id, 'name' => $this->resource->creator->name] : null),
            'document' => $this->resource->document_json, 'published_at' => $this->resource->published_at?->utc()->toISOString(),
            'created_at' => $this->resource->created_at?->utc()->toISOString()];
    }
}
