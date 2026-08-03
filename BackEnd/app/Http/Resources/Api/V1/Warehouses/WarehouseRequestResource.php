<?php

namespace App\Http\Resources\Api\V1\Warehouses;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class WarehouseRequestResource extends JsonResource
{
    /** @return array<string,mixed> */
    public function toArray(Request $request): array
    {
        return ['public_id' => $this->resource->public_id, 'status' => $this->resource->status, 'created_at' => $this->resource->created_at?->utc()->toISOString()];
    }
}
