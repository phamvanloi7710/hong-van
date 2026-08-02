<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['parent_id', 'name', 'slug', 'sort_order', 'created_by', 'updated_by'])]
final class MediaFolder extends Model
{
    use HasPublicId;
    use SoftDeletes;

    protected $table = 'hongvan_media_folders';

    /** @return BelongsTo<MediaFolder, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<MediaFolder, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /** @return HasMany<Media, $this> */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class, 'folder_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }
}
