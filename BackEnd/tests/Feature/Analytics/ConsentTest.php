<?php

namespace Tests\Feature\Analytics;

use App\Domain\Analytics\AnalyticsEventPayload;
use App\Domain\Analytics\AnalyticsScriptRenderer;
use App\Domain\Analytics\ConsentManager;
use App\Domain\Identity\PermissionRegistry;
use App\Http\Middleware\SecurityHeaders;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\CompanySettingsSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ConsentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([PermissionSeeder::class, CompanySettingsSeeder::class]);
    }

    public function test_disabled_analytics_returns_localized_banner_without_external_scripts(): void
    {
        $this->getJson('/api/public/v1/consent', ['X-Locale' => 'vi'])
            ->assertOk()
            ->assertJsonPath('data.enabled', false)
            ->assertJsonPath('data.current.necessary', true)
            ->assertJsonPath('data.current.analytics', false)
            ->assertJsonPath('data.current.decided', false)
            ->assertJsonPath('data.banner.title', 'Quyền riêng tư của bạn')
            ->assertJsonCount(0, 'data.scripts');
    }

    public function test_settings_are_masked_and_reject_arbitrary_provider_or_script_keys(): void
    {
        $this->actingAs($this->superAdmin());

        $this->putJson('/api/admin/v1/settings/groups/analytics', [
            'values' => ['enabled' => true, 'provider' => 'custom', 'tracking_identifier' => 'https://evil.test/payload.js'],
        ])->assertUnprocessable()->assertJsonValidationErrors(['values.provider']);

        $this->putJson('/api/admin/v1/settings/groups/analytics', [
            'values' => ['script_url' => 'https://evil.test/payload.js'],
        ])->assertUnprocessable()->assertJsonValidationErrors(['values']);

        $response = $this->putJson('/api/admin/v1/settings/groups/analytics', [
            'values' => ['enabled' => true, 'provider' => 'google_analytics_4', 'tracking_identifier' => 'G-ABC12345'],
        ])->assertOk();

        $tracking = collect($response->json('data.settings'))->firstWhere('key', 'tracking_identifier');
        $this->assertIsArray($tracking);
        $this->assertNull($tracking['value']);
        $this->assertTrue($tracking['has_value']);
        $this->assertStringNotContainsString('G-ABC12345', $response->getContent());
    }

    public function test_consent_is_persisted_revocable_and_scripts_are_released_only_after_opt_in(): void
    {
        $this->enableAnalytics();

        $before = $this->getJson('/api/public/v1/consent')
            ->assertOk()
            ->assertJsonCount(0, 'data.scripts');
        $this->assertStringNotContainsString('googletagmanager.com', (string) $before->headers->get('Content-Security-Policy'));

        $granted = $this->putJson('/api/public/v1/consent', [
            'analytics' => true,
            'marketing' => false,
            'policy_version' => '2026-08-03',
        ])->assertOk()
            ->assertJsonPath('data.current.analytics', true)
            ->assertJsonPath('data.scripts.0.attributes.data-provider', 'google_analytics_4');

        $cookie = collect($granted->headers->getCookies())->first(fn ($cookie) => $cookie->getName() === 'hongvan_consent');
        $this->assertNotNull($cookie);
        $this->assertTrue($cookie->isHttpOnly());

        $persistedRequest = Request::create('/api/public/v1/consent', 'GET');
        $persistedRequest->cookies->set('hongvan_consent', $cookie->getValue());
        $persisted = app(ConsentManager::class)->payload($persistedRequest);
        $this->assertTrue($persisted['current']['decided']);
        $this->assertTrue($persisted['current']['analytics']);
        $this->assertCount(1, $persisted['scripts']);

        $secured = app(SecurityHeaders::class)->handle($persistedRequest, static fn () => response('ok'));
        $csp = (string) $secured->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("'nonce-", $csp);
        $this->assertStringContainsString('https://www.googletagmanager.com', $csp);

        $this->deleteJson('/api/public/v1/consent')
            ->assertOk()
            ->assertJsonPath('data.current.analytics', false)
            ->assertJsonPath('data.current.decided', false)
            ->assertJsonCount(0, 'data.scripts')
            ->assertCookieExpired('hongvan_consent');
    }

    public function test_script_renderer_requires_a_nonce_and_event_hooks_exclude_pii(): void
    {
        $renderer = app(AnalyticsScriptRenderer::class);
        $this->assertSame('', $renderer->render([['src' => 'https://plausible.io/js/script.js', 'attributes' => ['defer' => true]]], ''));
        $html = $renderer->render([['src' => 'https://plausible.io/js/script.js', 'attributes' => ['defer' => true, 'data-domain' => 'hongvan.local']]], 'safe-nonce');
        $this->assertStringContainsString('nonce="safe-nonce"', $html);
        $this->assertStringContainsString('src="https://plausible.io/js/script.js"', $html);

        $events = app(AnalyticsEventPayload::class);
        $lead = $events->leadSubmitted('quote<script>alert(1)</script>');
        $product = $events->productViewed('01K1VV6E6JX8HM7QGQF2VMY7RS', 'zh');
        $encoded = json_encode([$lead, $product], JSON_THROW_ON_ERROR);
        $this->assertSame(['lead_type'], array_keys($lead['parameters']));
        $this->assertSame(['product_public_id', 'locale'], array_keys($product['parameters']));
        $this->assertStringNotContainsString('email', $encoded);
        $this->assertStringNotContainsString('phone', $encoded);
        $this->assertStringNotContainsString('<script>', $encoded);
    }

    private function enableAnalytics(): void
    {
        $this->actingAs($this->superAdmin());
        $this->putJson('/api/admin/v1/settings/groups/analytics', [
            'values' => [
                'enabled' => true,
                'provider' => 'google_analytics_4',
                'tracking_identifier' => 'G-ABC12345',
                'consent_mode' => 'opt_in',
                'marketing_enabled' => false,
                'policy_path' => '/privacy',
                'policy_version' => '2026-08-03',
                'retention_days' => 180,
            ],
        ])->assertOk();
        $this->app['auth']->forgetGuards();
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->firstOrFail();
        $user->roles()->attach($role, ['created_at' => now()]);

        return $user;
    }
}
