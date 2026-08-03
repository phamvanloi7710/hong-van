<?php

namespace App\Models;

use App\Domain\Localization\TranslatableModel;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'area_value', 'area_unit', 'latitude', 'longitude', 'map_display', 'business_hours', 'status', 'is_featured', 'sort_order', 'published_at', 'unpublished_at', 'created_by', 'updated_by', 'deleted_by'])]
final class Warehouse extends TranslatableModel
{
    use HasPublicId;
    use SoftDeletes;

    protected $table = 'hongvan_warehouses';

    public static function translationModelClass(): string
    {
        return WarehouseTranslation::class;
    }

    public static function translationForeignKey(): string
    {
        return 'warehouse_id';
    }

    public static function translationNamespace(): string
    {
        return 'warehouses';
    }

    /** @return BelongsToMany<Media, $this> */
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'hongvan_warehouse_media')->withPivot(['role', 'sort_order', 'created_at']);
    }

    /** @return BelongsToMany<WarehouseFacility, $this> */
    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(WarehouseFacility::class, 'hongvan_warehouse_facility_assignments')->withPivot(['sort_order', 'created_at']);
    }

    /** @return BelongsToMany<WarehouseService, $this> */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(WarehouseService::class, 'hongvan_warehouse_service_assignments')->withPivot(['sort_order', 'created_at']);
    }

    /** @return HasMany<WarehouseRequest, $this> */
    public function requests(): HasMany
    {
        return $this->hasMany(WarehouseRequest::class);
    }

    /** @return array<string,string> */
    protected function casts(): array
    {
        return ['area_value' => 'decimal:3', 'latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'business_hours' => 'array', 'is_featured' => 'boolean', 'published_at' => 'immutable_datetime', 'unpublished_at' => 'immutable_datetime'];
    }
}
