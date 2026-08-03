<?php

namespace App\Policies;

use App\Domain\Identity\PermissionService;
use App\Models\Service;
use App\Models\User;

final readonly class ServicePolicy
{
    public function __construct(private PermissionService $permissions) {}

    public function viewAny(User $actor): bool
    {
        return $this->permissions->allows($actor, 'services.view');
    }

    public function view(User $actor, Service $service): bool
    {
        return $this->viewAny($actor);
    }

    public function create(User $actor): bool
    {
        return $this->permissions->allows($actor, 'services.create');
    }

    public function update(User $actor, Service $service): bool
    {
        return $this->permissions->allows($actor, 'services.update');
    }

    public function delete(User $actor, Service $service): bool
    {
        return $this->permissions->allows($actor, 'services.delete');
    }

    public function restore(User $actor, Service $service): bool
    {
        return $this->permissions->allows($actor, 'services.restore');
    }

    public function publish(User $actor, Service $service): bool
    {
        return $this->permissions->allows($actor, 'services.publish');
    }
}
