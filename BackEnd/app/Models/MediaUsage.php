<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['media_id', 'owner_type', 'owner_public_id', 'field', 'metadata'])]
final class MediaUsage extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_media_usages';

    /** @return BelongsTo<Media, $this> */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
