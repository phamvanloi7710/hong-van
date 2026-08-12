<?php

namespace App\Http\Resources\Api\V1\Identity;

use App\Domain\Identity\PermissionRegistry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PermissionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $labels = PermissionRegistry::labels($this->resource->key) ?? [
            'vi' => $this->resource->name,
            'en' => $this->resource->name,
            'zh' => $this->resource->name,
        ];

        return [
            'public_id' => $this->resource->public_id,
            'key' => $this->resource->key,
            'module' => $this->resource->module,
            'action' => $this->resource->action,
            'name' => $labels[app()->getLocale()] ?? $this->resource->name,
            'labels' => $labels,
            'description' => $this->resource->description,
            'is_system' => $this->resource->is_system,
            'roles_count' => $this->whenCounted('roles'),
            'created_at' => $this->resource->created_at?->toAtomString(),
            'updated_at' => $this->resource->updated_at?->toAtomString(),
        ];
    }
}
