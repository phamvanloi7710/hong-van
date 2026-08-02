<?php

namespace App\Domain\Localization;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

final class DateTimePresenter
{
    public function display(CarbonInterface|string $value, ?string $timezone = null): CarbonImmutable
    {
        $instant = $value instanceof CarbonInterface
            ? CarbonImmutable::instance($value)
            : CarbonImmutable::parse($value, 'UTC');

        return $instant->utc()->setTimezone($timezone ?? (string) config('localization.display_timezone'));
    }

    public function api(CarbonInterface|string $value): string
    {
        $instant = $value instanceof CarbonInterface
            ? CarbonImmutable::instance($value)
            : CarbonImmutable::parse($value, 'UTC');

        return $instant->utc()->toISOString();
    }
}
