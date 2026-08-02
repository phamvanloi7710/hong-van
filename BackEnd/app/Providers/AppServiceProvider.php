<?php

namespace App\Providers;

use App\Domain\Identity\PermissionService;
use App\Models\Permission;
use App\Models\PersonalAccessToken;
use App\Models\Role;
use App\Models\User;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

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
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        RateLimiter::for('api', static function (Request $request): Limit {
            $user = $request->user();
            $identifier = $user instanceof User && $user->getAuthIdentifier() !== null
                ? 'user:'.$user->getAuthIdentifier()
                : 'ip:'.$request->ip();

            return Limit::perMinute(max(1, (int) config('api.rate_limit_per_minute', 60)))
                ->by($identifier);
        });

        RateLimiter::for('auth.login', static function (Request $request): Limit {
            return Limit::perMinute(max(1, (int) config('api.auth_rate_limits.login_per_minute', 5)))
                ->by(self::authenticationThrottleKey($request));
        });

        RateLimiter::for('auth.password', static function (Request $request): Limit {
            return Limit::perMinute(max(1, (int) config('api.auth_rate_limits.password_per_minute', 3)))
                ->by(self::authenticationThrottleKey($request));
        });

        ResetPassword::createUrlUsing(static function (User $user, string $token): string {
            return rtrim((string) config('app.url'), '/')
                .'/admin/reset-password?token='.rawurlencode($token)
                .'&email='.rawurlencode($user->getEmailForPasswordReset());
        });

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);

        Gate::define(
            'system_health',
            static fn (User $user): bool => app(PermissionService::class)->allows($user, 'system.health'),
        );
    }

    private static function authenticationThrottleKey(Request $request): string
    {
        $email = Str::lower(trim((string) $request->input('email')));

        return hash('sha256', $email).'|'.$request->ip();
    }
}
