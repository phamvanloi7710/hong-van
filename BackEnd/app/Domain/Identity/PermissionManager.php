<?php

namespace App\Domain\Identity;

use App\Exceptions\ConflictException;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final readonly class PermissionManager
{
    public function __construct(private SessionRevoker $sessionRevoker, private IdentityAuditLogger $auditLogger) {}

    /** @param array<string, mixed> $data */
    public function create(User $actor, array $data): Permission
    {
        $key = $data['module'].'.'.$data['action'];
        if (Permission::query()->where('key', $key)->exists()) {
            throw new ConflictException('Permission key đã tồn tại.');
        }

        $permission = Permission::query()->create([
            ...Arr::only($data, ['module', 'action', 'name', 'description']),
            'key' => $key,
            'is_system' => false,
        ]);
        $this->auditLogger->record('identity.permission.created', $actor, 'permission', $permission->public_id, ['key' => $key]);

        return $permission->loadCount('roles');
    }

    /** @param array<string, mixed> $data */
    public function update(User $actor, Permission $permission, array $data): Permission
    {
        if ($permission->is_system) {
            throw new ConflictException('Quyền hệ thống không thể chỉnh sửa.');
        }

        return DB::transaction(function () use ($actor, $permission, $data): Permission {
            $module = $data['module'] ?? $permission->module;
            $action = $data['action'] ?? $permission->action;
            $key = $module.'.'.$action;

            if (Permission::query()->where('key', $key)->whereKeyNot($permission->getKey())->exists()) {
                throw new ConflictException('Permission key đã tồn tại.');
            }

            $users = User::query()
                ->whereHas('roles.permissions', fn ($query) => $query->whereKey($permission->getKey()))
                ->orWhereHas('permissionOverrides', fn ($query) => $query->whereKey($permission->getKey()))
                ->get();

            $permission->fill([
                ...Arr::only($data, ['name', 'description']),
                'module' => $module,
                'action' => $action,
                'key' => $key,
            ])->save();
            $users->each(fn (User $user) => $this->sessionRevoker->revoke($user));
            $this->auditLogger->record('identity.permission.updated', $actor, 'permission', $permission->public_id, ['key' => $key]);

            return $permission->loadCount('roles');
        });
    }

    public function delete(User $actor, Permission $permission): void
    {
        if ($permission->is_system) {
            throw new ConflictException('Quyền hệ thống không thể xóa.');
        }

        DB::transaction(function () use ($actor, $permission): void {
            $users = User::query()
                ->whereHas('roles.permissions', fn ($query) => $query->whereKey($permission->getKey()))
                ->orWhereHas('permissionOverrides', fn ($query) => $query->whereKey($permission->getKey()))
                ->get();
            $publicId = $permission->public_id;
            $key = $permission->key;
            $permission->delete();
            $users->each(fn (User $user) => $this->sessionRevoker->revoke($user));
            $this->auditLogger->record('identity.permission.deleted', $actor, 'permission', $publicId, ['key' => $key]);
        });
    }
}
