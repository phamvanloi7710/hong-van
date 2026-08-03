<?php

namespace App\Http\Resources\Api\V1\CropSolutions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CropSolutionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->resource->public_id,
            'crop' => $this->whenLoaded('crop', fn (): array => [
                'public_id' => $this->resource->crop->public_id,
                'translations' => $this->resource->crop->translations->map(static fn ($translation): array => [
                    'locale' => $translation->locale,
                    'name' => $translation->name,
                    'slug' => $translation->slug,
                ])->values()->all(),
            ]),
            'stage' => $this->whenLoaded('stage', fn (): ?array => $this->resource->stage === null ? null : [
                'public_id' => $this->resource->stage->public_id,
                'translations' => $this->resource->stage->translations->map(static fn ($translation): array => [
                    'locale' => $translation->locale,
                    'name' => $translation->name,
                ])->values()->all(),
            ]),
            'code' => $this->resource->code,
            'status' => $this->resource->status,
            'hero_media_id' => $this->resource->heroMedia?->public_id,
            'is_featured' => (bool) $this->resource->is_featured,
            'sort_order' => (int) $this->resource->sort_order,
            'translations' => $this->whenLoaded('translations', fn (): array => $this->resource->translations->map(static fn ($translation): array => [
                'locale' => $translation->locale,
                'title' => $translation->title,
                'slug' => $translation->slug,
                'summary' => $translation->summary,
                'content' => $translation->content,
                'content_sections' => $translation->content_sections ?? [],
                'meta_title' => $translation->meta_title,
                'meta_description' => $translation->meta_description,
            ])->values()->all()),
            'products' => $this->whenLoaded('products', fn (): array => $this->resource->products->map(static fn ($product): array => [
                'public_id' => $product->public_id,
                'sku' => $product->sku,
                'status' => $product->status,
                'deleted_at' => $product->deleted_at?->utc()->toISOString(),
                'sort_order' => (int) $product->pivot->sort_order,
                'recommendation_note' => $product->pivot->recommendation_note,
                'translations' => $product->translations->map(static fn ($translation): array => [
                    'locale' => $translation->locale,
                    'name' => $translation->name,
                    'slug' => $translation->slug,
                ])->values()->all(),
            ])->values()->all()),
            'published_at' => $this->resource->published_at?->utc()->toISOString(),
            'unpublished_at' => $this->resource->unpublished_at?->utc()->toISOString(),
            'deleted_at' => $this->resource->deleted_at?->utc()->toISOString(),
            'created_at' => $this->resource->created_at?->utc()->toISOString(),
            'updated_at' => $this->resource->updated_at?->utc()->toISOString(),
        ];
    }
}
