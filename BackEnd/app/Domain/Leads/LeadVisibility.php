<?php

namespace App\Domain\Leads;

use App\Domain\Identity\PermissionService;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final readonly class LeadVisibility
{
    public function __construct(private PermissionService $permissions) {}

    /** @return Builder<Lead> */
    public function queryFor(User $actor): Builder
    {
        $query = Lead::query();

        if (! $this->canViewAll($actor)) {
            $query->where('assigned_to', $actor->getKey());
        }

        return $query;
    }

    public function canView(User $actor, Lead $lead): bool
    {
        return $this->canViewAll($actor)
            || $lead->assigned_to === $actor->getKey();
    }

    public function canViewAll(User $actor): bool
    {
        return $this->permissions->isSuperAdmin($actor)
            || $this->permissions->allows($actor, 'leads.view_all');
    }
}
