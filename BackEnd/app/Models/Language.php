<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['locale', 'name', 'native_name', 'is_active', 'is_default', 'fallback_language_id', 'sort_order'])]
final class Language extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_languages';

    /** @return BelongsTo<Language, $this> */
    public function fallbackLanguage(): BelongsTo
    {
        return $this->belongsTo(self::class, 'fallback_language_id');
    }

    /** @return HasMany<Language, $this> */
    public function fallbackForLanguages(): HasMany
    {
        return $this->hasMany(self::class, 'fallback_language_id');
    }

    /** @return HasMany<TranslationValue, $this> */
    public function translationValues(): HasMany
    {
        return $this->hasMany(TranslationValue::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
