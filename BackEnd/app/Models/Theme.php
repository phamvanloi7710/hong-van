<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['key', 'name', 'description', 'is_active', 'published_version_id', 'created_by', 'updated_by'])]
final class Theme extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_themes';

    /** @return HasMany<ThemeVersion, $this> */
    public function versions(): HasMany
    {
        return $this->hasMany(ThemeVersion::class);
    }

    /** @return BelongsTo<ThemeVersion, $this> */
    public function publishedVersion(): BelongsTo
    {
        return $this->belongsTo(ThemeVersion::class, 'published_version_id');
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
