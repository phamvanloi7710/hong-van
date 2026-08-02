<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'slug'])]
final class MediaTag extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_media_tags';

    /** @return BelongsToMany<Media, $this> */
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'hongvan_media_tag_links', 'media_tag_id', 'media_id')
            ->withPivot('created_at');
    }
}
