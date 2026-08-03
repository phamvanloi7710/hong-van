<?php

namespace App\Models;

use App\Domain\Localization\TranslatableModel;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['parent_id', 'code', 'image_media_id', 'is_active', 'sort_order', 'created_by', 'updated_by', 'deleted_by'])]
final class CropCategory extends TranslatableModel
{
    use HasPublicId;
    use SoftDeletes;

    protected $table = 'hongvan_crop_categories';

    public static function translationModelClass(): string
    {
        return CropCategoryTranslation::class;
    }

    public static function translationForeignKey(): string
    {
        return 'crop_category_id';
    }

    public static function translationNamespace(): string
    {
        return 'crop_categories';
    }

    /** @return BelongsTo<CropCategory, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<CropCategory, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    /** @return HasMany<Crop, $this> */
    public function crops(): HasMany
    {
        return $this->hasMany(Crop::class);
    }

    /** @return BelongsTo<Media, $this> */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_media_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
