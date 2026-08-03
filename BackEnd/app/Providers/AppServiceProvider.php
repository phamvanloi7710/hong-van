<?php

namespace App\Providers;

use App\Domain\Identity\PermissionService;
use App\Models\Brand;
use App\Models\Crop;
use App\Models\CropCategory;
use App\Models\CropSolution;
use App\Models\CropStage;
use App\Models\Media;
use App\Models\Permission;
use App\Models\PersonalAccessToken;
use App\Models\Product;
use App\Models\ProductAttributeDefinition;
use App\Models\ProductCategory;
use App\Models\ProductTag;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\TransportRoute;
use App\Models\TransportServiceArea;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Policies\BrandPolicy;
use App\Policies\CropCategoryPolicy;
use App\Policies\CropPolicy;
use App\Policies\CropSolutionPolicy;
use App\Policies\CropStagePolicy;
use App\Policies\MediaPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\ProductAttributePolicy;
use App\Policies\ProductCategoryPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ProductTagPolicy;
use App\Policies\RolePolicy;
use App\Policies\ServiceCategoryPolicy;
use App\Policies\ServicePolicy;
use App\Policies\TransportationPolicy;
use App\Policies\UserPolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Middleware\TrustHosts;
use Illuminate\Http\Middleware\TrustProxies;
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

        $trustedHosts = (array) config('security.trusted_hosts', []);
        if ($trustedHosts !== []) {
            TrustHosts::at($trustedHosts, subdomains: false);
        }

        $trustedProxies = (array) config('security.trusted_proxies', []);
        if ($trustedProxies !== []) {
            TrustProxies::at($trustedProxies);
            TrustProxies::withHeaders(
                Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
            );
        }

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

        RateLimiter::for('public.forms', static function (Request $request): Limit {
            return Limit::perMinute(max(1, (int) config('security.rate_limits.public_forms_per_minute', 10)))
                ->by('public-form|'.$request->ip());
        });

        RateLimiter::for('uploads', static function (Request $request): Limit {
            return Limit::perMinute(max(1, (int) config('security.rate_limits.uploads_per_minute', 20)))
                ->by(self::securityThrottleKey($request, 'upload'));
        });

        RateLimiter::for('preview.sessions', static function (Request $request): Limit {
            return Limit::perMinute(max(1, (int) config('security.rate_limits.preview_sessions_per_minute', 10)))
                ->by(self::securityThrottleKey($request, 'preview'));
        });

        ResetPassword::createUrlUsing(static function (User $user, string $token): string {
            return rtrim((string) config('app.url'), '/')
                .'/admin/reset-password?token='.rawurlencode($token)
                .'&email='.rawurlencode($user->getEmailForPasswordReset());
        });

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(Media::class, MediaPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(ProductCategory::class, ProductCategoryPolicy::class);
        Gate::policy(Brand::class, BrandPolicy::class);
        Gate::policy(ProductTag::class, ProductTagPolicy::class);
        Gate::policy(ProductAttributeDefinition::class, ProductAttributePolicy::class);
        Gate::policy(CropCategory::class, CropCategoryPolicy::class);
        Gate::policy(Crop::class, CropPolicy::class);
        Gate::policy(CropStage::class, CropStagePolicy::class);
        Gate::policy(CropSolution::class, CropSolutionPolicy::class);
        Gate::policy(Service::class, ServicePolicy::class);
        Gate::policy(ServiceCategory::class, ServiceCategoryPolicy::class);
        Gate::policy(VehicleType::class, TransportationPolicy::class);
        Gate::policy(Vehicle::class, TransportationPolicy::class);
        Gate::policy(TransportRoute::class, TransportationPolicy::class);
        Gate::policy(TransportServiceArea::class, TransportationPolicy::class);

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

    private static function securityThrottleKey(Request $request, string $scope): string
    {
        $user = $request->user();
        $identity = $user instanceof User && $user->getAuthIdentifier() !== null
            ? 'user:'.$user->getAuthIdentifier()
            : 'ip:'.$request->ip();

        return $scope.'|'.$identity;
    }
}
