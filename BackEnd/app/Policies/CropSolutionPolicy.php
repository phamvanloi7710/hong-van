<?php

namespace App\Policies;

use App\Domain\Identity\PermissionService;
use App\Models\CropSolution;
use App\Models\User;

final readonly class CropSolutionPolicy
{
    public function __construct(private PermissionService $permissions) {}

    public function viewAny(User $actor): bool
    {
        return $this->permissions->allows($actor, 'crop_solutions.view');
    }

    public function view(User $actor, CropSolution $solution): bool
    {
        return $this->viewAny($actor);
    }

    public function create(User $actor): bool
    {
        return $this->permissions->allows($actor, 'crop_solutions.create');
    }

    public function update(User $actor, CropSolution $solution): bool
    {
        return $this->permissions->allows($actor, 'crop_solutions.update');
    }

    public function delete(User $actor, CropSolution $solution): bool
    {
        return $this->permissions->allows($actor, 'crop_solutions.delete');
    }

    public function publish(User $actor, CropSolution $solution): bool
    {
        return $this->permissions->allows($actor, 'crop_solutions.publish');
    }
}
