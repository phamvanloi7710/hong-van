<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['post_category_id', 'locale', 'name', 'slug', 'description', 'meta_title', 'meta_description'])]
final class PostCategoryTranslation extends Model
{
    protected $table = 'hongvan_post_category_translations';

    /** @return BelongsTo<PostCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }
}
