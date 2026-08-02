<?php

namespace App\Http\Resources\Api\V1\Audit;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AuditLogResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->resource->public_id,
            'actor_type' => $this->resource->actor_type,
            'actor_public_id' => $this->resource->actor_public_id,
            'action' => $this->resource->action,
            'subject_type' => $this->resource->subject_type,
            'subject_public_id' => $this->resource->subject_public_id,
            'before' => $this->resource->before_data,
            'after' => $this->resource->after_data,
            'metadata' => $this->resource->metadata,
            'ip_hash' => $this->resource->ip_hash,
            'user_agent_hash' => $this->resource->user_agent_hash,
            'request_id' => $this->resource->request_id,
            'occurred_at' => $this->resource->occurred_at?->utc()->toISOString(),
        ];
    }
}
