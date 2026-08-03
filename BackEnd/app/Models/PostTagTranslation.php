<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['post_tag_id', 'locale', 'name', 'slug'])]
final class PostTagTranslation extends Model
{
    protected $table = 'hongvan_post_tag_translations';

    /** @return BelongsTo<PostTag, $this> */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(PostTag::class, 'post_tag_id');
    }
}
