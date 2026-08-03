<?php

namespace App\Http\Resources\Api\V1\Leads;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PublicLeadResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->resource->lead->public_id,
            'type' => $this->resource->lead->type,
            'status' => $this->resource->lead->status,
            'request_public_id' => $this->resource->request?->public_id,
            'duplicate' => ! $this->resource->created,
            'created_at' => $this->resource->lead->created_at?->utc()->toISOString(),
        ];
    }
}
