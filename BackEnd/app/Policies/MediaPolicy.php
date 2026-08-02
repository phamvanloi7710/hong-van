<?php

namespace App\Policies;

use App\Domain\Identity\PermissionService;
use App\Models\Media;
use App\Models\User;

final readonly class MediaPolicy
{
    public function __construct(private PermissionService $permissions) {}

    public function viewAny(User $actor): bool
    {
        return $this->permissions->allows($actor, 'media.view');
    }

    public function view(User $actor, Media $media): bool
    {
        return $this->viewAny($actor);
    }

    public function create(User $actor): bool
    {
        return $this->permissions->allows($actor, 'media.create');
    }

    public function update(User $actor, Media $media): bool
    {
        return $this->permissions->allows($actor, 'media.update');
    }

    public function delete(User $actor, Media $media): bool
    {
        return $this->permissions->allows($actor, 'media.delete');
    }

    public function restore(User $actor, Media $media): bool
    {
        return $this->permissions->allows($actor, 'media.restore');
    }
}
