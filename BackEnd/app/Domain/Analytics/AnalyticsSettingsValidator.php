<?php

namespace App\Domain\Analytics;

use App\Models\Setting;
use Illuminate\Validation\ValidationException;

final readonly class AnalyticsSettingsValidator
{
    public function __construct(private ApprovedAnalyticsProviders $providers) {}

    /** @param array<string, mixed> $values */
    public function validate(string $groupKey, array $values): void
    {
        if ($groupKey !== 'analytics') {
            return;
        }

        $stored = Setting::query()
            ->whereHas('group', fn ($query) => $query->where('key', 'analytics'))
            ->pluck('value', 'key');
        $enabled = (bool) ($values['enabled'] ?? (($stored['enabled'] ?? '0') === '1'));
        $provider = (string) ($values['provider'] ?? ($stored['provider'] ?? 'none'));
        $newIdentifier = trim((string) ($values['tracking_identifier'] ?? ''));
        $hasStoredIdentifier = is_string($stored['tracking_identifier'] ?? null) && $stored['tracking_identifier'] !== '';
        $providerChanged = array_key_exists('provider', $values) && $provider !== (string) ($stored['provider'] ?? 'none');

        if (! $enabled) {
            return;
        }

        if (! $this->providers->isApproved($provider)) {
            throw ValidationException::withMessages(['values.provider' => [__('consent.validation.provider')]]);
        }

        if ($newIdentifier === '' && (! $hasStoredIdentifier || $providerChanged)) {
            throw ValidationException::withMessages(['values.tracking_identifier' => [__('consent.validation.identifier_required')]]);
        }

        if ($newIdentifier !== '' && ! str_starts_with($newIdentifier, 'env:') && ! $this->providers->identifierIsValid($provider, $newIdentifier)) {
            throw ValidationException::withMessages(['values.tracking_identifier' => [__('consent.validation.identifier_invalid')]]);
        }
    }
}
