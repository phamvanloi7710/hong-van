<?php

namespace App\Domain\CropSolutions;

use App\Models\CropSolution;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class CropSolutionQuery
{
    /**
     * @param  array<string, mixed>  $input
     * @return LengthAwarePaginator<int, CropSolution>
     */
    public function paginate(array $input): LengthAwarePaginator
    {
        $filters = is_array($input['filter'] ?? null) ? $input['filter'] : [];
        $builder = CropSolution::query()->with([
            'translations',
            'crop.translations',
            'stage.translations',
            'heroMedia',
            'products.translations',
        ]);

        if (isset($input['search']) && is_string($input['search']) && trim($input['search']) !== '') {
            $search = '%'.addcslashes(trim($input['search']), '%_\\').'%';
            $builder->where(static function ($query) use ($search): void {
                $query->where('code', 'like', $search)
                    ->orWhereHas('translations', static fn ($translations) => $translations
                        ->where('title', 'like', $search)
                        ->orWhere('slug', 'like', $search));
            });
        }
        if (isset($filters['status']) && is_string($filters['status'])) {
            $builder->where('status', $filters['status']);
        }
        if (isset($filters['crop_id']) && is_string($filters['crop_id'])) {
            $builder->whereHas('crop', static fn ($crop) => $crop->where('public_id', $filters['crop_id']));
        }
        if (isset($filters['stage_id']) && is_string($filters['stage_id'])) {
            $builder->whereHas('stage', static fn ($stage) => $stage->where('public_id', $filters['stage_id']));
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
        $builder->orderBy(ltrim($sort, '-'), $descending ? 'desc' : 'asc')->orderByDesc('id');

        return $builder->paginate((int) ($input['per_page'] ?? 20));
    }
}
