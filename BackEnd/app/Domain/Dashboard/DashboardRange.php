<?php

namespace App\Domain\Dashboard;

use Carbon\CarbonImmutable;

final readonly class DashboardRange
{
    public function __construct(
        public CarbonImmutable $fromUtc,
        public CarbonImmutable $toUtc,
        public string $timezone,
    ) {}

    /** @param array<string, mixed> $values */
    public static function fromValidated(array $values): self
    {
        $timezone = (string) ($values['timezone'] ?? config('dashboard.timezone', 'Asia/Ho_Chi_Minh'));
        $from = CarbonImmutable::parse((string) ($values['from'] ?? now($timezone)->subDays(29)->toDateString()), $timezone)->startOfDay();
        $to = CarbonImmutable::parse((string) ($values['to'] ?? now($timezone)->toDateString()), $timezone)->endOfDay();

        return new self($from->utc(), $to->utc(), $timezone);
    }

    /** @return array{from: string, to: string, timezone: string} */
    public function toArray(): array
    {
        return [
            'from' => $this->fromUtc->setTimezone($this->timezone)->toDateString(),
            'to' => $this->toUtc->setTimezone($this->timezone)->toDateString(),
            'timezone' => $this->timezone,
        ];
    }
}
