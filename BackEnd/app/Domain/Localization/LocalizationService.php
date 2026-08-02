<?php

namespace App\Domain\Localization;

use App\Models\Language;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class LocalizationService
{
    public function __construct(
        private MissingTranslationReport $missingTranslations,
        private DateTimePresenter $dateTimes,
    ) {}

    /** @return array<string, mixed> */
    public function adminPayload(): array
    {
        $now = CarbonImmutable::now('UTC');

        return [
            'languages' => Language::query()->with('fallbackLanguage:id,locale')->orderBy('sort_order')->get()
                ->map(fn (Language $language): array => $this->serializeLanguage($language))->all(),
            'missing_translations' => $this->missingTranslations->generate(),
            'storage_timezone' => (string) config('localization.storage_timezone', 'UTC'),
            'display_timezone' => (string) config('localization.display_timezone', 'Asia/Ho_Chi_Minh'),
            'generated_at' => $this->dateTimes->api($now),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function updateLanguage(Language $language, array $data): array
    {
        DB::transaction(function () use ($data, $language): void {
            $language = Language::query()->lockForUpdate()->findOrFail($language->getKey());
            $makeDefault = (bool) ($data['is_default'] ?? $language->is_default);
            $makeActive = (bool) ($data['is_active'] ?? $language->is_active);

            if ($language->is_default && (! $makeDefault || ! $makeActive)) {
                throw ValidationException::withMessages(['is_default' => [__('api.localization_default_required')]]);
            }

            if ($makeDefault) {
                Language::query()->whereKeyNot($language->getKey())->update(['is_default' => false]);
                $makeActive = true;
            }

            $fallbackId = $language->fallback_language_id;
            if (array_key_exists('fallback_locale', $data)) {
                $fallbackLocale = $data['fallback_locale'];
                if ($fallbackLocale === $language->locale) {
                    throw ValidationException::withMessages(['fallback_locale' => [__('api.localization_fallback_cycle')]]);
                }

                $fallbackId = is_string($fallbackLocale) && $fallbackLocale !== ''
                    ? Language::query()->where('locale', $fallbackLocale)->value('id')
                    : null;

                $this->ensureFallbackChainIsAcyclic($language, $fallbackId);
            }

            $language->forceFill([
                'is_active' => $makeActive,
                'is_default' => $makeDefault,
                'fallback_language_id' => $fallbackId,
                'sort_order' => $data['sort_order'] ?? $language->sort_order,
            ])->save();
        });

        return $this->adminPayload();
    }

    private function ensureFallbackChainIsAcyclic(Language $language, mixed $fallbackId): void
    {
        $visitedIds = [$language->getKey()];

        while ($fallbackId !== null) {
            if (in_array($fallbackId, $visitedIds, false)) {
                throw ValidationException::withMessages(['fallback_locale' => [__('api.localization_fallback_cycle')]]);
            }

            $visitedIds[] = $fallbackId;
            $fallbackId = Language::query()->whereKey($fallbackId)->value('fallback_language_id');
        }
    }

    /** @return array<string, mixed> */
    private function serializeLanguage(Language $language): array
    {
        return [
            'public_id' => $language->public_id,
            'locale' => $language->locale,
            'name' => $language->name,
            'native_name' => $language->native_name,
            'is_active' => $language->is_active,
            'is_default' => $language->is_default,
            'fallback_locale' => $language->fallbackLanguage?->locale,
            'sort_order' => $language->sort_order,
            'updated_at' => $language->updated_at?->utc()->toISOString(),
        ];
    }
}
