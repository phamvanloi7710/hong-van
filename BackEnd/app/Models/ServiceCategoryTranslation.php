<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['service_category_id', 'locale', 'name', 'slug', 'summary', 'meta_title', 'meta_description'])]
final class ServiceCategoryTranslation extends Model
{
    protected $table = 'hongvan_service_category_translations';

    /** @return BelongsTo<ServiceCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }
}
