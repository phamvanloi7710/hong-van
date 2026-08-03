<?php

namespace App\Domain\PublicSite;

use App\Domain\Localization\LocaleRegistry;
use App\Domain\Settings\CompanySettingsService;
use Illuminate\Support\Facades\App;

final readonly class PublicSiteViewData
{
    public function __construct(
        private CompanySettingsService $settings,
        private LocaleRegistry $locales,
    ) {}

    /** @return array<string, mixed> */
    public function forPage(string $page): array
    {
        $payload = $this->settings->publicPayload();
        $locale = App::getLocale();
        $companyName = (string) $this->setting($payload, 'company', 'company_name');
        $shortName = (string) $this->setting($payload, 'company', 'short_name');
        $siteTitle = (string) $this->setting($payload, 'seo_defaults', 'site_title');
        $pageTitle = (string) trans("public.pages.{$page}.title");

        return [
            'currentPage' => $page,
            'pageTitle' => $pageTitle,
            'metaTitle' => $page === 'home' ? ($siteTitle ?: $companyName) : $pageTitle.' — '.($siteTitle ?: $companyName),
            'metaDescription' => (string) $this->setting($payload, 'seo_defaults', 'meta_description'),
            'site' => [
                'company_name' => $companyName,
                'short_name' => $shortName ?: $companyName,
                'legal_name' => (string) $this->setting($payload, 'legal', 'legal_name'),
                'tax_code' => (string) $this->setting($payload, 'legal', 'tax_code'),
                'contact_channels' => $payload['contact_channels'] ?? [],
                'social_links' => $payload['social_links'] ?? [],
            ],
            'navigation' => $this->navigation($locale),
            'localeLinks' => $this->localeLinks($page),
        ];
    }

    /** @param array<string, mixed> $payload */
    private function setting(array $payload, string $group, string $key): mixed
    {
        $value = data_get($payload, "settings.{$group}.{$key}");

        return $value ?? config("company_settings.groups.{$group}.settings.{$key}.default");
    }

    /** @return list<array{label: string, url: string, active: bool}> */
    private function navigation(string $locale): array
    {
        return [
            ['label' => (string) trans('public.navigation.home'), 'url' => $this->pageUrl('home', $locale), 'active' => request()->routeIs('public.home', 'public.localized-home')],
            ['label' => (string) trans('public.navigation.privacy'), 'url' => $this->pageUrl('privacy', $locale), 'active' => request()->routeIs('public.privacy', 'public.localized-privacy')],
            ['label' => (string) trans('public.navigation.terms'), 'url' => $this->pageUrl('terms', $locale), 'active' => request()->routeIs('public.terms', 'public.localized-terms')],
        ];
    }

    /** @return list<array{locale: string, label: string, url: string, active: bool}> */
    private function localeLinks(string $page): array
    {
        return array_map(fn (string $locale): array => [
            'locale' => $locale,
            'label' => (string) trans("public.locales.{$locale}"),
            'url' => $this->pageUrl($page, $locale),
            'active' => App::getLocale() === $locale,
        ], $this->locales->supportedLocales());
    }

    private function pageUrl(string $page, string $locale): string
    {
        $defaultLocale = $this->locales->defaultLocale();
        $baseRoute = match ($page) {
            'privacy' => 'public.privacy',
            'terms' => 'public.terms',
            default => 'public.home',
        };

        if ($locale === $defaultLocale) {
            return route($baseRoute);
        }

        return route(str_replace('public.', 'public.localized-', $baseRoute), ['locale' => $locale]);
    }
}
