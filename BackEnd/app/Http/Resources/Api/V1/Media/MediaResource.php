<?php

namespace App\Http\Resources\Api\V1\Media;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class MediaResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->resource->public_id,
            'folder' => $this->whenLoaded('folder', fn () => $this->resource->folder === null ? null : (new MediaFolderResource($this->resource->folder))->resolve($request)),
            'original_filename' => $this->resource->original_filename,
            'normalized_filename' => $this->resource->normalized_filename,
            'extension' => $this->resource->extension,
            'mime_type' => $this->resource->mime_type,
            'size_bytes' => $this->resource->size_bytes,
            'checksum_sha256' => $this->resource->checksum_sha256,
            'width' => $this->resource->width,
            'height' => $this->resource->height,
            'status' => $this->resource->status,
            'visibility' => $this->resource->visibility,
            'is_locked' => (bool) $this->resource->is_locked,
            'title' => $this->resource->title,
            'alt_text' => $this->resource->alt_text,
            'caption' => $this->resource->caption,
            'content_url' => '/api/admin/v1/media/'.$this->resource->public_id.'/content',
            'variants' => $this->whenLoaded('variants', fn (): array => $this->resource->variants->map(fn ($variant): array => [
                'public_id' => $variant->public_id,
                'key' => $variant->variant_key,
                'mime_type' => $variant->mime_type,
                'size_bytes' => $variant->size_bytes,
                'width' => $variant->width,
                'height' => $variant->height,
                'status' => $variant->status,
                'content_url' => '/api/admin/v1/media/'.$this->resource->public_id.'/content?variant='.$variant->variant_key,
            ])->values()->all()),
            'tags' => $this->whenLoaded('tags', fn (): array => $this->resource->tags->map(static fn ($tag): array => [
                'public_id' => $tag->public_id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ])->values()->all()),
            'usage_count' => (int) ($this->resource->usages_count ?? $this->resource->usages?->count() ?? 0),
            'usages' => $this->whenLoaded('usages', fn (): array => $this->resource->usages->map(static fn ($usage): array => [
                'public_id' => $usage->public_id,
                'owner_type' => $usage->owner_type,
                'owner_public_id' => $usage->owner_public_id,
                'field' => $usage->field,
            ])->values()->all()),
            'can_delete' => (int) ($this->resource->usages_count ?? $this->resource->usages?->count() ?? 0) === 0,
            'deleted_at' => $this->resource->deleted_at?->utc()->toISOString(),
            'created_at' => $this->resource->created_at?->utc()->toISOString(),
            'updated_at' => $this->resource->updated_at?->utc()->toISOString(),
        ];
    }
}
