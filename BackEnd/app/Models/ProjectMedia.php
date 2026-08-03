<?php

namespace App\Models;

use App\Domain\Localization\TranslatableModel;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_id', 'media_id', 'role', 'sort_order'])]
final class ProjectMedia extends TranslatableModel
{
    use HasPublicId;

    protected $table = 'hongvan_project_media';

    public static function translationModelClass(): string
    {
        return ProjectMediaTranslation::class;
    }

    public static function translationForeignKey(): string
    {
        return 'project_media_id';
    }

    public static function translationNamespace(): string
    {
        return 'project_media';
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<Media, $this> */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
