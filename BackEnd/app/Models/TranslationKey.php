<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['namespace', 'key', 'description', 'is_system'])]
final class TranslationKey extends Model
{
    use HasPublicId;

    protected $table = 'hongvan_translation_keys';

    /** @return HasMany<TranslationValue, $this> */
    public function values(): HasMany
    {
        return $this->hasMany(TranslationValue::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_system' => 'boolean'];
    }
}
