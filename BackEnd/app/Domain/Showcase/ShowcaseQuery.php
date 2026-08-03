<?php

namespace App\Domain\Showcase;

use App\Models\Certification;
use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Models\Partner;
use App\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class ShowcaseQuery
{
    /**
     * @param  array<string, mixed>  $input
     * @return LengthAwarePaginator<int, Model>
     */
    public function paginate(string $kind, array $input): LengthAwarePaginator
    {
        $filters = is_array($input['filter'] ?? null) ? $input['filter'] : [];
        $builder = $this->builder($kind);
        if (filled($input['search'] ?? null)) {
            $search = '%'.addcslashes(trim((string) $input['search']), '%_\\').'%';
            $fields = match ($kind) {
                'gallery-items' => ['title'], 'partners' => ['name'], 'projects' => ['title', 'slug'], default => ['name', 'slug']
            };
            $builder->where(function (Builder $query) use ($kind, $search, $fields): void {
                if ($kind !== 'gallery-items') {
                    $query->where('code', 'like', $search);
                }
                $query->orWhereHas('translations', function (Builder $translation) use ($fields, $search): void {
                    $translation->where(function (Builder $localized) use ($fields, $search): void {
                        foreach ($fields as $index => $field) {
                            $index === 0 ? $localized->where($field, 'like', $search) : $localized->orWhere($field, 'like', $search);
                        }
                    });
                });
            });
        }
        if (filled($filters['status'] ?? null)) {
            $builder->where('status', $filters['status']);
        }
        if (isset($filters['featured'])) {
            $builder->where('is_featured', filter_var($filters['featured'], FILTER_VALIDATE_BOOL));
        }
        if ($kind === 'gallery-items' && filled($filters['gallery_id'] ?? null)) {
            $builder->whereHas('gallery', fn (Builder $q) => $q->where('public_id', $filters['gallery_id']));
        }
        if (($filters['trashed'] ?? 'without') !== 'without') {
            $builder->withoutGlobalScope(SoftDeletingScope::class);
        }
        if (($filters['trashed'] ?? 'without') === 'only') {
            $builder->whereNotNull($builder->getModel()->qualifyColumn('deleted_at'));
        }
        $sort = (string) ($input['sort'] ?? 'sort_order');
        $column = ltrim($sort, '-');
        if ($kind === 'gallery-items' && $column === 'code') {
            $column = 'sort_order';
        }
        $builder->orderBy(in_array($column, ['code', 'status', 'sort_order', 'updated_at'], true) ? $column : 'sort_order', str_starts_with($sort, '-') ? 'desc' : 'asc')->orderBy('id');

        return $builder->paginate((int) ($input['per_page'] ?? 100));
    }

    /** @return Builder<*> */
    private function builder(string $kind): Builder
    {
        return match ($kind) {
            'galleries' => Gallery::query()->with(['translations', 'items.translations', 'items.media']),
            'gallery-items' => GalleryItem::query()->with(['translations', 'gallery.translations', 'media']),
            'partners' => Partner::query()->with(['translations', 'logo']),
            'certifications' => Certification::query()->with(['translations', 'image', 'document']),
            'projects' => Project::query()->with(['translations', 'mediaItems.translations', 'mediaItems.media']),
            default => throw new \InvalidArgumentException('Unknown showcase kind.'),
        };
    }
}
