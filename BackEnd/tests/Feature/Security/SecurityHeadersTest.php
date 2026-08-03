<?php

namespace Tests\Feature\Security;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_default_headers_deny_framing_and_preview_allows_only_same_origin_framing(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Content-Security-Policy')
            ->assertHeaderMissing('Strict-Transport-Security');

        Route::get('/preview/p15-frame-check', static fn () => response('preview'));

        $this->get('/preview/p15-frame-check')
            ->assertOk()
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Content-Security-Policy');

        $this->assertStringContainsString("script-src 'self' 'nonce-", (string) $this->get('/')->headers->get('Content-Security-Policy'));
    }

    public function test_hsts_is_only_added_for_secure_production_requests(): void
    {
        config()->set('app.env', 'production');

        $this->get('http://hongvan.local/')
            ->assertHeaderMissing('Strict-Transport-Security');
        $this->get('https://hongvan.local/')
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_foundation_rate_limiters_are_registered_and_enforced(): void
    {
        config()->set('security.rate_limits.public_forms_per_minute', 1);
        config()->set('security.rate_limits.uploads_per_minute', 1);
        config()->set('security.rate_limits.preview_sessions_per_minute', 1);

        foreach ([
            '/_p15/public-form' => 'public.forms',
            '/_p15/upload' => 'uploads',
            '/_p15/preview-session' => 'preview.sessions',
        ] as $uri => $limiter) {
            Route::post($uri, static fn () => response()->json(['ok' => true]))->middleware('throttle:'.$limiter);
            $this->postJson($uri)->assertOk();
            $this->postJson($uri)->assertTooManyRequests()->assertHeader('Retry-After');
        }
    }

    public function test_trusted_network_logging_and_retention_configuration_is_explicit(): void
    {
        $this->assertContains('^hongvan\\.local$', config('security.trusted_hosts'));
        $this->assertIsArray(config('security.trusted_proxies'));
        $this->assertSame('security', config('security.logging.channel'));
        $this->assertSame(365, config('security.audit.retention_days'));
        $this->assertSame('daily', config('logging.channels.security.driver'));
        $this->assertSame(90, config('logging.channels.security.days'));
    }
}
