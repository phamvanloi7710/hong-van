<?php

namespace App\Models;

use App\Models\Concerns\HasSearchText;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['crop_solution_id', 'locale', 'title', 'slug', 'summary', 'content', 'content_sections', 'meta_title', 'meta_description'])]
final class CropSolutionTranslation extends Model
{
    use HasSearchText;

    protected $table = 'hongvan_crop_solution_translations';

    /** @return BelongsTo<CropSolution, $this> */
    public function solution(): BelongsTo
    {
        return $this->belongsTo(CropSolution::class, 'crop_solution_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['content_sections' => 'array'];
    }

    protected function searchTextSourceFields(): array
    {
        return ['title', 'summary'];
    }
}
