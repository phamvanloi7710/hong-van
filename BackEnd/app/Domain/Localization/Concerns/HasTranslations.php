<?php

namespace App\Domain\Localization\Concerns;

use App\Domain\Localization\LocaleRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @mixin Model */
trait HasTranslations
{
    /** @return HasMany<*, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(static::translationModelClass(), static::translationForeignKey());
    }

    public function translationForLocale(?string $locale = null): ?Model
    {
        $registry = app(LocaleRegistry::class);

        foreach ($registry->fallbackChain($locale ?? app()->getLocale()) as $candidate) {
            $translation = $this->translations()->where('locale', $candidate)->first();

            if ($translation instanceof Model) {
                return $translation;
            }
        }

        return null;
    }
}
