<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'folder_id', 'disk', 'path', 'original_filename', 'normalized_filename', 'extension',
    'mime_type', 'size_bytes', 'checksum_sha256', 'width', 'height', 'status',
    'visibility', 'is_locked', 'title', 'alt_text', 'caption', 'metadata', 'uploaded_by', 'updated_by',
    'deleted_by',
])]
final class Media extends Model
{
    use HasPublicId;
    use SoftDeletes;

    protected $table = 'hongvan_media';

    /** @return BelongsTo<MediaFolder, $this> */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    /** @return HasMany<MediaVariant, $this> */
    public function variants(): HasMany
    {
        return $this->hasMany(MediaVariant::class, 'media_id');
    }

    /** @return BelongsToMany<MediaTag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(MediaTag::class, 'hongvan_media_tag_links', 'media_id', 'media_tag_id')
            ->withPivot('created_at');
    }

    /** @return HasMany<MediaUsage, $this> */
    public function usages(): HasMany
    {
        return $this->hasMany(MediaUsage::class, 'media_id');
    }

    /** @return HasMany<MediaOperation, $this> */
    public function operations(): HasMany
    {
        return $this->hasMany(MediaOperation::class, 'media_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'is_locked' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
