<?php

namespace App\Models;

use App\Domain\Localization\TranslatableModel;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'started_on', 'completed_on', 'status', 'is_featured', 'sort_order', 'published_at', 'created_by', 'updated_by', 'deleted_by'])]
final class Project extends TranslatableModel
{
    use HasPublicId, SoftDeletes;

    protected $table = 'hongvan_projects';

    public static function translationModelClass(): string
    {
        return ProjectTranslation::class;
    }

    public static function translationForeignKey(): string
    {
        return 'project_id';
    }

    public static function translationNamespace(): string
    {
        return 'projects';
    }

    /** @return HasMany<ProjectMedia, $this> */
    public function mediaItems(): HasMany
    {
        return $this->hasMany(ProjectMedia::class)->orderBy('sort_order')->orderBy('id');
    }

    protected function casts(): array
    {
        return ['is_featured' => 'boolean', 'started_on' => 'immutable_date', 'completed_on' => 'immutable_date', 'published_at' => 'immutable_datetime'];
    }
}
