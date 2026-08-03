<?php

namespace App\Policies;

use App\Domain\Identity\PermissionService;
use App\Models\ProductAttributeDefinition;
use App\Models\User;

final readonly class ProductAttributePolicy
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

    public function update(User $actor, ProductAttributeDefinition $attribute): bool
    {
        return $this->permissions->allows($actor, 'products.update');
    }

    public function delete(User $actor, ProductAttributeDefinition $attribute): bool
    {
        return $this->permissions->allows($actor, 'products.delete');
    }
}
