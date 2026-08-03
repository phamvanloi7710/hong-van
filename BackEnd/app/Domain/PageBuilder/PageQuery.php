<?php

namespace App\Domain\PageBuilder;

use App\Models\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class PageQuery
{
    /**
     * @param  array<string, mixed>  $input
     * @return LengthAwarePaginator<int, Page>
     */
    public function paginate(array $input): LengthAwarePaginator
    {
        $filters = is_array($input['filter'] ?? null) ? $input['filter'] : [];
        $builder = Page::query()->with(['translations', 'draftVersion', 'publishedVersion']);
        if (filled($input['search'] ?? null)) {
            $search = '%'.addcslashes(trim((string) $input['search']), '%_\\').'%';
            $builder->where(static fn ($query) => $query->where('code', 'like', $search)
                ->orWhereHas('translations', static fn ($translation) => $translation
                    ->where('title', 'like', $search)->orWhere('slug', 'like', $search)));
        }
        if (filled($filters['status'] ?? null)) {
            $builder->where('status', $filters['status']);
        }
        if (filled($filters['type'] ?? null)) {
            $builder->where('type', $filters['type']);
        }
        $sort = is_string($input['sort'] ?? null) ? $input['sort'] : '-updated_at';
        $descending = str_starts_with($sort, '-');
        $column = ltrim($sort, '-');
        $allowed = ['code', 'type', 'status', 'updated_at'];
        $builder->orderBy(in_array($column, $allowed, true) ? $column : 'updated_at', $descending ? 'desc' : 'asc')->orderByDesc('id');

        return $builder->paginate((int) ($input['per_page'] ?? 20));
    }
}
