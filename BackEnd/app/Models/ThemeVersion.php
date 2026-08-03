<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['theme_id', 'version_number', 'status', 'tokens', 'compiled_css', 'checksum', 'parent_version_id', 'created_by', 'published_by', 'published_at'])]
final class ThemeVersion extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_theme_versions';

    /** @return BelongsTo<Theme, $this> */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    protected function casts(): array
    {
        return ['tokens' => 'array', 'published_at' => 'immutable_datetime'];
    }
}
