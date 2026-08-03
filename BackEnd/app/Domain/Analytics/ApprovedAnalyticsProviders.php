<?php

namespace App\Domain\Analytics;

final class ApprovedAnalyticsProviders
{
    public function isApproved(string $provider): bool
    {
        return is_array(config('analytics.providers.'.$provider));
    }

    public function identifierIsValid(string $provider, string $identifier): bool
    {
        $pattern = config('analytics.providers.'.$provider.'.identifier_pattern');

        return is_string($pattern) && preg_match($pattern, $identifier) === 1;
    }

    /** @return array{script: list<string>, connect: list<string>, image: list<string>} */
    public function cspSources(string $provider): array
    {
        return [
            'script' => $this->stringList(config('analytics.providers.'.$provider.'.script_hosts', [])),
            'connect' => $this->stringList(config('analytics.providers.'.$provider.'.connect_hosts', [])),
            'image' => $this->stringList(config('analytics.providers.'.$provider.'.image_hosts', [])),
        ];
    }

    /** @return list<array{src: string, attributes: array<string, string|bool>}> */
    public function scripts(string $provider, string $identifier): array
    {
        if (! $this->identifierIsValid($provider, $identifier)) {
            return [];
        }

        return match ($provider) {
            'google_analytics_4' => [[
                'src' => 'https://www.googletagmanager.com/gtag/js?id='.rawurlencode($identifier),
                'attributes' => ['async' => true, 'data-provider' => 'google_analytics_4'],
            ]],
            'google_tag_manager' => [[
                'src' => 'https://www.googletagmanager.com/gtm.js?id='.rawurlencode($identifier),
                'attributes' => ['async' => true, 'data-provider' => 'google_tag_manager'],
            ]],
            'plausible' => [[
                'src' => 'https://plausible.io/js/script.js',
                'attributes' => ['defer' => true, 'data-domain' => $identifier, 'data-provider' => 'plausible'],
            ]],
            default => [],
        };
    }

    /** @return list<string> */
    private function stringList(mixed $value): array
    {
        return is_array($value)
            ? array_values(array_filter($value, 'is_string'))
            : [];
    }
}
