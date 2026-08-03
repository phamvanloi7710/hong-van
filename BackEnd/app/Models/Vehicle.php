<?php

namespace App\Models;

use App\Domain\Localization\TranslatableModel;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['vehicle_type_id', 'code', 'payload_capacity', 'payload_unit', 'availability_display', 'status', 'is_featured', 'sort_order', 'published_at', 'unpublished_at', 'created_by', 'updated_by', 'deleted_by'])]
final class Vehicle extends TranslatableModel
{
    use HasPublicId;
    use SoftDeletes;

    protected $table = 'hongvan_vehicles';

    public static function translationModelClass(): string
    {
        return VehicleTranslation::class;
    }

    public static function translationForeignKey(): string
    {
        return 'vehicle_id';
    }

    public static function translationNamespace(): string
    {
        return 'vehicles';
    }

    /** @return BelongsTo<VehicleType, $this> */
    public function type(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    /** @return BelongsToMany<Media, $this> */
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'hongvan_vehicle_media')->withPivot(['role', 'sort_order', 'created_at']);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['payload_capacity' => 'decimal:3', 'is_featured' => 'boolean', 'published_at' => 'immutable_datetime', 'unpublished_at' => 'immutable_datetime'];
    }
}
