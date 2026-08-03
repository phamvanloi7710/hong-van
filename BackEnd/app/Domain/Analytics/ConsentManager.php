<?php

namespace App\Domain\Analytics;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Cookie as HttpCookie;

final readonly class ConsentManager
{
    public function __construct(
        private AnalyticsConfiguration $configuration,
        private ApprovedAnalyticsProviders $providers,
    ) {}

    /** @return array<string, mixed> */
    public function payload(Request $request): array
    {
        $configuration = $this->configuration->get();
        $current = $this->current($request, $configuration);

        return [
            'enabled' => $configuration['enabled'],
            'mode' => $configuration['consent_mode'],
            'policy' => [
                'url' => url($configuration['policy_path']),
                'version' => $configuration['policy_version'],
            ],
            'banner' => [
                'title' => __('consent.banner.title'),
                'message' => __('consent.banner.message'),
                'accept_all' => __('consent.banner.accept_all'),
                'necessary_only' => __('consent.banner.necessary_only'),
                'preferences' => __('consent.banner.preferences'),
                'save' => __('consent.banner.save'),
                'revoke' => __('consent.banner.revoke'),
                'policy_link' => __('consent.banner.policy_link'),
            ],
            'categories' => [
                'necessary' => ['required' => true, 'available' => true, 'label' => __('consent.categories.necessary.label'), 'description' => __('consent.categories.necessary.description')],
                'analytics' => ['required' => false, 'available' => $configuration['enabled'], 'label' => __('consent.categories.analytics.label'), 'description' => __('consent.categories.analytics.description')],
                'marketing' => ['required' => false, 'available' => $configuration['enabled'] && $configuration['marketing_enabled'], 'label' => __('consent.categories.marketing.label'), 'description' => __('consent.categories.marketing.description')],
            ],
            'current' => $current,
            'scripts' => $this->scripts($configuration, $current),
            'events' => [
                'lead_submit' => ['allowed_parameters' => ['lead_type']],
                'product_view' => ['allowed_parameters' => ['product_public_id', 'locale']],
            ],
        ];
    }

    /** @param array{analytics: bool, marketing: bool, policy_version: string} $preferences */
    public function grantCookie(Request $request, array $preferences): HttpCookie
    {
        $configuration = $this->configuration->get();
        $payload = [
            'necessary' => true,
            'analytics' => $configuration['enabled'] && $preferences['analytics'],
            'marketing' => $configuration['enabled'] && $configuration['marketing_enabled'] && $preferences['marketing'],
            'policy_version' => $configuration['policy_version'],
            'issued_at' => now()->utc()->getTimestamp(),
        ];

        return Cookie::make(
            name: $this->cookieName(),
            value: Crypt::encryptString(json_encode($payload, JSON_THROW_ON_ERROR)),
            minutes: $configuration['retention_days'] * 1440,
            path: '/',
            secure: $request->isSecure(),
            httpOnly: true,
            raw: false,
            sameSite: (string) config('analytics.cookie.same_site', 'lax'),
        );
    }

    public function revokeCookie(): HttpCookie
    {
        return Cookie::forget($this->cookieName(), '/');
    }

    public function hasAnalyticsConsent(Request $request): bool
    {
        $configuration = $this->configuration->get();

        return $configuration['enabled'] && $this->current($request, $configuration)['analytics'];
    }

    /**
     * @param  array{enabled: bool, provider: string, consent_mode: string, marketing_enabled: bool, policy_path: string, policy_version: string, retention_days: int}  $configuration
     * @return array{necessary: bool, analytics: bool, marketing: bool, decided: bool}
     */
    private function current(Request $request, array $configuration): array
    {
        $empty = ['necessary' => true, 'analytics' => false, 'marketing' => false, 'decided' => false];
        $encrypted = $request->cookie($this->cookieName());

        if (! is_string($encrypted) || $encrypted === '') {
            return $empty;
        }

        try {
            $decoded = json_decode(Crypt::decryptString($encrypted), true, 16, JSON_THROW_ON_ERROR);
        } catch (DecryptException|\JsonException) {
            return $empty;
        }

        if (! is_array($decoded) || ($decoded['policy_version'] ?? null) !== $configuration['policy_version']) {
            return $empty;
        }

        return [
            'necessary' => true,
            'analytics' => $configuration['enabled'] && ($decoded['analytics'] ?? false) === true,
            'marketing' => $configuration['enabled'] && $configuration['marketing_enabled'] && ($decoded['marketing'] ?? false) === true,
            'decided' => true,
        ];
    }

    /**
     * @param  array{enabled: bool, provider: string, consent_mode: string, marketing_enabled: bool, policy_path: string, policy_version: string, retention_days: int}  $configuration
     * @param  array{necessary: bool, analytics: bool, marketing: bool, decided: bool}  $current
     * @return list<array{src: string, attributes: array<string, string|bool>}>
     */
    private function scripts(array $configuration, array $current): array
    {
        if (! $configuration['enabled'] || ! $current['analytics']) {
            return [];
        }

        $identifier = $this->configuration->trackingIdentifier();

        return is_string($identifier)
            ? $this->providers->scripts($configuration['provider'], $identifier)
            : [];
    }

    private function cookieName(): string
    {
        return (string) config('analytics.cookie.name', 'hongvan_consent');
    }
}
