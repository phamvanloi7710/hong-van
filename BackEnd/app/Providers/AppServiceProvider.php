<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', static function (Request $request): Limit {
            $user = $request->user();
            $identifier = $user instanceof User && $user->getAuthIdentifier() !== null
                ? 'user:'.$user->getAuthIdentifier()
                : 'ip:'.$request->ip();

            return Limit::perMinute(max(1, (int) config('api.rate_limit_per_minute', 60)))
                ->by($identifier);
        });

        Gate::define('system_health', static fn (User $user): bool => false);
    }
}
