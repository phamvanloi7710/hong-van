<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'attribute_definition_id', 'locale', 'value_text', 'value_decimal', 'value_boolean', 'value_json'])]
final class ProductAttributeValue extends Model
{
    protected $table = 'hongvan_product_attribute_values';

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return BelongsTo<ProductAttributeDefinition, $this> */
    public function definition(): BelongsTo
    {
        return $this->belongsTo(ProductAttributeDefinition::class, 'attribute_definition_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['value_decimal' => 'decimal:4', 'value_boolean' => 'boolean', 'value_json' => 'array'];
    }
}
