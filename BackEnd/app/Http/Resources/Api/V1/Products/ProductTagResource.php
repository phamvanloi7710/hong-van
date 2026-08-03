<?php

namespace App\Http\Resources\Api\V1\Products;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProductTagResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->resource->public_id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'products_count' => $this->whenCounted('products'),
            'created_at' => $this->resource->created_at?->utc()->toISOString(),
            'updated_at' => $this->resource->updated_at?->utc()->toISOString(),
        ];
    }
}
