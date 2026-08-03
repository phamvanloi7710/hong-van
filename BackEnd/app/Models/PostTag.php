<?php

namespace App\Models;

use App\Domain\Localization\TranslatableModel;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'is_active', 'sort_order', 'created_by', 'updated_by', 'deleted_by'])]
final class PostTag extends TranslatableModel
{
    use HasPublicId, SoftDeletes;

    protected $table = 'hongvan_post_tags';

    public static function translationModelClass(): string
    {
        return PostTagTranslation::class;
    }

    public static function translationForeignKey(): string
    {
        return 'post_tag_id';
    }

    public static function translationNamespace(): string
    {
        return 'post_tags';
    }

    /** @return BelongsToMany<Post, $this> */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'hongvan_post_tag_links')->withPivot('created_at');
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
