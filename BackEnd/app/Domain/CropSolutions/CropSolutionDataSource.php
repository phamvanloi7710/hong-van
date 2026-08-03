<?php

namespace App\Domain\CropSolutions;

use App\Models\CropSolution;
use Illuminate\Support\Facades\Cache;

final class CropSolutionDataSource
{
    public function identifier(): string
    {
        return 'crop_solution_grid';
    }

    /** @return list<array<string, mixed>> */
    public function resolve(string $locale, int $limit = 8, ?string $cropPublicId = null): array
    {
        $safeLimit = max(1, min($limit, 24));
        $version = (int) Cache::get('crop_solutions:version', 0);
        $key = 'crop_solutions:data:'.$version.':'.$locale.':'.$safeLimit.':'.($cropPublicId ?? '*');

        return Cache::remember($key, now()->addMinutes(10), function () use ($locale, $safeLimit, $cropPublicId): array {
            return CropSolution::query()
                ->where('status', 'published')
                ->where(static fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now('UTC')))
                ->where(static fn ($query) => $query->whereNull('unpublished_at')->orWhere('unpublished_at', '>', now('UTC')))
                ->when($cropPublicId !== null, static fn ($query) => $query->whereHas('crop', static fn ($crop) => $crop->where('public_id', $cropPublicId)))
                ->with([
                    'translations',
                    'crop.translations',
                    'stage.translations',
                    'heroMedia',
                    'products' => static fn ($query) => $query
                        ->where('status', 'published')
                        ->whereNull('deleted_at')
                        ->with('translations')
                        ->orderBy('hongvan_crop_solution_products.sort_order'),
                ])
                ->orderBy('sort_order')
                ->limit($safeLimit)
                ->get()
                ->map(function (CropSolution $solution) use ($locale): array {
                    $translation = $solution->translations->firstWhere('locale', $locale)
                        ?? $solution->translations->firstWhere('locale', 'vi')
                        ?? $solution->translations->first();

                    return [
                        'public_id' => $solution->public_id,
                        'title' => $translation?->title,
                        'slug' => $translation?->slug,
                        'summary' => $translation?->summary,
                        'crop' => data_get($solution->crop->translations->firstWhere('locale', $locale), 'name')
                            ?? data_get($solution->crop->translations->firstWhere('locale', 'vi'), 'name'),
                        'stage' => data_get($solution->stage?->translations->firstWhere('locale', $locale), 'name')
                            ?? data_get($solution->stage?->translations->firstWhere('locale', 'vi'), 'name'),
                        'hero_media_id' => $solution->heroMedia?->public_id,
                        'products' => $solution->products->map(static fn ($product): array => [
                            'public_id' => $product->public_id,
                            'name' => data_get($product->translations->firstWhere('locale', $locale), 'name')
                                ?? data_get($product->translations->firstWhere('locale', 'vi'), 'name'),
                            'slug' => data_get($product->translations->firstWhere('locale', $locale), 'slug')
                                ?? data_get($product->translations->firstWhere('locale', 'vi'), 'slug'),
                        ])->values()->all(),
                    ];
                })->values()->all();
        });
    }
}
