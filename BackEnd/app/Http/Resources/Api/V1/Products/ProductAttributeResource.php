<?php

namespace App\Http\Resources\Api\V1\Products;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProductAttributeResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->resource->public_id,
            'code' => $this->resource->code,
            'name' => $this->resource->name,
            'data_type' => $this->resource->data_type,
            'unit' => $this->resource->unit,
            'options' => $this->resource->options,
            'is_filterable' => (bool) $this->resource->is_filterable,
            'is_required' => (bool) $this->resource->is_required,
            'sort_order' => (int) $this->resource->sort_order,
            'values_count' => $this->whenCounted('values'),
            'created_at' => $this->resource->created_at?->utc()->toISOString(),
            'updated_at' => $this->resource->updated_at?->utc()->toISOString(),
        ];
    }
}
