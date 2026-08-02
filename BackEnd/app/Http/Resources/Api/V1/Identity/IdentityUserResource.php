<?php

namespace App\Http\Resources\Api\V1\Identity;

use App\Domain\Identity\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class IdentityUserResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->resource->public_id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'email_verified_at' => $this->resource->email_verified_at?->toAtomString(),
            'is_active' => $this->resource->is_active,
            'locked_at' => $this->resource->locked_at?->toAtomString(),
            'roles' => RoleResource::collection($this->whenLoaded('roles'))->resolve($request),
            'permission_overrides' => $this->whenLoaded('permissionOverrides', fn (): array => $this->resource->permissionOverrides->map(static fn ($permission): array => [
                'permission_id' => $permission->public_id,
                'key' => $permission->key,
                'is_allowed' => (bool) $permission->pivot->is_allowed,
            ])->values()->all()),
            'permissions' => app(PermissionService::class)->effectivePermissionKeys($this->resource),
            'created_at' => $this->resource->created_at?->toAtomString(),
            'updated_at' => $this->resource->updated_at?->toAtomString(),
        ];
    }
}
