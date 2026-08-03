<?php

namespace App\Domain\PageBuilder;

use App\Models\VehicleType;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final class FormOptionResolver
{
    /** @var array<string, list<array{value: string, label: string}>> */
    private array $memo = [];

    /** @return list<array{value: string, label: string}> */
    public function resolve(?string $source, string $locale): array
    {
        if ($source === null) {
            return [];
        }
        $key = $source.'|'.$locale;

        return $this->memo[$key] ??= match ($source) {
            'vehicle_types' => VehicleType::query()->where('is_active', true)->with('translations')->orderBy('sort_order')->limit(50)->get()->map(fn (VehicleType $item): array => $this->option($item, $locale))->all(),
            'warehouses' => Warehouse::query()->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now('UTC'))->where(fn (Builder $query) => $query->whereNull('unpublished_at')->orWhere('unpublished_at', '>', now('UTC')))->with('translations')->orderBy('sort_order')->limit(50)->get()->map(fn (Warehouse $item): array => $this->option($item, $locale))->all(),
            default => [],
        };
    }

    /** @return array{value: string, label: string} */
    private function option(Model $model, string $locale): array
    {
        $translations = $model->getRelation('translations');
        $translation = $translations instanceof Collection
            ? ($translations->firstWhere('locale', $locale) ?? $translations->firstWhere('locale', 'vi') ?? $translations->first())
            : null;

        return ['value' => (string) $model->getAttribute('public_id'), 'label' => (string) ($translation?->getAttribute('name') ?? $model->getAttribute('code'))];
    }
}
