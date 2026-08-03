<?php

namespace App\Policies;

use App\Domain\Identity\PermissionService;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

final readonly class TransportationPolicy
{
    public function __construct(private PermissionService $permissions) {}

    public function viewAny(User $actor): bool
    {
        return $this->permissions->allows($actor, 'transportation.view');
    }

    public function view(User $actor, Model $model): bool
    {
        return $this->viewAny($actor);
    }

    public function create(User $actor): bool
    {
        return $this->permissions->allows($actor, 'transportation.create');
    }

    public function update(User $actor, Model $model): bool
    {
        return $this->permissions->allows($actor, 'transportation.update');
    }

    public function delete(User $actor, Model $model): bool
    {
        return $this->permissions->allows($actor, 'transportation.delete');
    }

    public function publish(User $actor, Model $model): bool
    {
        return $this->permissions->allows($actor, 'transportation.publish');
    }
}
