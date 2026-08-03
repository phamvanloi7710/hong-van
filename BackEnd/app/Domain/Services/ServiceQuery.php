<?php

namespace App\Domain\Services;

use App\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ServiceQuery
{
    private const SORTS = ['code', 'sort_order', 'status', 'published_at', 'updated_at'];

    /**
     * @param  array<string, mixed>  $input
     * @return LengthAwarePaginator<int, Service>
     */
    public function paginate(array $input): LengthAwarePaginator
    {
        $filters = is_array($input['filter'] ?? null) ? $input['filter'] : [];
        $builder = Service::query()->with(['translations', 'category.translations', 'media']);

        if (isset($input['search']) && is_string($input['search']) && trim($input['search']) !== '') {
            $search = '%'.addcslashes(trim($input['search']), '%_\\').'%';
            $builder->where(static function ($query) use ($search): void {
                $query->where('code', 'like', $search)
                    ->orWhereHas('translations', static fn ($translations) => $translations
                        ->where('name', 'like', $search)
                        ->orWhere('slug', 'like', $search));
            });
        }
        foreach (['status', 'service_type', 'cta_type'] as $filter) {
            if (isset($filters[$filter]) && is_string($filters[$filter])) {
                $builder->where($filter, $filters[$filter]);
            }
        }
        if (isset($filters['category_id']) && is_string($filters['category_id'])) {
            $builder->whereHas('category', static fn ($category) => $category->where('public_id', $filters['category_id']));
        }
        if (isset($filters['featured'])) {
            $builder->where('is_featured', filter_var($filters['featured'], FILTER_VALIDATE_BOOL));
        }
        match ($filters['trashed'] ?? 'without') {
            'with' => $builder->withTrashed(),
            'only' => $builder->onlyTrashed(),
            default => null,
        };

        $sort = is_string($input['sort'] ?? null) ? $input['sort'] : '-updated_at';
        $descending = str_starts_with($sort, '-');
        $column = ltrim($sort, '-');
        $builder->orderBy(in_array($column, self::SORTS, true) ? $column : 'updated_at', $descending ? 'desc' : 'asc')
            ->orderByDesc('id');

        return $builder->paginate((int) ($input['per_page'] ?? 20));
    }
}
