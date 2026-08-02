<?php

namespace App\Domain\Identity;

use App\Exceptions\ConflictException;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final readonly class RoleManager
{
    public function __construct(private SessionRevoker $sessionRevoker, private IdentityAuditLogger $auditLogger) {}

    /** @param array<string, mixed> $data */
    public function create(User $actor, array $data): Role
    {
        return DB::transaction(function () use ($actor, $data): Role {
            $role = Role::query()->create([
                ...Arr::only($data, ['name', 'slug', 'description']),
                'is_system' => false,
            ]);
            $this->syncPermissions($role, $actor, $data['permission_ids'] ?? []);
            $this->auditLogger->record('identity.role.created', $actor, 'role', $role->public_id, ['slug' => $role->slug]);

            return $this->load($role);
        });
    }

    /** @param array<string, mixed> $data */
    public function update(User $actor, Role $role, array $data): Role
    {
        if ($role->is_system) {
            throw new ConflictException('Vai trò hệ thống không thể chỉnh sửa.');
        }

        return DB::transaction(function () use ($actor, $role, $data): Role {
            $role->fill(Arr::only($data, ['name', 'slug', 'description']))->save();
            if (array_key_exists('permission_ids', $data)) {
                $affectedUsers = $role->users()->get();
                $this->syncPermissions($role, $actor, $data['permission_ids']);
                $affectedUsers->each(fn (User $user) => $this->sessionRevoker->revoke($user));
            }
            $this->auditLogger->record('identity.role.updated', $actor, 'role', $role->public_id, [
                'fields' => implode(',', array_keys($data)),
            ]);

            return $this->load($role);
        });
    }

    public function delete(User $actor, Role $role): void
    {
        if ($role->is_system) {
            throw new ConflictException('Vai trò hệ thống không thể xóa.');
        }
        if ($role->users()->exists()) {
            throw new ConflictException('Không thể xóa vai trò đang được gán cho người dùng.');
        }

        $publicId = $role->public_id;
        $slug = $role->slug;
        $role->delete();
        $this->auditLogger->record('identity.role.deleted', $actor, 'role', $publicId, ['slug' => $slug]);
    }

    /** @param list<string> $permissionPublicIds */
    private function syncPermissions(Role $role, User $actor, array $permissionPublicIds): void
    {
        $ids = Permission::query()->whereIn('public_id', $permissionPublicIds)->pluck('id')->all();
        $role->permissions()->syncWithPivotValues($ids, [
            'granted_by' => $actor->getKey(),
        ]);
    }

    private function load(Role $role): Role
    {
        return $role->fresh(['permissions'])->loadCount('users');
    }
}
