<?php

namespace App\Domain\Audit;

use App\Models\AuditLog;
use App\Support\Query\AllowedFilter;
use App\Support\Query\AllowedSort;
use App\Support\Query\FilterOperator;
use App\Support\Query\QueryAllowlist;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class AuditLogQuery
{
    /**
     * @param  array<string, mixed>  $query
     * @return LengthAwarePaginator<int, AuditLog>
     */
    public function paginate(array $query): LengthAwarePaginator
    {
        $filters = is_array($query['filter'] ?? null) ? $query['filter'] : [];
        $queryFilters = array_intersect_key($filters, array_flip([
            'action',
            'actor_public_id',
            'subject_type',
            'subject_public_id',
            'request_id',
        ]));
        $allowlist = new QueryAllowlist(
            filters: [
                new AllowedFilter('action', 'action', operator: FilterOperator::Contains),
                new AllowedFilter('actor_public_id', 'actor_public_id'),
                new AllowedFilter('subject_type', 'subject_type'),
                new AllowedFilter('subject_public_id', 'subject_public_id'),
                new AllowedFilter('request_id', 'request_id'),
            ],
            sorts: [
                new AllowedSort('occurred_at', 'occurred_at'),
                new AllowedSort('action', 'action'),
            ],
        );

        $builder = $allowlist->resolve($queryFilters, $query['sort'] ?? null)
            ->apply(AuditLog::query());

        if (isset($filters['date_from'])) {
            $builder->where('occurred_at', '>=', CarbonImmutable::parse((string) $filters['date_from'], 'UTC')->startOfDay());
        }

        if (isset($filters['date_to'])) {
            $builder->where('occurred_at', '<=', CarbonImmutable::parse((string) $filters['date_to'], 'UTC')->endOfDay());
        }

        if (! isset($query['sort'])) {
            $builder->latest('occurred_at')->latest('id');
        }

        return $builder->paginate((int) ($query['per_page'] ?? 20));
    }
}
