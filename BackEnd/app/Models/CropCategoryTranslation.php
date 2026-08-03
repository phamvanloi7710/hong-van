<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['crop_category_id', 'locale', 'name', 'slug', 'summary', 'meta_title', 'meta_description'])]
final class CropCategoryTranslation extends Model
{
    protected $table = 'hongvan_crop_category_translations';

    /** @return BelongsTo<CropCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CropCategory::class, 'crop_category_id');
    }
}
