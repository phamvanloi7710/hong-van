<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['post_id', 'locale', 'title', 'slug', 'excerpt', 'content_html', 'meta_title', 'meta_description'])]
final class PostTranslation extends Model
{
    protected $table = 'hongvan_post_translations';

    /** @return BelongsTo<Post, $this> */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
