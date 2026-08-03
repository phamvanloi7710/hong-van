<?php

namespace App\Policies;

use App\Domain\Identity\PermissionService;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

final readonly class ShowcasePolicy
{
    public function __construct(private PermissionService $permissions) {}

    public function viewAny(User $actor): bool
    {
        return $this->permissions->allows($actor, 'showcase.view');
    }

    public function view(User $actor, Model $model): bool
    {
        return $this->viewAny($actor);
    }

    public function create(User $actor): bool
    {
        return $this->permissions->allows($actor, 'showcase.create');
    }

    public function update(User $actor, Model $model): bool
    {
        return $this->permissions->allows($actor, 'showcase.update');
    }

    public function delete(User $actor, Model $model): bool
    {
        return $this->permissions->allows($actor, 'showcase.delete');
    }

    public function restore(User $actor, Model $model): bool
    {
        return $this->permissions->allows($actor, 'showcase.restore');
    }

    public function publish(User $actor, Model $model): bool
    {
        return $this->permissions->allows($actor, 'showcase.publish');
    }
}
