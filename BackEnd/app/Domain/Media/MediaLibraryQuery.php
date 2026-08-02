<?php

namespace App\Domain\Media;

use App\Models\Media;
use App\Support\Query\AllowedFilter;
use App\Support\Query\AllowedSort;
use App\Support\Query\FilterOperator;
use App\Support\Query\QueryAllowlist;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class MediaLibraryQuery
{
    /**
     * @param  array<string, mixed>  $query
     * @return LengthAwarePaginator<int, Media>
     */
    public function paginate(array $query): LengthAwarePaginator
    {
        $filters = is_array($query['filter'] ?? null) ? $query['filter'] : [];
        $directFilters = array_intersect_key($filters, array_flip(['status', 'mime_type']));
        $allowlist = new QueryAllowlist(
            filters: [
                new AllowedFilter('status', 'status'),
                new AllowedFilter('mime_type', 'mime_type', operator: FilterOperator::Contains),
            ],
            sorts: [
                new AllowedSort('created_at', 'created_at'),
                new AllowedSort('original_filename', 'original_filename'),
                new AllowedSort('size_bytes', 'size_bytes'),
            ],
        );
        $builder = Media::query();

        match ($filters['trashed'] ?? 'without') {
            'only' => $builder->onlyTrashed(),
            'with' => $builder->withTrashed(),
            default => null,
        };

        $builder = $allowlist->resolve($directFilters, $query['sort'] ?? null)->apply($builder);

        if (isset($query['search']) && is_string($query['search']) && $query['search'] !== '') {
            $search = '%'.addcslashes($query['search'], '%_\\').'%';
            $builder->where(static function ($queryBuilder) use ($search): void {
                $queryBuilder->where('original_filename', 'like', $search)
                    ->orWhere('normalized_filename', 'like', $search)
                    ->orWhere('title', 'like', $search)
                    ->orWhere('alt_text', 'like', $search);
            });
        }

        if (isset($filters['folder_id'])) {
            $builder->whereHas('folder', static fn ($folderQuery) => $folderQuery->where('public_id', $filters['folder_id']));
        }

        if (isset($filters['tag'])) {
            $builder->whereHas('tags', static fn ($tagQuery) => $tagQuery->where('slug', $filters['tag']));
        }

        if (! isset($query['sort'])) {
            $builder->latest('created_at')->latest('id');
        }

        return $builder
            ->with(['folder:id,public_id,name', 'variants', 'tags:id,public_id,name,slug'])
            ->withCount('usages')
            ->paginate((int) ($query['per_page'] ?? 24));
    }
}
