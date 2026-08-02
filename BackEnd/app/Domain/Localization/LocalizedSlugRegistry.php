<?php

namespace App\Domain\Localization;

use App\Models\Language;
use App\Models\LocalizedSlug;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class LocalizedSlugRegistry
{
    public function __construct(private LocaleRegistry $locales) {}

    public function reserve(string $namespace, string $slug, string $locale, string $ownerType, string $ownerKey): LocalizedSlug
    {
        $normalizedLocale = $this->locales->normalize($locale);
        $normalizedSlug = Str::slug($slug);

        if ($normalizedLocale === null || $normalizedSlug === '' || preg_match('/^[a-z][a-z0-9_-]*$/', $namespace) !== 1) {
            throw ValidationException::withMessages(['slug' => [__('api.localization_invalid_slug')]]);
        }

        $language = Language::query()->where('locale', $normalizedLocale)->firstOrFail();

        return DB::transaction(function () use ($language, $namespace, $normalizedSlug, $ownerKey, $ownerType): LocalizedSlug {
            $conflict = LocalizedSlug::query()
                ->where('language_id', $language->getKey())
                ->where('namespace', $namespace)
                ->where('slug', $normalizedSlug)
                ->where(static function ($query) use ($ownerKey, $ownerType): void {
                    $query->where('owner_type', '!=', $ownerType)->orWhere('owner_key', '!=', $ownerKey);
                })
                ->lockForUpdate()
                ->exists();

            if ($conflict) {
                throw ValidationException::withMessages(['slug' => [__('api.localization_slug_conflict')]]);
            }

            return LocalizedSlug::query()->updateOrCreate(
                [
                    'owner_type' => $ownerType,
                    'owner_key' => $ownerKey,
                    'language_id' => $language->getKey(),
                    'namespace' => $namespace,
                ],
                ['slug' => $normalizedSlug],
            );
        });
    }
}
