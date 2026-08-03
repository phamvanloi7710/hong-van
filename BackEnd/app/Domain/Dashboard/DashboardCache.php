<?php

namespace App\Domain\Dashboard;

use Closure;
use Illuminate\Support\Facades\Cache;

final class DashboardCache
{
    private const VERSION_KEY = 'hongvan.dashboard.aggregate.version';

    /**
     * @param  Closure(): array<string, mixed>  $resolver
     * @return array<string, mixed>
     */
    public function remember(string $key, Closure $resolver): array
    {
        $version = (int) Cache::get(self::VERSION_KEY, 1);

        return Cache::remember(
            'hongvan.dashboard.v'.$version.'.'.$key,
            max(15, (int) config('dashboard.cache_ttl_seconds', 60)),
            $resolver,
        );
    }

    public function invalidate(): void
    {
        $version = (int) Cache::get(self::VERSION_KEY, 1);
        Cache::forever(self::VERSION_KEY, $version + 1);
    }
}
