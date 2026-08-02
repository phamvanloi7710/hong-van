<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'locale', 'name', 'slug', 'short_description', 'description', 'benefits', 'usage_instructions', 'meta_title', 'meta_description'])]
final class ProductTranslation extends Model
{
    protected $table = 'hongvan_product_translations';

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
