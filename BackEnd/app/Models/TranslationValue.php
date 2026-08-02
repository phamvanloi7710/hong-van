<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['translation_key_id', 'language_id', 'value', 'is_reviewed', 'translated_by'])]
final class TranslationValue extends Model
{
    protected $table = 'hongvan_translation_values';

    /** @return BelongsTo<TranslationKey, $this> */
    public function translationKey(): BelongsTo
    {
        return $this->belongsTo(TranslationKey::class);
    }

    /** @return BelongsTo<Language, $this> */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /** @return BelongsTo<User, $this> */
    public function translator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'translated_by');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_reviewed' => 'boolean'];
    }
}
