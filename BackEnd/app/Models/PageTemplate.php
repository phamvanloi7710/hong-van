<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['key', 'name', 'description', 'page_template_category_id', 'is_system', 'is_active', 'published_version_id', 'created_by', 'updated_by'])]
final class PageTemplate extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_page_templates';

    /** @return HasMany<PageTemplateVersion, $this> */
    public function versions(): HasMany
    {
        return $this->hasMany(PageTemplateVersion::class);
    }

    /** @return BelongsTo<PageTemplateVersion, $this> */
    public function publishedVersion(): BelongsTo
    {
        return $this->belongsTo(PageTemplateVersion::class, 'published_version_id');
    }

    /** @return BelongsTo<PageTemplateCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PageTemplateCategory::class, 'page_template_category_id');
    }

    protected function casts(): array
    {
        return ['is_system' => 'boolean', 'is_active' => 'boolean'];
    }
}
