<?php

namespace App\Domain\Seo;

use App\Domain\Localization\TranslatableModel;
use App\Models\Certification;
use App\Models\CropSolution;
use App\Models\Gallery;
use App\Models\Post;
use App\Models\Product;
use App\Models\Project;
use App\Models\Service;
use App\Models\TransportRoute;
use App\Models\TransportServiceArea;
use App\Models\Vehicle;
use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class SeoEntityRegistry
{
    /** @var array<string, class-string<TranslatableModel>> */
    private const TYPES = [
        'product' => Product::class,
        'crop_solution' => CropSolution::class,
        'service' => Service::class,
        'post' => Post::class,
        'project' => Project::class,
        'gallery' => Gallery::class,
        'certification' => Certification::class,
        'vehicle' => Vehicle::class,
        'transport_route' => TransportRoute::class,
        'transport_service_area' => TransportServiceArea::class,
        'warehouse' => Warehouse::class,
    ];

    /** @return list<string> */
    public function types(): array
    {
        return array_keys(self::TYPES);
    }

    public function find(string $type, string $publicId): TranslatableModel
    {
        $class = $this->classFor($type);

        return $class::query()->where('public_id', $publicId)->firstOrFail();
    }

    /** @return LengthAwarePaginator<int, TranslatableModel> */
    public function paginate(string $type, string $locale, ?string $search, int $perPage): LengthAwarePaginator
    {
        $class = $this->classFor($type);
        $translationClass = $class::translationModelClass();
        $columns = $translationClass::query()->getModel()->getFillable();
        $labelColumn = in_array('name', $columns, true) ? 'name' : 'title';

        return $class::query()
            ->with(['translations' => static fn ($query) => $query->whereIn('locale', [$locale, 'vi'])])
            ->when($search, function (Builder $query, string $term) use ($labelColumn): void {
                $query->where(function (Builder $nested) use ($labelColumn, $term): void {
                    $nested->where('code', 'like', '%'.$term.'%')
                        ->orWhereHas('translations', static fn (Builder $translation) => $translation->where($labelColumn, 'like', '%'.$term.'%'));
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /** @return array{public_id: string, label: string, code: string|null, status: string|null} */
    public function serialize(TranslatableModel $entity, string $locale): array
    {
        $translation = $entity->translations->firstWhere('locale', $locale)
            ?? $entity->translations->firstWhere('locale', 'vi')
            ?? $entity->translations->first();

        return [
            'public_id' => (string) $entity->getAttribute('public_id'),
            'label' => (string) ($translation?->getAttribute('name') ?? $translation?->getAttribute('title') ?? $entity->getAttribute('code') ?? $entity->getAttribute('public_id')),
            'code' => $entity->getAttribute('code'),
            'status' => $entity->getAttribute('status'),
        ];
    }

    /** @return class-string<TranslatableModel> */
    private function classFor(string $type): string
    {
        abort_unless(isset(self::TYPES[$type]), 404);

        return self::TYPES[$type];
    }
}
