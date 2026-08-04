<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['key', 'name', 'sort_order'])]
final class PageTemplateCategory extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_page_template_categories';

    /** @return HasMany<PageTemplate, $this> */
    public function templates(): HasMany
    {
        return $this->hasMany(PageTemplate::class);
    }
}
