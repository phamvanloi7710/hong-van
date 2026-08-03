<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['crop_stage_id', 'locale', 'name', 'summary', 'content'])]
final class CropStageTranslation extends Model
{
    protected $table = 'hongvan_crop_stage_translations';

    /** @return BelongsTo<CropStage, $this> */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(CropStage::class, 'crop_stage_id');
    }
}
