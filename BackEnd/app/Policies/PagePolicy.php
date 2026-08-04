<?php

namespace App\Policies;

use App\Domain\Identity\PermissionService;
use App\Models\Page;
use App\Models\User;

final readonly class PagePolicy
{
    public function __construct(private PermissionService $permissions) {}

    public function viewAny(User $actor): bool
    {
        return $this->permissions->allows($actor, 'pages.view');
    }

    public function view(User $actor, Page $page): bool
    {
        return $this->viewAny($actor);
    }

    public function create(User $actor): bool
    {
        return $this->permissions->allows($actor, 'pages.create');
    }

    public function update(User $actor, Page $page): bool
    {
        return $this->permissions->allows($actor, 'pages.update');
    }

    public function publish(User $actor, Page $page): bool
    {
        return $this->permissions->allows($actor, 'pages.publish');
    }
}
