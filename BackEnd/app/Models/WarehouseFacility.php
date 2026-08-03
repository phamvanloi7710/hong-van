<?php

namespace App\Models;

use App\Domain\Localization\TranslatableModel;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['code', 'icon', 'is_active', 'sort_order', 'created_by', 'updated_by'])]
final class WarehouseFacility extends TranslatableModel
{
    use HasPublicId;

    protected $table = 'hongvan_warehouse_facilities';

    public static function translationModelClass(): string
    {
        return WarehouseFacilityTranslation::class;
    }

    public static function translationForeignKey(): string
    {
        return 'warehouse_facility_id';
    }

    public static function translationNamespace(): string
    {
        return 'warehouse_facilities';
    }

    /** @return BelongsToMany<Warehouse, $this> */
    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'hongvan_warehouse_facility_assignments')->withPivot(['sort_order', 'created_at']);
    }

    /** @return array<string,string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
