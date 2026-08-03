<?php

namespace App\Models;

use App\Domain\Localization\TranslatableModel;
use App\Domain\Products\ProductPriceMode;
use App\Models\Concerns\HasPublicId;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'sku', 'code', 'status', 'product_category_id', 'brand_id', 'origin', 'packaging', 'is_featured',
    'price_mode', 'price_amount', 'price_min', 'price_max', 'currency', 'price_unit', 'price_note',
    'is_price_visible', 'published_at', 'unpublished_at', 'created_by', 'updated_by', 'deleted_by',
])]
final class Product extends TranslatableModel
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    use HasPublicId;
    use SoftDeletes;

    protected $table = 'hongvan_products';

    public static function translationModelClass(): string
    {
        return ProductTranslation::class;
    }

    public static function translationForeignKey(): string
    {
        return 'product_id';
    }

    public static function translationNamespace(): string
    {
        return 'products';
    }

    /** @return BelongsTo<ProductCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    /** @return BelongsTo<Brand, $this> */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /** @return BelongsToMany<Media, $this> */
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'hongvan_product_media', 'product_id', 'media_id')
            ->withPivot(['role', 'locale', 'is_primary', 'sort_order', 'alt_text'])
            ->withTimestamps();
    }

    /** @return BelongsToMany<ProductTag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ProductTag::class, 'hongvan_product_tag_links', 'product_id', 'product_tag_id')
            ->withPivot('created_at');
    }

    /** @return HasMany<ProductAttributeValue, $this> */
    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    /** @return HasMany<ProductSpecification, $this> */
    public function specifications(): HasMany
    {
        return $this->hasMany(ProductSpecification::class)->orderBy('sort_order');
    }

    /** @return BelongsToMany<Product, $this> */
    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'hongvan_product_related', 'product_id', 'related_product_id')
            ->withPivot(['sort_order', 'created_at']);
    }

    /** @return BelongsToMany<CropSolution, $this> */
    public function cropSolutions(): BelongsToMany
    {
        return $this->belongsToMany(CropSolution::class, 'hongvan_crop_solution_products')
            ->withPivot(['sort_order', 'recommendation_note', 'created_at']);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'price_mode' => ProductPriceMode::class,
            'price_amount' => 'decimal:4',
            'price_min' => 'decimal:4',
            'price_max' => 'decimal:4',
            'is_featured' => 'boolean',
            'is_price_visible' => 'boolean',
            'published_at' => 'immutable_datetime',
            'unpublished_at' => 'immutable_datetime',
        ];
    }
}
