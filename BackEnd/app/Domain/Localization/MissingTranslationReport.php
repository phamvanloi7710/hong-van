<?php

namespace App\Domain\Localization;

use App\Models\Language;
use App\Models\TranslationKey;

final class MissingTranslationReport
{
    /** @return array{total_keys: int, languages: list<array{locale: string, missing_count: int, missing_keys: list<string>}>} */
    public function generate(): array
    {
        $keys = TranslationKey::query()->with('values:id,translation_key_id,language_id,value')->orderBy('namespace')->orderBy('key')->get();
        $languages = Language::query()->orderBy('sort_order')->get();

        return [
            'total_keys' => $keys->count(),
            'languages' => $languages->map(static function (Language $language) use ($keys): array {
                $missing = $keys->filter(static function (TranslationKey $key) use ($language): bool {
                    $value = $key->values->firstWhere('language_id', $language->getKey())?->value;

                    return ! is_string($value) || trim($value) === '';
                })->map(static fn (TranslationKey $key): string => $key->namespace.'.'.$key->key)->values()->all();

                return [
                    'locale' => $language->locale,
                    'missing_count' => count($missing),
                    'missing_keys' => $missing,
                ];
            })->all(),
        ];
    }
}
