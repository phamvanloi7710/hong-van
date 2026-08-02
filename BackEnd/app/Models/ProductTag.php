<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'slug', 'created_by', 'updated_by'])]
final class ProductTag extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_product_tags';

    /** @return BelongsToMany<Product, $this> */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'hongvan_product_tag_links', 'product_tag_id', 'product_id')
            ->withPivot('created_at');
    }
}
