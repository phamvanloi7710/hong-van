<?php

namespace App\Models;

use App\Models\Concerns\HasSearchText;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['service_id', 'locale', 'name', 'slug', 'summary', 'content', 'content_sections', 'cta_label', 'meta_title', 'meta_description'])]
final class ServiceTranslation extends Model
{
    use HasSearchText;

    protected $table = 'hongvan_service_translations';

    /** @return BelongsTo<Service, $this> */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['content_sections' => 'array'];
    }

    protected function searchTextSourceFields(): array
    {
        return ['name', 'summary'];
    }
}
