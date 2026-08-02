<?php

namespace Tests\Feature\Audit;

use App\Domain\Audit\AuditTrail;
use App\Domain\Identity\AuthenticationAuditLogger;
use App\Domain\Identity\IdentityAuditLogger;
use App\Domain\Settings\CompanySettingsAuditLogger;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use LogicException;
use Tests\TestCase;

class AuditFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    public function test_audit_is_append_only_hashed_and_recursively_redacted(): void
    {
        $actor = User::factory()->create();
        $request = Request::create('/api/admin/v1/security-check', 'POST', server: [
            'REMOTE_ADDR' => '203.0.113.10',
            'HTTP_USER_AGENT' => 'Audit test browser',
        ]);
        $audit = app(AuditTrail::class)->record(
            'security.redaction.checked',
            $actor,
            'user',
            $actor->public_id,
            before: ['name' => 'Before', 'password' => 'before-secret'],
            after: ['name' => 'After', 'nested' => ['api_token' => 'token-secret', 'safe' => true]],
            metadata: ['cookie' => 'cookie-secret', 'upload' => ['contents' => 'file-secret']],
            request: $request,
        );

        $stored = AuditLog::query()->findOrFail($audit->getKey());
        $encoded = json_encode($stored->toArray(), JSON_THROW_ON_ERROR);

        $this->assertSame('[REDACTED]', $stored->before_data['password']);
        $this->assertSame('[REDACTED]', $stored->after_data['nested']['api_token']);
        $this->assertSame('[REDACTED]', $stored->metadata['cookie']);
        $this->assertStringNotContainsString('before-secret', $encoded);
        $this->assertStringNotContainsString('token-secret', $encoded);
        $this->assertStringNotContainsString('cookie-secret', $encoded);
        $this->assertStringNotContainsString('file-secret', $encoded);
        $this->assertSame(64, strlen((string) $stored->ip_hash));
        $this->assertSame(64, strlen((string) $stored->user_agent_hash));
        $this->assertTrue(Str::isUlid($stored->request_id));

        try {
            $stored->forceFill(['action' => 'security.audit.tampered'])->save();
            $this->fail('Audit update should have been rejected.');
        } catch (LogicException) {
            $this->assertDatabaseHas('hongvan_audit_logs', ['id' => $stored->getKey(), 'action' => 'security.redaction.checked']);
        }

        try {
            AuditLog::query()->findOrFail($stored->getKey())->delete();
            $this->fail('Audit delete should have been rejected.');
        } catch (LogicException) {
            $this->assertDatabaseHas('hongvan_audit_logs', ['id' => $stored->getKey()]);
        }
    }

    public function test_auth_identity_and_settings_loggers_persist_to_the_shared_audit_trail(): void
    {
        $actor = User::factory()->create();
        $request = Request::create('/api/admin/v1/foundation', 'POST', server: ['REMOTE_ADDR' => '127.0.0.1']);

        app(AuthenticationAuditLogger::class)->loginSucceeded($request, $actor, $actor->email);
        app(IdentityAuditLogger::class)->record('identity.user.updated', $actor, 'user', $actor->public_id, ['fields' => 'name'], $request);
        app(CompanySettingsAuditLogger::class)->record('settings.group.updated', $actor, 'email', ['smtp_password'], $request);

        $this->assertDatabaseHas('hongvan_audit_logs', ['action' => 'auth.login.succeeded', 'actor_public_id' => $actor->public_id]);
        $this->assertDatabaseHas('hongvan_audit_logs', ['action' => 'identity.user.updated', 'subject_public_id' => $actor->public_id]);
        $this->assertDatabaseHas('hongvan_audit_logs', ['action' => 'settings.group.updated', 'subject_type' => 'company_settings']);
        $settingsAudit = AuditLog::query()->where('action', 'settings.group.updated')->firstOrFail();
        $this->assertSame('smtp_password', $settingsAudit->after_data['changed_keys'][0]);
    }

    public function test_audit_api_requires_permission_filters_allowlisted_fields_and_has_no_mutation_route(): void
    {
        $actor = User::factory()->create();
        $request = Request::create('/audit-fixture', 'POST');
        app(AuditTrail::class)->record('identity.user.updated', $actor, 'user', $actor->public_id, request: $request);
        app(AuditTrail::class)->record('settings.group.updated', $actor, 'company_settings', 'company', request: $request);

        $viewer = User::factory()->create();
        $this->actingAs($viewer)->getJson('/api/admin/v1/audit-logs')->assertForbidden();

        $permission = Permission::query()->where('key', 'audit.view')->firstOrFail();
        $viewer->permissionOverrides()->attach($permission, ['is_allowed' => true]);

        $this->getJson('/api/admin/v1/audit-logs?filter[action]=identity.user&filter[subject_type]=user')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.action', 'identity.user.updated')
            ->assertJsonPath('data.0.subject_public_id', $actor->public_id)
            ->assertJsonPath('meta.pagination.total', 1);

        $this->getJson('/api/admin/v1/audit-logs?filter[unsafe]=value')
            ->assertUnprocessable();
        $this->postJson('/api/admin/v1/audit-logs', [])->assertMethodNotAllowed();
        $this->deleteJson('/api/admin/v1/audit-logs/'.$actor->public_id)->assertNotFound();
    }
}
