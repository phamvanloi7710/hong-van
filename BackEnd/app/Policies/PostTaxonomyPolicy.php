<?php

namespace App\Policies;

use App\Domain\Identity\PermissionService;
use App\Models\User;

final readonly class PostTaxonomyPolicy
{
    public function __construct(private PermissionService $permissions) {}

    public function viewAny(User $actor): bool
    {
        return $this->permissions->allows($actor, 'posts.view');
    }

    public function view(User $actor): bool
    {
        return $this->viewAny($actor);
    }

    public function create(User $actor): bool
    {
        return $this->permissions->allows($actor, 'posts.create');
    }

    public function update(User $actor): bool
    {
        return $this->permissions->allows($actor, 'posts.update');
    }

    public function delete(User $actor): bool
    {
        return $this->permissions->allows($actor, 'posts.delete');
    }
}
