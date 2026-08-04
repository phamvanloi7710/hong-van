<?php

namespace App\Models;

use App\Domain\Localization\TranslatableModel;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'type', 'status', 'is_home', 'page_template_id', 'draft_version_id', 'published_version_id', 'created_by', 'updated_by', 'deleted_by'])]
final class Page extends TranslatableModel
{
    use HasPublicId, SoftDeletes;

    public const STATUSES = ['draft', 'published', 'archived'];

    public const TYPES = ['standard', 'landing', 'system'];

    protected $table = 'hongvan_pages';

    public static function translationModelClass(): string
    {
        return PageTranslation::class;
    }

    public static function translationForeignKey(): string
    {
        return 'page_id';
    }

    public static function translationNamespace(): string
    {
        return 'pages';
    }

    /** @return HasMany<PageVersion, $this> */
    public function versions(): HasMany
    {
        return $this->hasMany(PageVersion::class);
    }

    /** @return HasMany<PagePublishSchedule, $this> */
    public function publishSchedules(): HasMany
    {
        return $this->hasMany(PagePublishSchedule::class);
    }

    /** @return BelongsTo<PageVersion, $this> */
    public function draftVersion(): BelongsTo
    {
        return $this->belongsTo(PageVersion::class, 'draft_version_id');
    }

    /** @return BelongsTo<PageVersion, $this> */
    public function publishedVersion(): BelongsTo
    {
        return $this->belongsTo(PageVersion::class, 'published_version_id');
    }

    /** @return BelongsTo<PageTemplate, $this> */
    public function template(): BelongsTo
    {
        return $this->belongsTo(PageTemplate::class, 'page_template_id');
    }

    protected function casts(): array
    {
        return ['is_home' => 'boolean'];
    }
}
