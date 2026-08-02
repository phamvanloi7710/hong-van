<?php

namespace App\Models;

use App\Domain\Localization\TranslatableModel;
use App\Models\Concerns\HasPublicId;
use Database\Factories\ProductCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['parent_id', 'code', 'is_active', 'is_featured', 'sort_order', 'created_by', 'updated_by', 'deleted_by'])]
final class ProductCategory extends TranslatableModel
{
    /** @use HasFactory<ProductCategoryFactory> */
    use HasFactory;

    use HasPublicId;
    use SoftDeletes;

    protected $table = 'hongvan_product_categories';

    public static function translationModelClass(): string
    {
        return ProductCategoryTranslation::class;
    }

    public static function translationForeignKey(): string
    {
        return 'product_category_id';
    }

    public static function translationNamespace(): string
    {
        return 'product_categories';
    }

    /** @return BelongsTo<ProductCategory, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<ProductCategory, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    /** @return HasMany<Product, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'is_featured' => 'boolean', 'sort_order' => 'integer'];
    }
}
