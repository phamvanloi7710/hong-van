<?php

namespace App\Policies;

use App\Domain\Identity\PermissionService;
use App\Models\User;

final readonly class UserPolicy
{
    public function __construct(private PermissionService $permissions) {}

    public function viewAny(User $actor): bool
    {
        return $this->permissions->allows($actor, 'users.view');
    }

    public function view(User $actor, User $user): bool
    {
        return $this->viewAny($actor);
    }

    public function create(User $actor): bool
    {
        return $this->permissions->allows($actor, 'users.create');
    }

    public function update(User $actor, User $user): bool
    {
        return $this->permissions->allows($actor, 'users.update');
    }

    public function delete(User $actor, User $user): bool
    {
        return ! $actor->is($user) && $this->permissions->allows($actor, 'users.delete');
    }

    public function manageSessions(User $actor, User $user): bool
    {
        return ! $actor->is($user) && $this->permissions->allows($actor, 'users.update');
    }
}
