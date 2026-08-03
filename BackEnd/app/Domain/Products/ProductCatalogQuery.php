<?php

namespace App\Domain\Products;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ProductCatalogQuery
{
    /**
     * @param  array<string, mixed>  $input
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginate(array $input): LengthAwarePaginator
    {
        $filters = is_array($input['filter'] ?? null) ? $input['filter'] : [];
        $builder = Product::query()
            ->with([
                'translations',
                'category.translations',
                'category.parent:id,public_id',
                'brand.translations',
                'brand.logo:id,public_id',
                'tags',
                'media',
            ]);

        if (isset($input['search']) && is_string($input['search']) && trim($input['search']) !== '') {
            $search = '%'.addcslashes(trim($input['search']), '%_\\').'%';
            $builder->where(static function ($query) use ($search): void {
                $query->where('sku', 'like', $search)
                    ->orWhere('code', 'like', $search)
                    ->orWhereHas('translations', static fn ($translationQuery) => $translationQuery
                        ->where('name', 'like', $search)
                        ->orWhere('slug', 'like', $search));
            });
        }

        if (isset($filters['status']) && is_string($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (isset($filters['price_mode']) && is_string($filters['price_mode'])) {
            $builder->where('price_mode', $filters['price_mode']);
        }

        if (isset($filters['featured'])) {
            $builder->where('is_featured', filter_var($filters['featured'], FILTER_VALIDATE_BOOL));
        }

        if (isset($filters['category_id']) && is_string($filters['category_id'])) {
            $builder->whereHas('category', static fn ($categoryQuery) => $categoryQuery->where('public_id', $filters['category_id']));
        }

        if (isset($filters['brand_id']) && is_string($filters['brand_id'])) {
            $builder->whereHas('brand', static fn ($brandQuery) => $brandQuery->where('public_id', $filters['brand_id']));
        }

        match ($filters['trashed'] ?? 'without') {
            'with' => $builder->withTrashed(),
            'only' => $builder->onlyTrashed(),
            default => null,
        };

        $sort = is_string($input['sort'] ?? null) ? $input['sort'] : '-updated_at';
        $descending = str_starts_with($sort, '-');
        $column = ltrim($sort, '-');
        $builder->orderBy($column, $descending ? 'desc' : 'asc')->orderByDesc('id');

        return $builder->paginate((int) ($input['per_page'] ?? 20));
    }
}
