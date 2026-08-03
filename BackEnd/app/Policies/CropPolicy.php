<?php

namespace App\Policies;

use App\Domain\Identity\PermissionService;
use App\Models\Crop;
use App\Models\User;

final readonly class CropPolicy
{
    public function __construct(private PermissionService $permissions) {}

    public function viewAny(User $actor): bool
    {
        return $this->permissions->allows($actor, 'crops.view');
    }

    public function create(User $actor): bool
    {
        return $this->permissions->allows($actor, 'crops.create');
    }

    public function update(User $actor, Crop $crop): bool
    {
        return $this->permissions->allows($actor, 'crops.update');
    }

    public function delete(User $actor, Crop $crop): bool
    {
        return $this->permissions->allows($actor, 'crops.delete');
    }
}
