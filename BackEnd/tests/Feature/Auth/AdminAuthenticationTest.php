<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Laravel\Sanctum\PersonalAccessToken;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array<string, string>
     */
    private array $statefulHeaders = [
        'Accept' => 'application/json',
        'Origin' => 'http://hongvan.local',
        'Referer' => 'http://hongvan.local/admin/login',
    ];

    public function test_sanctum_csrf_cookie_and_session_security_are_configured(): void
    {
        $this->withHeaders($this->statefulHeaders)
            ->get('/sanctum/csrf-cookie')
            ->assertNoContent()
            ->assertCookie('XSRF-TOKEN');

        $this->assertContains('hongvan.local', config('sanctum.stateful'));
        $this->assertSame('hongvan_admin_session', config('session.cookie'));
        $this->assertTrue(config('session.encrypt'));
        $this->assertTrue(config('session.http_only'));
        $this->assertSame('lax', config('session.same_site'));

        $sameOriginRequest = Request::create('/api/admin/v1/auth/login', 'POST', server: [
            'HTTP_ORIGIN' => 'http://hongvan.local',
            'HTTP_REFERER' => 'http://hongvan.local/admin/login',
        ]);
        $externalRequest = Request::create('/api/admin/v1/auth/login', 'POST', server: [
            'HTTP_ORIGIN' => 'https://external.example',
            'HTTP_REFERER' => 'https://external.example/login',
        ]);

        $this->assertTrue(EnsureFrontendRequestsAreStateful::fromFrontend($sameOriginRequest));
        $this->assertFalse(EnsureFrontendRequestsAreStateful::fromFrontend($externalRequest));
    }

    public function test_admin_can_login_restore_session_read_profile_and_logout(): void
    {
        $password = 'Safe-password-123!';
        $user = User::factory()->create(['password' => $password]);
        Log::spy();

        $this->withSession(['before_login' => true]);
        $previousSessionId = session()->getId();

        $this->withHeaders($this->statefulHeaders)
            ->postJson('/api/admin/v1/auth/login', [
                'email' => Str::upper($user->email),
                'password' => $password,
                'remember' => true,
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.public_id', $user->public_id)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonMissingPath('data.id')
            ->assertJsonMissingPath('data.password');

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($previousSessionId, session()->getId());

        $this->withHeaders($this->statefulHeaders)
            ->getJson('/api/admin/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.public_id', $user->public_id);

        Log::shouldHaveReceived('notice')->once()->withArgs(
            static fn (string $message, array $context): bool => $message === 'Admin authentication event.'
                && $context['event'] === 'auth.login.succeeded'
                && $context['user_id'] === $user->getKey()
                && ! array_key_exists('password', $context),
        );

        $this->withHeaders($this->statefulHeaders)
            ->postJson('/api/admin/v1/auth/logout')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertGuest();
        $this->withHeaders($this->statefulHeaders)
            ->getJson('/api/admin/v1/auth/me')
            ->assertUnauthorized();
    }

    public function test_invalid_credentials_are_generic_and_never_audit_the_password(): void
    {
        $password = 'Never-log-this-password!';
        $user = User::factory()->create();
        Log::spy();

        $this->withHeaders($this->statefulHeaders)
            ->postJson('/api/admin/v1/auth/login', [
                'email' => $user->email,
                'password' => $password,
            ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', __('auth.credentials_invalid'));

        $this->assertGuest();
        Log::shouldHaveReceived('notice')->once()->withArgs(
            static fn (string $message, array $context): bool => $message === 'Admin authentication event.'
                && $context['event'] === 'auth.login.failed'
                && ! str_contains(json_encode($context, JSON_THROW_ON_ERROR), $password)
                && ! array_key_exists('email', $context)
                && array_key_exists('email_fingerprint', $context),
        );
    }

    /**
     * @param  array<string, mixed>  $state
     */
    #[DataProvider('unavailableAccountProvider')]
    public function test_inactive_and_locked_accounts_cannot_login(array $state): void
    {
        $password = 'Safe-password-123!';
        $user = User::factory()->create([...$state, 'password' => $password]);

        $this->withHeaders($this->statefulHeaders)
            ->postJson('/api/admin/v1/auth/login', [
                'email' => $user->email,
                'password' => $password,
            ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', __('auth.credentials_invalid'));

        $this->assertGuest();
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public static function unavailableAccountProvider(): array
    {
        return [
            'inactive account' => [['is_active' => false]],
            'locked account' => [['locked_at' => '2026-08-02 00:00:00']],
        ];
    }

    public function test_forgot_password_always_returns_the_same_response(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $existingResponse = $this->withHeaders($this->statefulHeaders)
            ->postJson('/api/admin/v1/auth/forgot-password', ['email' => $user->email])
            ->assertOk();

        $missingResponse = $this->withHeaders($this->statefulHeaders)
            ->postJson('/api/admin/v1/auth/forgot-password', ['email' => 'missing@example.test'])
            ->assertOk();

        $this->assertSame($existingResponse->json('message'), $missingResponse->json('message'));

        Notification::assertSentTo(
            $user,
            ResetPasswordNotification::class,
            static fn (ResetPasswordNotification $notification): bool => str_contains(
                $notification->toMail($user)->actionUrl,
                '/admin/reset-password?token=',
            ),
        );
    }

    public function test_password_reset_changes_password_and_invalidates_sessions_and_tokens(): void
    {
        $user = User::factory()->create();
        $resetToken = Password::broker()->createToken($user);
        $personalToken = $user->createToken('test-session');

        $this->assertInstanceOf(PersonalAccessToken::class, $personalToken->accessToken);

        $sessionId = 'existing-admin-session';
        $user->getConnection()->table('hongvan_sessions')->insert([
            'id' => $sessionId,
            'user_id' => $user->getKey(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'P10 test',
            'payload' => 'test-payload',
            'last_activity' => now()->timestamp,
        ]);

        $newPassword = 'New-safe-password-456!';

        $this->withHeaders($this->statefulHeaders)
            ->postJson('/api/admin/v1/auth/reset-password', [
                'email' => $user->email,
                'token' => $resetToken,
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
            ])
            ->assertOk()
            ->assertJsonPath('message', __('auth.password_reset'));

        $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));
        $this->assertDatabaseMissing('hongvan_sessions', ['id' => $sessionId]);
        $this->assertDatabaseMissing('hongvan_personal_access_tokens', [
            'id' => $personalToken->accessToken->getKey(),
        ]);
    }

    public function test_login_endpoint_is_rate_limited(): void
    {
        config()->set('api.auth_rate_limits.login_per_minute', 2);
        $payload = [
            'email' => 'rate-limit@example.test',
            'password' => 'invalid-password',
        ];

        $this->withHeaders($this->statefulHeaders)
            ->postJson('/api/admin/v1/auth/login', $payload)
            ->assertUnprocessable();
        $this->withHeaders($this->statefulHeaders)
            ->postJson('/api/admin/v1/auth/login', $payload)
            ->assertUnprocessable();
        $this->withHeaders($this->statefulHeaders)
            ->postJson('/api/admin/v1/auth/login', $payload)
            ->assertTooManyRequests()
            ->assertHeader('Retry-After');
    }
}
