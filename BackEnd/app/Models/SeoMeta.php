<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'seoable_type', 'seoable_id', 'locale', 'meta_title', 'meta_description', 'canonical_url',
    'robots_index', 'robots_follow', 'og_title', 'og_description', 'og_image_media_id', 'og_type',
    'twitter_card', 'twitter_title', 'twitter_description', 'focus_keywords', 'created_by', 'updated_by',
])]
final class SeoMeta extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_seo_meta';

    /** @return BelongsTo<Media, $this> */
    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_image_media_id');
    }

    protected function casts(): array
    {
        return [
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
            'focus_keywords' => 'array',
        ];
    }
}
