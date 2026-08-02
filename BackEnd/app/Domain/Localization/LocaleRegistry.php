<?php

namespace App\Domain\Localization;

use App\Models\Language;

final class LocaleRegistry
{
    public function normalize(?string $locale): ?string
    {
        if ($locale === null || trim($locale) === '') {
            return null;
        }

        $normalized = strtolower(str_replace('_', '-', trim($locale)));
        $baseLocale = explode('-', $normalized, 2)[0];

        foreach ($this->supportedLocales() as $supportedLocale) {
            if ($normalized === strtolower($supportedLocale) || $baseLocale === strtolower($supportedLocale)) {
                return $supportedLocale;
            }
        }

        return null;
    }

    public function defaultLocale(): string
    {
        return (string) (Language::query()
            ->where('is_default', true)
            ->where('is_active', true)
            ->value('locale') ?? config('localization.default_locale', 'vi'));
    }

    public function isActive(string $locale): bool
    {
        return Language::query()->where('locale', $locale)->where('is_active', true)->exists();
    }

    /** @return list<string> */
    public function supportedLocales(): array
    {
        $locales = Language::query()->orderBy('sort_order')->pluck('locale')->all();

        if ($locales !== []) {
            return $locales;
        }

        return array_values(array_filter(
            config('localization.supported_locales', ['vi', 'en', 'zh']),
            static fn (mixed $locale): bool => is_string($locale) && $locale !== '',
        ));
    }

    /** @return list<string> */
    public function fallbackChain(string $locale): array
    {
        $resolved = $this->normalize($locale) ?? $this->defaultLocale();
        $chain = [];

        while (! in_array($resolved, $chain, true)) {
            $chain[] = $resolved;
            $fallback = Language::query()
                ->where('locale', $resolved)
                ->with('fallbackLanguage:id,locale')
                ->first()?->fallbackLanguage?->locale;

            if (! is_string($fallback) || $fallback === '') {
                break;
            }

            $resolved = $fallback;
        }

        $default = $this->defaultLocale();
        if (! in_array($default, $chain, true)) {
            $chain[] = $default;
        }

        return $chain;
    }
}
