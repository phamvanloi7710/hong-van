<?php

namespace App\Domain\Posts;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class PostQuery
{
    private const SORTS = ['code', 'status', 'scheduled_for', 'published_at', 'updated_at'];

    /**
     * @param  array<string, mixed>  $input
     * @return LengthAwarePaginator<int, Post>
     */
    public function paginate(array $input): LengthAwarePaginator
    {
        $filters = is_array($input['filter'] ?? null) ? $input['filter'] : [];
        $builder = Post::query()->with(['translations', 'category.translations', 'tags.translations', 'author', 'featuredMedia']);

        if (filled($input['search'] ?? null)) {
            $search = '%'.addcslashes(trim((string) $input['search']), '%_\\').'%';
            $builder->where(static fn ($query) => $query->where('code', 'like', $search)
                ->orWhereHas('translations', static fn ($translation) => $translation
                    ->where('title', 'like', $search)->orWhere('slug', 'like', $search)));
        }
        if (filled($filters['status'] ?? null)) {
            $builder->where('status', $filters['status']);
        }
        if (filled($filters['category_id'] ?? null)) {
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
