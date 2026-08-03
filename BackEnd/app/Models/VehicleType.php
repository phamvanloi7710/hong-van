<?php

namespace App\Models;

use App\Domain\Localization\TranslatableModel;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'is_active', 'sort_order', 'created_by', 'updated_by'])]
final class VehicleType extends TranslatableModel
{
    use HasPublicId;

    protected $table = 'hongvan_vehicle_types';

    public static function translationModelClass(): string
    {
        return VehicleTypeTranslation::class;
    }

    public static function translationForeignKey(): string
    {
        return 'vehicle_type_id';
    }

    public static function translationNamespace(): string
    {
        return 'vehicle_types';
    }

    /** @return HasMany<Vehicle, $this> */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
