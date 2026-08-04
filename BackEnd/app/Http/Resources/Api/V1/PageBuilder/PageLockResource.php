<?php

namespace App\Http\Resources\Api\V1\PageBuilder;

use App\Domain\PageBuilder\PageLockManager;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PageLockResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->resource->public_id,
            'owner' => $this->resource->relationLoaded('user') && $this->resource->user !== null
                ? ['public_id' => $this->resource->user->public_id, 'name' => $this->resource->user->name] : null,
            'expires_at' => $this->resource->expires_at?->utc()->toISOString(),
            'ttl_seconds' => PageLockManager::ttlSeconds(),
        ];
    }
}
