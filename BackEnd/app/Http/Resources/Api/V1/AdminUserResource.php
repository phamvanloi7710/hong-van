<?php

namespace App\Http\Resources\Api\V1;

use App\Domain\Identity\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AdminUserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $permissions = app(PermissionService::class);

        return [
            'public_id' => $this->resource->public_id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'email_verified_at' => $this->resource->email_verified_at?->toAtomString(),
            'is_active' => $this->resource->is_active,
            'locked_at' => $this->resource->locked_at?->toAtomString(),
            'roles' => $this->resource->roles()->orderBy('slug')->pluck('slug')->all(),
            'permissions' => $permissions->effectivePermissionKeys($this->resource),
        ];
    }
}
