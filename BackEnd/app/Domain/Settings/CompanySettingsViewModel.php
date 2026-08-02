<?php

namespace App\Domain\Settings;

use Illuminate\Contracts\Support\Arrayable;

/** @implements Arrayable<string, mixed> */
final readonly class CompanySettingsViewModel implements Arrayable
{
    public function __construct(private CompanySettingsService $settings) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->settings->publicPayload();
    }

    public function get(string $group, string $key, mixed $default = null): mixed
    {
        return data_get($this->toArray(), 'settings.'.$group.'.'.$key, $default);
    }
}
