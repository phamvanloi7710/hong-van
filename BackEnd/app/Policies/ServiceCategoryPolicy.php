<?php

namespace App\Policies;

use App\Domain\Identity\PermissionService;
use App\Models\ServiceCategory;
use App\Models\User;

final readonly class ServiceCategoryPolicy
{
    public function __construct(private PermissionService $permissions) {}

    public function viewAny(User $actor): bool
    {
        return $this->permissions->allows($actor, 'services.view');
    }

    public function create(User $actor): bool
    {
        return $this->permissions->allows($actor, 'services.create');
    }

    public function update(User $actor, ServiceCategory $category): bool
    {
        return $this->permissions->allows($actor, 'services.update');
    }

    public function delete(User $actor, ServiceCategory $category): bool
    {
        return $this->permissions->allows($actor, 'services.delete');
    }
}
