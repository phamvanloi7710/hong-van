<?php

namespace App\Models;

use App\Domain\Localization\TranslatableModel;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'post_category_id', 'author_id', 'featured_media_id', 'status', 'is_featured', 'scheduled_for', 'published_at', 'unpublished_at', 'created_by', 'updated_by', 'deleted_by'])]
final class Post extends TranslatableModel
{
    use HasPublicId, SoftDeletes;

    public const STATUSES = ['draft', 'scheduled', 'published', 'archived'];

    protected $table = 'hongvan_posts';

    public static function translationModelClass(): string
    {
        return PostTranslation::class;
    }

    public static function translationForeignKey(): string
    {
        return 'post_id';
    }

    public static function translationNamespace(): string
    {
        return 'posts';
    }

    /** @return BelongsTo<PostCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** @return BelongsTo<Media, $this> */
    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    /** @return BelongsToMany<PostTag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(PostTag::class, 'hongvan_post_tag_links')->withPivot('created_at');
    }

    /** @return HasMany<PostSlugHistory, $this> */
    public function slugHistories(): HasMany
    {
        return $this->hasMany(PostSlugHistory::class);
    }

    protected function casts(): array
    {
        return ['is_featured' => 'boolean', 'scheduled_for' => 'immutable_datetime', 'published_at' => 'immutable_datetime', 'unpublished_at' => 'immutable_datetime'];
    }
}
