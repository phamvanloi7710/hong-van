<?php

namespace App\Domain\Analytics;

use App\Models\Setting;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

final class AnalyticsConfiguration
{
    /** @return array{enabled: bool, provider: string, consent_mode: string, marketing_enabled: bool, policy_path: string, policy_version: string, retention_days: int} */
    public function get(): array
    {
        return Cache::rememberForever((string) config('analytics.cache_key'), function (): array {
            $values = Setting::query()
                ->whereHas('group', fn ($query) => $query->where('key', 'analytics'))
                ->pluck('value', 'key');

            return [
                'enabled' => ($values['enabled'] ?? '0') === '1',
                'provider' => (string) ($values['provider'] ?? 'none'),
                'consent_mode' => (string) ($values['consent_mode'] ?? 'opt_in'),
                'marketing_enabled' => ($values['marketing_enabled'] ?? '0') === '1',
                'policy_path' => (string) ($values['policy_path'] ?? '/privacy'),
                'policy_version' => (string) ($values['policy_version'] ?? '2026-08-03'),
                'retention_days' => max(30, min(365, (int) ($values['retention_days'] ?? 180))),
            ];
        });
    }

    public function trackingIdentifier(): ?string
    {
        $value = Setting::query()
            ->where('key', 'tracking_identifier')
            ->whereHas('group', fn ($query) => $query->where('key', 'analytics'))
            ->value('value');

        if (! is_string($value) || $value === '') {
            return null;
        }

        if (str_starts_with($value, 'env:')) {
            $resolved = Env::get(substr($value, 4));

            return is_scalar($resolved) ? (string) $resolved : null;
        }

        return str_starts_with($value, 'enc:')
            ? Crypt::decryptString(substr($value, 4))
            : null;
    }

    public function invalidate(): void
    {
        Cache::forget((string) config('analytics.cache_key'));
    }
}
