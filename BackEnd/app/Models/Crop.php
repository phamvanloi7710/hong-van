<?php

namespace App\Models;

use App\Domain\Localization\TranslatableModel;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['crop_category_id', 'code', 'image_media_id', 'is_active', 'sort_order', 'created_by', 'updated_by', 'deleted_by'])]
final class Crop extends TranslatableModel
{
    use HasPublicId;
    use SoftDeletes;

    protected $table = 'hongvan_crops';

    public static function translationModelClass(): string
    {
        return CropTranslation::class;
    }

    public static function translationForeignKey(): string
    {
        return 'crop_id';
    }

    public static function translationNamespace(): string
    {
        return 'crops';
    }

    /** @return BelongsTo<CropCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CropCategory::class, 'crop_category_id');
    }

    /** @return BelongsTo<Media, $this> */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_media_id');
    }

    /** @return HasMany<CropStage, $this> */
    public function stages(): HasMany
    {
        return $this->hasMany(CropStage::class)->orderBy('sort_order');
    }

    /** @return HasMany<CropSolution, $this> */
    public function solutions(): HasMany
    {
        return $this->hasMany(CropSolution::class)->orderBy('sort_order');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
