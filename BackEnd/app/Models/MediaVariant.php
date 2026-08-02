<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'media_id', 'variant_key', 'disk', 'path', 'extension', 'mime_type', 'size_bytes',
    'checksum_sha256', 'width', 'height', 'status', 'error_message', 'generated_at',
])]
final class MediaVariant extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_media_variants';

    /** @return BelongsTo<Media, $this> */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'generated_at' => 'immutable_datetime',
        ];
    }
}
