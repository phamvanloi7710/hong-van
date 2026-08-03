<?php

namespace App\Domain\Seo;

use Closure;
use Illuminate\Support\Facades\Cache;

final class SitemapCache
{
    private const VERSION_KEY = 'hongvan.seo.sitemap.version';

    private const HEALTH_KEY = 'hongvan.seo.sitemap.health';

    /** @param Closure(): string $generator */
    public function remember(string $name, Closure $generator): string
    {
        return Cache::remember($this->key($name), now()->addMinutes(15), $generator);
    }

    public function invalidate(): void
    {
        Cache::forever(self::VERSION_KEY, $this->version() + 1);
        Cache::forget(self::HEALTH_KEY);
    }

    /** @param array<string, mixed> $health */
    public function recordHealth(array $health): void
    {
        Cache::forever(self::HEALTH_KEY, $health);
    }

    /** @return array<string, mixed> */
    public function health(): array
    {
        return (array) Cache::get(self::HEALTH_KEY, ['generated_at' => null, 'shard_count' => 0, 'url_count' => 0]);
    }

    private function key(string $name): string
    {
        return 'hongvan.seo.sitemap.v'.$this->version().'.'.$name;
    }

    private function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }
}
