<?php

namespace App\Http\Resources\Api\V1\Services;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ServiceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->resource->public_id,
            'category' => $this->whenLoaded('category', fn (): ?array => $this->resource->category === null ? null : [
                'public_id' => $this->resource->category->public_id,
                'translations' => $this->resource->category->translations->map(static fn ($translation): array => [
                    'locale' => $translation->locale,
                    'name' => $translation->name,
                    'slug' => $translation->slug,
                ])->values()->all(),
            ]),
            'code' => $this->resource->code,
            'service_type' => $this->resource->service_type,
            'specialized_module' => match ($this->resource->service_type) {
                'transportation_link' => 'transportation',
                'warehouse_link' => 'warehouses',
                default => null,
            },
            'status' => $this->resource->status,
            'cta_type' => $this->resource->cta_type,
            'cta_source' => [
                'source_type' => 'service',
                'source_public_id' => $this->resource->public_id,
            ],
            'is_featured' => (bool) $this->resource->is_featured,
            'sort_order' => (int) $this->resource->sort_order,
            'translations' => $this->whenLoaded('translations', fn (): array => $this->resource->translations->map(static fn ($translation): array => [
                'locale' => $translation->locale,
                'name' => $translation->name,
                'slug' => $translation->slug,
                'summary' => $translation->summary,
                'content' => $translation->content,
                'content_sections' => $translation->content_sections ?? [],
                'cta_label' => $translation->cta_label,
                'meta_title' => $translation->meta_title,
                'meta_description' => $translation->meta_description,
            ])->values()->all()),
            'media' => $this->whenLoaded('media', fn (): array => $this->resource->media->map(static fn ($media): array => [
                'public_id' => $media->public_id,
                'file_name' => $media->original_filename,
                'mime_type' => $media->mime_type,
                'role' => $media->pivot->role,
                'sort_order' => (int) $media->pivot->sort_order,
            ])->values()->all()),
            'published_at' => $this->resource->published_at?->utc()->toISOString(),
            'unpublished_at' => $this->resource->unpublished_at?->utc()->toISOString(),
            'deleted_at' => $this->resource->deleted_at?->utc()->toISOString(),
            'created_at' => $this->resource->created_at?->utc()->toISOString(),
            'updated_at' => $this->resource->updated_at?->utc()->toISOString(),
        ];
    }
}
