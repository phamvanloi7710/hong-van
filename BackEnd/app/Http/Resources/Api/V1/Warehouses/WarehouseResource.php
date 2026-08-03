<?php

namespace App\Http\Resources\Api\V1\Warehouses;

use App\Models\Warehouse;
use App\Models\WarehouseFacility;
use App\Models\WarehouseService;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

final class WarehouseResource extends JsonResource
{
    /** @return array<string,mixed> */
    public function toArray(Request $request): array
    {
        $model = $this->resource;
        $translationFields = $model instanceof Warehouse ? ['locale', 'name', 'slug', 'summary', 'description', 'address_display', 'area_description', 'capacity_description', 'security_description', 'fire_safety_description', 'business_hours_description', 'meta_title', 'meta_description'] : ['locale', 'name', 'description'];
        $base = ['public_id' => $model->public_id, 'code' => $model->code, 'sort_order' => (int) $model->sort_order, 'translations' => $model->relationLoaded('translations') ? $model->translations->map(fn ($item): array => Arr::only($item->getAttributes(), $translationFields))->all() : [], 'updated_at' => $this->iso($model->updated_at)];
        if ($model instanceof WarehouseFacility || $model instanceof WarehouseService) {
            return [...$base, 'icon' => $model->icon, 'is_active' => (bool) $model->is_active, 'warehouses_count' => (int) ($model->warehouses_count ?? 0)];
        }
        if ($model instanceof Warehouse) {
            return [...$base, 'area_value' => $model->area_value, 'area_unit' => $model->area_unit, 'latitude' => $model->latitude, 'longitude' => $model->longitude, 'map_display' => $model->map_display, 'business_hours' => $model->business_hours ?? [], 'status' => $model->status, 'is_featured' => (bool) $model->is_featured, 'published_at' => $this->iso($model->published_at), 'unpublished_at' => $this->iso($model->unpublished_at), 'facility_ids' => $model->facilities->pluck('public_id')->all(), 'service_ids' => $model->services->pluck('public_id')->all(), 'media' => $model->media->map(function ($media): array {
                $pivot = $media->getRelation('pivot');

                return ['public_id' => $media->public_id, 'role' => $pivot->getAttribute('role'), 'sort_order' => (int) $pivot->getAttribute('sort_order')];
            })->all()];
        }

        return $base;
    }

    private function iso(mixed $value): ?string
    {
        return $value instanceof CarbonInterface ? $value->utc()->toISOString() : null;
    }
}
