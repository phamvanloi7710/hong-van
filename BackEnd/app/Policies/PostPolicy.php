<?php

namespace App\Policies;

use App\Domain\Identity\PermissionService;
use App\Models\Post;
use App\Models\User;

final readonly class PostPolicy
{
    public function __construct(private PermissionService $permissions) {}

    public function viewAny(User $actor): bool
    {
        return $this->permissions->allows($actor, 'posts.view');
    }

    public function view(User $actor, Post $post): bool
    {
        return $this->viewAny($actor);
    }

    public function create(User $actor): bool
    {
        return $this->permissions->allows($actor, 'posts.create');
    }

    public function update(User $actor, Post $post): bool
    {
        return $this->permissions->allows($actor, 'posts.update');
    }

    public function delete(User $actor, Post $post): bool
    {
        return $this->permissions->allows($actor, 'posts.delete');
    }

    public function restore(User $actor, Post $post): bool
    {
        return $this->permissions->allows($actor, 'posts.restore');
    }

    public function publish(User $actor, Post $post): bool
    {
        return $this->permissions->allows($actor, 'posts.publish');
    }
}
