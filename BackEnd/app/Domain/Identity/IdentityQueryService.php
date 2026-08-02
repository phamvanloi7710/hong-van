<?php

namespace App\Domain\Identity;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Query\AllowedFilter;
use App\Support\Query\AllowedSort;
use App\Support\Query\FilterOperator;
use App\Support\Query\FilterValueType;
use App\Support\Query\QueryAllowlist;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class IdentityQueryService
{
    /**
     * @param  array<string, mixed>  $query
     * @return LengthAwarePaginator<int, User>
     */
    public function users(array $query): LengthAwarePaginator
    {
        $allowlist = new QueryAllowlist(
            filters: [
                new AllowedFilter('name', 'name', operator: FilterOperator::Contains),
                new AllowedFilter('email', 'email', operator: FilterOperator::Contains),
                new AllowedFilter('is_active', 'is_active', FilterValueType::Boolean),
            ],
            sorts: [
                new AllowedSort('name', 'name'),
                new AllowedSort('email', 'email'),
                new AllowedSort('created_at', 'created_at'),
            ],
        );

        $builder = $allowlist->resolve($query['filter'] ?? null, $query['sort'] ?? null)
            ->apply(User::query()->with(['roles.permissions', 'permissionOverrides']));

        if (! isset($query['sort'])) {
            $builder->orderBy('name');
        }

        return $builder->paginate((int) ($query['per_page'] ?? 20));
    }

    /**
     * @param  array<string, mixed>  $query
     * @return LengthAwarePaginator<int, Role>
     */
    public function roles(array $query): LengthAwarePaginator
    {
        $allowlist = new QueryAllowlist(
            filters: [
                new AllowedFilter('name', 'name', operator: FilterOperator::Contains),
                new AllowedFilter('slug', 'slug', operator: FilterOperator::Contains),
                new AllowedFilter('is_system', 'is_system', FilterValueType::Boolean),
            ],
            sorts: [
                new AllowedSort('name', 'name'),
                new AllowedSort('slug', 'slug'),
                new AllowedSort('created_at', 'created_at'),
            ],
        );

        $builder = $allowlist->resolve($query['filter'] ?? null, $query['sort'] ?? null)
            ->apply(Role::query()->with('permissions')->withCount('users'));

        if (! isset($query['sort'])) {
            $builder->orderBy('name');
        }

        return $builder->paginate((int) ($query['per_page'] ?? 20));
    }

    /**
     * @param  array<string, mixed>  $query
     * @return LengthAwarePaginator<int, Permission>
     */
    public function permissions(array $query): LengthAwarePaginator
    {
        $allowlist = new QueryAllowlist(
            filters: [
                new AllowedFilter('module', 'module'),
                new AllowedFilter('action', 'action'),
                new AllowedFilter('name', 'name', operator: FilterOperator::Contains),
                new AllowedFilter('is_system', 'is_system', FilterValueType::Boolean),
            ],
            sorts: [
                new AllowedSort('key', 'key'),
                new AllowedSort('module', 'module'),
                new AllowedSort('name', 'name'),
            ],
        );

        $builder = $allowlist->resolve($query['filter'] ?? null, $query['sort'] ?? null)
            ->apply(Permission::query()->withCount('roles'));

        if (! isset($query['sort'])) {
            $builder->orderBy('key');
        }

        return $builder->paginate((int) ($query['per_page'] ?? 50));
    }
}
