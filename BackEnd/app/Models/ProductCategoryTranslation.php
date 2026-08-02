<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_category_id', 'locale', 'name', 'slug', 'summary', 'meta_title', 'meta_description'])]
final class ProductCategoryTranslation extends Model
{
    protected $table = 'hongvan_product_category_translations';

    /** @return BelongsTo<ProductCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }
}
