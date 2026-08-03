<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['post_id', 'locale', 'slug', 'created_at'])]
final class PostSlugHistory extends Model
{
    public $timestamps = false;

    protected $table = 'hongvan_post_slug_histories';

    /** @return BelongsTo<Post, $this> */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    protected function casts(): array
    {
        return ['created_at' => 'immutable_datetime'];
    }
}
