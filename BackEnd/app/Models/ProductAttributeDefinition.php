<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'data_type', 'unit', 'options', 'is_filterable', 'is_required', 'sort_order', 'created_by', 'updated_by'])]
final class ProductAttributeDefinition extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_product_attribute_definitions';

    /** @return HasMany<ProductAttributeValue, $this> */
    public function values(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_definition_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_filterable' => 'boolean',
            'is_required' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
