<?php

namespace App\Models;

use App\Domain\Localization\TranslatableModel;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['parent_id', 'code', 'is_active', 'sort_order', 'created_by', 'updated_by', 'deleted_by'])]
final class ServiceCategory extends TranslatableModel
{
    use HasPublicId;
    use SoftDeletes;

    protected $table = 'hongvan_service_categories';

    public static function translationModelClass(): string
    {
        return ServiceCategoryTranslation::class;
    }

    public static function translationForeignKey(): string
    {
        return 'service_category_id';
    }

    public static function translationNamespace(): string
    {
        return 'service_categories';
    }

    /** @return BelongsTo<ServiceCategory, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<ServiceCategory, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    /** @return HasMany<Service, $this> */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
