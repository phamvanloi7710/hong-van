<?php

namespace App\Http\Resources\Api\V1\Products;

use App\Domain\Products\ProductPriceData;
use App\Domain\Products\ProductPriceResolver;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProductResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $priceView = app(ProductPriceResolver::class)->resolve(ProductPriceData::fromProduct($this->resource), app()->getLocale());

        return [
            'public_id' => $this->resource->public_id,
            'sku' => $this->resource->sku,
            'code' => $this->resource->code,
            'status' => $this->resource->status,
            'category' => $this->whenLoaded('category', fn () => $this->resource->category === null ? null : (new ProductCategoryResource($this->resource->category))->resolve($request)),
            'brand' => $this->whenLoaded('brand', fn () => $this->resource->brand === null ? null : (new BrandResource($this->resource->brand))->resolve($request)),
            'origin' => $this->resource->origin,
            'packaging' => $this->resource->packaging,
            'is_featured' => (bool) $this->resource->is_featured,
            'price' => [
                'mode' => $this->resource->price_mode->value,
                'amount' => $this->resource->price_amount,
                'minimum' => $this->resource->price_min,
                'maximum' => $this->resource->price_max,
                'currency' => $this->resource->currency,
                'unit' => $this->resource->price_unit,
                'note' => $this->resource->price_note,
                'visible' => (bool) $this->resource->is_price_visible,
                'display' => [
                    'mode' => $priceView->mode->value,
                    'label' => $priceView->label,
                    'shows_numeric_price' => $priceView->showsNumericPrice,
                    'requires_quote' => $priceView->requiresQuote,
                ],
            ],
            'translations' => $this->whenLoaded('translations', fn (): array => $this->resource->translations->map(static fn ($translation): array => [
                'locale' => $translation->locale,
                'name' => $translation->name,
                'slug' => $translation->slug,
                'short_description' => $translation->short_description,
                'description' => $translation->description,
                'benefits' => $translation->benefits,
                'usage_instructions' => $translation->usage_instructions,
                'meta_title' => $translation->meta_title,
                'meta_description' => $translation->meta_description,
            ])->values()->all()),
            'media' => $this->whenLoaded('media', fn (): array => $this->resource->media->map(static fn ($media): array => [
                'public_id' => $media->public_id,
                'media_id' => $media->public_id,
                'title' => $media->title ?? $media->original_filename,
                'original_filename' => $media->original_filename,
                'mime_type' => $media->mime_type,
                'width' => $media->width,
                'height' => $media->height,
                'content_url' => '/api/admin/v1/media/'.$media->public_id.'/content',
                'role' => $media->pivot->role,
                'locale' => $media->pivot->locale,
                'is_primary' => (bool) $media->pivot->is_primary,
                'sort_order' => (int) $media->pivot->sort_order,
                'alt_text' => $media->pivot->alt_text,
            ])->values()->all()),
            'tags' => $this->whenLoaded('tags', fn (): array => ProductTagResource::collection($this->resource->tags)->resolve($request)),
            'attributes' => $this->whenLoaded('attributeValues', fn (): array => $this->resource->attributeValues->map(static fn ($value): array => [
                'definition_id' => $value->definition->public_id,
                'definition_name' => $value->definition->name,
                'locale' => $value->locale,
                'value_text' => $value->value_text,
                'value_decimal' => $value->value_decimal,
                'value_boolean' => $value->value_boolean,
                'value_json' => $value->value_json,
            ])->values()->all()),
            'specifications' => $this->whenLoaded('specifications', fn (): array => $this->resource->specifications->map(static fn ($specification): array => [
                'locale' => $specification->locale,
                'label' => $specification->label,
                'value' => $specification->value,
                'unit' => $specification->unit,
                'sort_order' => (int) $specification->sort_order,
            ])->values()->all()),
            'related_products' => $this->whenLoaded('relatedProducts', fn (): array => $this->resource->relatedProducts->map(static fn ($related): array => [
                'public_id' => $related->public_id,
                'sku' => $related->sku,
                'translations' => $related->translations->map(static fn ($translation): array => [
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
