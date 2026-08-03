<?php

namespace App\Domain\Services;

use App\Models\Service;
use Illuminate\Support\Facades\Cache;

final class ServiceDataSource
{
    public function identifier(): string
    {
        return 'service_grid';
    }

    /** @return list<array<string, mixed>> */
    public function resolve(string $locale, int $limit = 8, ?string $categoryPublicId = null, bool $featuredOnly = false): array
    {
        $safeLimit = max(1, min($limit, 24));
        $version = (int) Cache::get('services:version', 0);
        $key = 'services:data:'.$version.':'.$locale.':'.$safeLimit.':'.($categoryPublicId ?? '*').':'.(int) $featuredOnly;

        return Cache::remember($key, now()->addMinutes(10), function () use ($locale, $safeLimit, $categoryPublicId, $featuredOnly): array {
            return Service::query()
                ->where('status', 'published')
                ->where(static fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now('UTC')))
                ->where(static fn ($query) => $query->whereNull('unpublished_at')->orWhere('unpublished_at', '>', now('UTC')))
                ->when($categoryPublicId !== null, static fn ($query) => $query->whereHas('category', static fn ($category) => $category->where('public_id', $categoryPublicId)))
                ->when($featuredOnly, static fn ($query) => $query->where('is_featured', true))
                ->with(['translations', 'category.translations', 'media'])
                ->orderBy('sort_order')
                ->limit($safeLimit)
                ->get()
                ->map(function (Service $service) use ($locale): array {
                    $translation = $service->translations->firstWhere('locale', $locale)
                        ?? $service->translations->firstWhere('locale', 'vi')
                        ?? $service->translations->first();

                    return [
                        'public_id' => $service->public_id,
                        'name' => $translation?->name,
                        'slug' => $translation?->slug,
                        'summary' => $translation?->summary,
                        'service_type' => $service->service_type,
                        'specialized_module' => match ($service->service_type) {
                            'transportation_link' => 'transportation',
                            'warehouse_link' => 'warehouses',
                            default => null,
                        },
                        'category' => data_get($service->category?->translations->firstWhere('locale', $locale), 'name')
                            ?? data_get($service->category?->translations->firstWhere('locale', 'vi'), 'name'),
                        'cta' => [
                            'type' => $service->cta_type,
                            'label' => $translation?->cta_label,
                            'source_type' => 'service',
                            'source_public_id' => $service->public_id,
                        ],
                        'media' => $service->media->map(static function ($media): array {
                            $pivot = $media->getRelation('pivot');

                            return [
                                'public_id' => $media->public_id,
                                'role' => $pivot->getAttribute('role'),
                                'sort_order' => (int) $pivot->getAttribute('sort_order'),
                            ];
                        })->values()->all(),
                    ];
                })->values()->all();
        });
    }
}
