<?php

namespace App\Models;

use App\Domain\Localization\TranslatableModel;
use App\Models\Concerns\HasPublicId;
use Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'logo_media_id', 'is_active', 'sort_order', 'created_by', 'updated_by', 'deleted_by'])]
final class Brand extends TranslatableModel
{
    /** @use HasFactory<BrandFactory> */
    use HasFactory;

    use HasPublicId;
    use SoftDeletes;

    protected $table = 'hongvan_brands';

    public static function translationModelClass(): string
    {
        return BrandTranslation::class;
    }

    public static function translationForeignKey(): string
    {
        return 'brand_id';
    }

    public static function translationNamespace(): string
    {
        return 'brands';
    }

    /** @return BelongsTo<Media, $this> */
    public function logo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'logo_media_id');
    }

    /** @return HasMany<Product, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }
}
