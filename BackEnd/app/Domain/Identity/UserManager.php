<?php

namespace App\Domain\Identity;

use App\Exceptions\ConflictException;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class UserManager
{
    public function __construct(
        private PermissionService $permissions,
        private SessionRevoker $sessionRevoker,
        private IdentityAuditLogger $auditLogger,
    ) {}

    /** @param array<string, mixed> $data */
    public function create(User $actor, array $data): User
    {
        return DB::transaction(function () use ($actor, $data): User {
            $user = new User;
            $user->forceFill([
                'name' => $data['name'],
                'email' => Str::lower((string) $data['email']),
                'password' => $data['password'],
                'is_active' => (bool) ($data['is_active'] ?? true),
            ])->save();

            $this->syncRoles($user, $actor, $data['role_ids'] ?? []);
            $this->syncOverrides($user, $actor, $data['permission_overrides'] ?? []);
            $this->auditLogger->record('identity.user.created', $actor, 'user', $user->public_id);

            return $this->load($user);
        });
    }

    /** @param array<string, mixed> $data */
    public function update(User $actor, User $user, array $data): User
    {
        if ($actor->is($user) && (array_key_exists('role_ids', $data) || array_key_exists('permission_overrides', $data))) {
            throw new ConflictException(__('api.identity_self_authorization_update_forbidden'));
        }

        return DB::transaction(function () use ($actor, $user, $data): User {
            $superAdminRole = array_key_exists('role_ids', $data) ? $this->lockSuperAdminRole() : null;
            $user = $this->lockUser($user);
            if ($superAdminRole !== null && $this->wouldRemoveLastSuperAdmin($user, $data['role_ids'], $superAdminRole)) {
                throw new ConflictException(__('api.identity_active_super_admin_required'));
            }

            $fields = Arr::only($data, ['name', 'email', 'password']);
            if (isset($fields['email'])) {
                $fields['email'] = Str::lower((string) $fields['email']);
            }
            $user->forceFill($fields)->save();

            $authorizationChanged = false;
            if (array_key_exists('role_ids', $data)) {
                $this->syncRoles($user, $actor, $data['role_ids']);
                $authorizationChanged = true;
            }
            if (array_key_exists('permission_overrides', $data)) {
                $this->syncOverrides($user, $actor, $data['permission_overrides']);
                $authorizationChanged = true;
            }
            if ($authorizationChanged || array_key_exists('password', $data)) {
                $this->sessionRevoker->revoke($user);
            }

            $this->auditLogger->record('identity.user.updated', $actor, 'user', $user->public_id, [
                'fields' => implode(',', array_keys($data)),
            ]);

            return $this->load($user);
        });
    }

    public function delete(User $actor, User $user): void
    {
        if ($actor->is($user)) {
            throw new ConflictException(__('api.identity_self_delete_forbidden'));
        }

        DB::transaction(function () use ($actor, $user): void {
            $superAdminRole = $this->lockSuperAdminRole();
            $user = $this->lockUser($user);
            if ($this->isCountedSuperAdmin($user, $superAdminRole) && $this->permissions->superAdminCount() <= 1) {
                throw new ConflictException(__('api.identity_active_super_admin_required'));
            }

            $publicId = $user->public_id;
            $this->sessionRevoker->revoke($user);
            $user->delete();
            $this->auditLogger->record('identity.user.deleted', $actor, 'user', $publicId);
        });
    }

    public function activate(User $actor, User $user): User
    {
        $user = DB::transaction(function () use ($actor, $user): User {
            $user = $this->lockUser($user);
            $user->forceFill(['is_active' => true, 'locked_at' => null])->save();
            $this->auditLogger->record('identity.user.activated', $actor, 'user', $user->public_id);

            return $user;
        });

        return $this->load($user);
    }

    public function lock(User $actor, User $user): User
    {
        if ($actor->is($user)) {
            throw new ConflictException(__('api.identity_self_lock_forbidden'));
        }

        DB::transaction(function () use ($actor, $user): void {
            $superAdminRole = $this->lockSuperAdminRole();
            $user = $this->lockUser($user);
            if ($this->isCountedSuperAdmin($user, $superAdminRole) && $this->permissions->superAdminCount() <= 1) {
                throw new ConflictException(__('api.identity_active_super_admin_required'));
            }

            $user->forceFill(['is_active' => false, 'locked_at' => now()])->save();
            $this->sessionRevoker->revoke($user);
            $this->auditLogger->record('identity.user.locked', $actor, 'user', $user->public_id);
        });

        return $this->load($user);
    }

    public function resetSessions(User $actor, User $user): void
    {
        DB::transaction(function () use ($actor, $user): void {
            $user = $this->lockUser($user);
            $this->sessionRevoker->revoke($user);
            $this->auditLogger->record('identity.user.sessions_reset', $actor, 'user', $user->public_id);
        });
    }

    /** @param list<string> $rolePublicIds */
    private function syncRoles(User $user, User $actor, array $rolePublicIds): void
    {
        $ids = Role::query()->whereIn('public_id', $rolePublicIds)->pluck('id')->all();
        $user->roles()->syncWithPivotValues($ids, [
            'assigned_by' => $actor->getKey(),
        ]);
    }

    /** @param list<array{permission_id: string, is_allowed: bool}> $overrides */
    private function syncOverrides(User $user, User $actor, array $overrides): void
    {
        $publicIds = array_column($overrides, 'permission_id');
        $permissionIds = Permission::query()->whereIn('public_id', $publicIds)->pluck('id', 'public_id');
        $sync = [];

        foreach ($overrides as $override) {
            $id = $permissionIds->get($override['permission_id']);
            if ($id !== null) {
                $sync[$id] = [
                    'is_allowed' => $override['is_allowed'],
                    'assigned_by' => $actor->getKey(),
                ];
            }
        }

        $user->permissionOverrides()->sync($sync);
    }

    /** @param list<string> $rolePublicIds */
    private function wouldRemoveLastSuperAdmin(User $user, array $rolePublicIds, Role $superAdminRole): bool
    {
        if (! $this->isCountedSuperAdmin($user, $superAdminRole) || $this->permissions->superAdminCount() > 1) {
            return false;
        }

        return ! in_array($superAdminRole->public_id, $rolePublicIds, true);
    }

    private function load(User $user): User
    {
        return $user->fresh(['roles.permissions', 'permissionOverrides']) ?? $user;
    }

    private function isCountedSuperAdmin(User $user, Role $superAdminRole): bool
    {
        return $user->is_active
            && $user->locked_at === null
            && $user->roles()->where('hongvan_roles.id', $superAdminRole->getKey())->exists();
    }

    private function lockSuperAdminRole(): Role
    {
        return Role::query()
            ->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function lockUser(User $user): User
    {
        return User::query()->lockForUpdate()->findOrFail($user->getKey());
    }
}
