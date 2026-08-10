<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

final class RateLimiterAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_login_limiter_normalizes_email_and_keeps_other_accounts_separate(): void
    {
        config()->set('api.auth_rate_limits.login_per_minute', 1);
        Route::post('/_t025/auth-login', static fn () => response()->json(['ok' => true]))
            ->middleware('throttle:auth.login');

        $email = 'rate-'.Str::lower((string) Str::ulid()).'@example.test';

        $this->postJson('/_t025/auth-login', ['email' => $email])->assertOk();
        $this->postJson('/_t025/auth-login', ['email' => Str::upper($email)])->assertTooManyRequests();
        $this->postJson('/_t025/auth-login', ['email' => 'other-'.Str::lower((string) Str::ulid()).'@example.test'])->assertOk();
    }

    public function test_api_limiter_is_scoped_to_the_authenticated_user(): void
    {
        config()->set('api.rate_limit_per_minute', 1);
        Route::get('/api/_t025/rate-limit', static fn () => response()->json(['ok' => true]))->middleware('throttle:api');

        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        $this->actingAs($firstUser)->getJson('/api/_t025/rate-limit')->assertOk();
        $this->actingAs($firstUser)->getJson('/api/_t025/rate-limit')
            ->assertTooManyRequests()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', __('api.rate_limited'));
        $this->actingAs($secondUser)->getJson('/api/_t025/rate-limit')->assertOk();
    }

    public function test_public_forms_search_uploads_and_preview_sessions_have_independent_named_limits(): void
    {
        config()->set('security.rate_limits.public_forms_per_minute', 1);
        config()->set('security.rate_limits.public_search_per_minute', 1);
        config()->set('security.rate_limits.uploads_per_minute', 1);
        config()->set('security.rate_limits.preview_sessions_per_minute', 1);

        foreach ([
            ['POST', '/_t025/public-form', 'public.forms'],
            ['GET', '/_t025/public-search', 'public.search'],
            ['POST', '/_t025/upload', 'uploads'],
            ['POST', '/_t025/preview-session', 'preview.sessions'],
        ] as [$method, $uri, $limiter]) {
            Route::match([$method], $uri, static fn () => response()->json(['ok' => true]))
                ->middleware('throttle:'.$limiter);

            $response = $method === 'GET' ? $this->getJson($uri) : $this->postJson($uri);
            $response->assertOk();

            $response = $method === 'GET' ? $this->getJson($uri) : $this->postJson($uri);
            $response->assertTooManyRequests()->assertHeader('Retry-After');
        }
    }

    public function test_preview_views_are_limited_per_user_and_preview_routes_use_the_named_limiter(): void
    {
        config()->set('security.rate_limits.preview_views_per_minute', 1);
        Route::get('/_t025/preview-view', static fn () => response()->json(['ok' => true]))
            ->middleware('throttle:preview.views');

        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        $this->actingAs($firstUser)->getJson('/_t025/preview-view')->assertOk();
        $this->actingAs($firstUser)->getJson('/_t025/preview-view')->assertTooManyRequests();
        $this->actingAs($secondUser)->getJson('/_t025/preview-view')->assertOk();

        foreach (['preview.page-builder', 'preview.theme'] as $routeName) {
            $route = Route::getRoutes()->getByName($routeName);

            $this->assertNotNull($route);
            $this->assertContains('throttle:preview.views', $route->middleware());
        }
    }
}
