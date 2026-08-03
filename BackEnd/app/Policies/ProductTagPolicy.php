<?php

namespace App\Policies;

use App\Domain\Identity\PermissionService;
use App\Models\ProductTag;
use App\Models\User;

final readonly class ProductTagPolicy
{
    public function __construct(private PermissionService $permissions) {}

    public function viewAny(User $actor): bool
    {
        return $this->permissions->allows($actor, 'products.view');
    }

    public function create(User $actor): bool
    {
        return $this->permissions->allows($actor, 'products.create');
    }

    public function update(User $actor, ProductTag $tag): bool
    {
        return $this->permissions->allows($actor, 'products.update');
    }

    public function delete(User $actor, ProductTag $tag): bool
    {
        return $this->permissions->allows($actor, 'products.delete');
    }
}
