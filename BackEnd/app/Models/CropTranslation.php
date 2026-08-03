<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['crop_id', 'locale', 'name', 'slug', 'summary', 'description', 'meta_title', 'meta_description'])]
final class CropTranslation extends Model
{
    protected $table = 'hongvan_crop_translations';

    /** @return BelongsTo<Crop, $this> */
    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }
}
