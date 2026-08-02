<?php

namespace App\Domain\Localization;

use App\Models\TranslationKey;
use App\Models\TranslationValue;

final readonly class TranslationResolver
{
    public function __construct(private LocaleRegistry $locales) {}

    /** @param array<string, string|int|float> $replacements */
    public function translate(string $namespace, string $key, string $locale, array $replacements = [], ?string $default = null): string
    {
        $translationKey = TranslationKey::query()
            ->where('namespace', $namespace)
            ->where('key', $key)
            ->first();

        if ($translationKey instanceof TranslationKey) {
            foreach ($this->locales->fallbackChain($locale) as $candidate) {
                $value = TranslationValue::query()
                    ->where('translation_key_id', $translationKey->getKey())
                    ->whereHas('language', static fn ($query) => $query->where('locale', $candidate))
                    ->value('value');

                if (is_string($value) && trim($value) !== '') {
                    return $this->replace($value, $replacements);
                }
            }
        }

        return $this->replace($default ?? $namespace.'.'.$key, $replacements);
    }

    /** @param array<string, string|int|float> $replacements */
    private function replace(string $value, array $replacements): string
    {
        foreach ($replacements as $key => $replacement) {
            $value = str_replace('{'.$key.'}', (string) $replacement, $value);
        }

        return $value;
    }
}
