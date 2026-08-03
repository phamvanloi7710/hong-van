<?php

namespace App\Domain\Warehouses;

use App\Models\Warehouse;

final class WarehouseDataSource
{
    public function identifier(): string
    {
        return 'warehouse_capabilities';
    }

    /** @return list<array<string,mixed>> */
    public function resolve(string $locale, int $limit = 12): array
    {
        $limit = max(1, min($limit, 24));

        return Warehouse::query()
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now('UTC')))
            ->where(fn ($query) => $query->whereNull('unpublished_at')->orWhere('unpublished_at', '>', now('UTC')))
            ->with(['translations', 'facilities.translations', 'services.translations', 'media'])
            ->orderBy('sort_order')->limit($limit)->get()->map(function (Warehouse $warehouse) use ($locale): array {
                $translation = $warehouse->translations->firstWhere('locale', $locale) ?? $warehouse->translations->firstWhere('locale', 'vi') ?? $warehouse->translations->first();
                $localized = static fn ($item) => ($item->translations->firstWhere('locale', $locale) ?? $item->translations->firstWhere('locale', 'vi') ?? $item->translations->first())?->name;
                $coordinates = match ($warehouse->map_display) {
                    'exact' => ['latitude' => $warehouse->latitude, 'longitude' => $warehouse->longitude],
                    'approximate' => ['latitude' => $warehouse->latitude === null ? null : round((float) $warehouse->latitude, 2), 'longitude' => $warehouse->longitude === null ? null : round((float) $warehouse->longitude, 2)],
                    default => null,
                };

                return ['public_id' => $warehouse->public_id, 'name' => $translation?->name, 'slug' => $translation?->slug, 'summary' => $translation?->summary, 'address_display' => $translation?->address_display, 'area_value' => $warehouse->area_value, 'area_unit' => $warehouse->area_unit, 'map_display' => $warehouse->map_display, 'coordinates' => $coordinates, 'business_hours' => $warehouse->business_hours, 'facilities' => $warehouse->facilities->map($localized)->filter()->values()->all(), 'services' => $warehouse->services->map($localized)->filter()->values()->all()];
            })->all();
    }
}
