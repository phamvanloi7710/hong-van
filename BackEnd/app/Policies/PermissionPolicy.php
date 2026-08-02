<?php

namespace App\Policies;

use App\Domain\Identity\PermissionService;
use App\Models\Permission;
use App\Models\User;

final readonly class PermissionPolicy
{
    public function __construct(private PermissionService $permissions) {}

    public function viewAny(User $actor): bool
    {
        return $this->permissions->allows($actor, 'permissions.view');
    }

    public function view(User $actor, Permission $permission): bool
    {
        return $this->viewAny($actor);
    }

    public function create(User $actor): bool
    {
        return $this->permissions->allows($actor, 'permissions.create');
    }

    public function manageOverrides(User $actor): bool
    {
        return $this->permissions->allows($actor, 'permissions.update');
    }

    public function update(User $actor, Permission $permission): bool
    {
        return $this->permissions->allows($actor, 'permissions.update');
    }

    public function delete(User $actor, Permission $permission): bool
    {
        return ! $permission->is_system && $this->permissions->allows($actor, 'permissions.delete');
    }
}
