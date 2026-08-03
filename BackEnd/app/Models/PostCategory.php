<?php

namespace App\Models;

use App\Domain\Localization\TranslatableModel;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['parent_id', 'code', 'is_active', 'sort_order', 'created_by', 'updated_by', 'deleted_by'])]
final class PostCategory extends TranslatableModel
{
    use HasPublicId, SoftDeletes;

    protected $table = 'hongvan_post_categories';

    public static function translationModelClass(): string
    {
        return PostCategoryTranslation::class;
    }

    public static function translationForeignKey(): string
    {
        return 'post_category_id';
    }

    public static function translationNamespace(): string
    {
        return 'post_categories';
    }

    /** @return BelongsTo<PostCategory, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<PostCategory, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    /** @return HasMany<Post, $this> */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
