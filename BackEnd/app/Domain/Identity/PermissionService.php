<?php

namespace App\Domain\Identity;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;

final readonly class PermissionService
{
    public function __construct(private IdentityAuditLogger $auditLogger) {}

    public function allows(User $user, string $permission, ?Request $request = null): bool
    {
        if (! $user->is_active
            || $user->locked_at !== null
            || ! PermissionRegistry::isValidKey($permission)
            || ! Permission::query()->where('key', $permission)->exists()) {
            return false;
        }

        if ($this->isSuperAdmin($user)) {
            $this->auditLogger->superAdminBypass($user, $permission, $request);

            return true;
        }

        $override = $user->permissionOverrides()
            ->where('hongvan_permissions.key', $permission)
            ->first();

        if ($override !== null) {
            return (bool) $override->getRelation('pivot')->getAttribute('is_allowed');
        }

        return $user->roles()
            ->whereHas('permissions', static fn ($query) => $query->where('hongvan_permissions.key', $permission))
            ->exists();
    }

    /**
     * @return list<string>
     */
    public function effectivePermissionKeys(User $user): array
    {
        if (! $user->is_active || $user->locked_at !== null) {
            return [];
        }

        if ($this->isSuperAdmin($user)) {
            return Permission::query()
                ->orderBy('key')
                ->pluck('key')
                ->sort()
                ->values()
                ->all();
        }

        $roleKeys = $user->roles()
            ->with('permissions:id,key')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('key');

        $overrides = $user->permissionOverrides()
            ->get(['hongvan_permissions.id', 'hongvan_permissions.key'])
            ->mapWithKeys(static fn ($permission): array => [
                $permission->key => (bool) $permission->getRelation('pivot')->getAttribute('is_allowed'),
            ]);

        return $roleKeys
            ->reject(static fn (string $key): bool => $overrides->get($key) === false)
            ->merge($overrides->filter()->keys())
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function isSuperAdmin(User $user): bool
    {
        return $user->roles()
            ->where('hongvan_roles.slug', PermissionRegistry::SUPER_ADMIN_ROLE)
            ->exists();
    }

    public function superAdminCount(): int
    {
        return User::query()
            ->where('is_active', true)
            ->whereNull('locked_at')
            ->whereHas('roles', static fn ($query) => $query->where('hongvan_roles.slug', PermissionRegistry::SUPER_ADMIN_ROLE))
            ->count();
    }
}
