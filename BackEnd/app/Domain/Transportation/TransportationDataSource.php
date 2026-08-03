<?php

namespace App\Domain\Transportation;

use App\Models\TransportRoute;
use App\Models\TransportServiceArea;
use App\Models\Vehicle;

final class TransportationDataSource
{
    public function identifier(): string
    {
        return 'transportation_capabilities';
    }

    /** @return array{vehicles:list<array<string,mixed>>,routes:list<array<string,mixed>>,areas:list<array<string,mixed>>} */
    public function resolve(string $locale, int $limit = 12): array
    {
        $limit = max(1, min($limit, 24));
        $visible = static fn ($query) => $query->where('status', 'published')->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now('UTC')))->where(fn ($q) => $q->whereNull('unpublished_at')->orWhere('unpublished_at', '>', now('UTC')));
        $translation = static fn ($model) => $model->translations->firstWhere('locale', $locale) ?? $model->translations->firstWhere('locale', 'vi') ?? $model->translations->first();

        return [
            'vehicles' => Vehicle::query()->tap($visible)->with(['translations', 'type.translations', 'media'])->orderBy('sort_order')->limit($limit)->get()->map(fn ($item) => ['public_id' => $item->public_id, 'name' => $translation($item)?->name, 'payload_capacity' => $item->payload_capacity, 'payload_unit' => $item->payload_unit, 'availability_display' => $item->availability_display])->all(),
            'routes' => TransportRoute::query()->tap($visible)->with('translations')->orderBy('sort_order')->limit($limit)->get()->map(fn ($item) => ['public_id' => $item->public_id, 'name' => $translation($item)?->name, 'origin_code' => $item->origin_code, 'destination_code' => $item->destination_code])->all(),
            'areas' => TransportServiceArea::query()->tap($visible)->with('translations')->orderBy('sort_order')->limit($limit)->get()->map(fn ($item) => ['public_id' => $item->public_id, 'name' => $translation($item)?->name])->all(),
        ];
    }
}
