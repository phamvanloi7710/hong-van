<?php

namespace App\Http\Resources\Api\V1\Transportation;

use App\Models\TransportRoute;
use App\Models\TransportServiceArea;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

final class TransportationResource extends JsonResource
{
    /** @return array<string,mixed> */
    public function toArray(Request $request): array
    {
        $model = $this->resource;
        $base = ['public_id' => $model->public_id, 'code' => $model->code, 'sort_order' => (int) $model->sort_order, 'translations' => $model->relationLoaded('translations') ? $model->translations->map(fn ($item): array => Arr::only($item->getAttributes(), ['locale', 'name', 'slug', 'summary', 'description', 'body_dimensions', 'meta_title', 'meta_description']))->all() : [], 'updated_at' => $this->iso($model->updated_at)];
        if ($model instanceof VehicleType) {
            return [...$base, 'is_active' => (bool) $model->is_active, 'vehicles_count' => (int) ($model->vehicles_count ?? 0)];
        }
        if ($model instanceof Vehicle) {
            return [...$base, 'vehicle_type_id' => $model->type?->public_id, 'payload_capacity' => $model->payload_capacity, 'payload_unit' => $model->payload_unit, 'availability_display' => $model->availability_display, 'status' => $model->status, 'is_featured' => (bool) $model->is_featured, 'published_at' => $this->iso($model->published_at), 'unpublished_at' => $this->iso($model->unpublished_at), 'media' => $model->media->map(function ($media): array {
                $pivot = $media->getRelation('pivot');

                return ['public_id' => $media->public_id, 'role' => $pivot->getAttribute('role'), 'sort_order' => (int) $pivot->getAttribute('sort_order')];
            })->all()];
        }
        if ($model instanceof TransportRoute) {
            return [...$base, 'origin_code' => $model->origin_code, 'destination_code' => $model->destination_code, 'status' => $model->status, 'is_featured' => (bool) $model->is_featured, 'published_at' => $this->iso($model->published_at), 'unpublished_at' => $this->iso($model->unpublished_at)];
        }
        if ($model instanceof TransportServiceArea) {
            return [...$base, 'status' => $model->status, 'is_featured' => (bool) $model->is_featured, 'published_at' => $this->iso($model->published_at), 'unpublished_at' => $this->iso($model->unpublished_at)];
        }

        return $base;
    }

    private function iso(mixed $value): ?string
    {
        return $value instanceof CarbonInterface ? $value->utc()->toISOString() : null;
    }
}
