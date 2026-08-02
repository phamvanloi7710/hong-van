<?php

namespace App\Policies;

use App\Domain\Identity\PermissionService;
use App\Models\Product;
use App\Models\User;

final readonly class ProductPolicy
{
    public function __construct(private PermissionService $permissions) {}

    public function viewAny(User $actor): bool
    {
        return $this->permissions->allows($actor, 'products.view');
    }

    public function view(User $actor, Product $product): bool
    {
        return $this->viewAny($actor);
    }

    public function create(User $actor): bool
    {
        return $this->permissions->allows($actor, 'products.create');
    }

    public function update(User $actor, Product $product): bool
    {
        return $this->permissions->allows($actor, 'products.update');
    }

    public function delete(User $actor, Product $product): bool
    {
        return $this->permissions->allows($actor, 'products.delete');
    }

    public function restore(User $actor, Product $product): bool
    {
        return $this->permissions->allows($actor, 'products.restore');
    }

    public function publish(User $actor, Product $product): bool
    {
        return $this->permissions->allows($actor, 'products.publish');
    }
}
