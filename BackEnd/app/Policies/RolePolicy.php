<?php

namespace App\Policies;

use App\Domain\Identity\PermissionService;
use App\Models\Role;
use App\Models\User;

final readonly class RolePolicy
{
    public function __construct(private PermissionService $permissions) {}

    public function viewAny(User $actor): bool
    {
        return $this->permissions->allows($actor, 'roles.view');
    }

    public function view(User $actor, Role $role): bool
    {
        return $this->viewAny($actor);
    }

    public function create(User $actor): bool
    {
        return $this->permissions->allows($actor, 'roles.create');
    }

    public function manageAssignments(User $actor): bool
    {
        return $this->permissions->allows($actor, 'roles.update');
    }

    public function update(User $actor, Role $role): bool
    {
        return $this->permissions->allows($actor, 'roles.update');
    }

    public function delete(User $actor, Role $role): bool
    {
        return ! $role->is_system && $this->permissions->allows($actor, 'roles.delete');
    }
}
