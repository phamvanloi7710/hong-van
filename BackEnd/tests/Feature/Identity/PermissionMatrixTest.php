<?php

namespace Tests\Feature\Identity;

use App\Domain\Identity\PermissionRegistry;
use App\Domain\Identity\PermissionService;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class PermissionMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([LanguageSeeder::class, PermissionSeeder::class]);
    }

    public function test_direct_api_denies_user_without_permission(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create();
        $this->actingAs($actor);

        $this->getJson('/api/admin/v1/identity/users')
            ->assertForbidden()
            ->assertJsonPath('success', false);
        $this->postJson('/api/admin/v1/identity/users/'.$target->public_id.'/activate')
            ->assertForbidden();
        $this->postJson('/api/admin/v1/identity/users/'.$target->public_id.'/lock')
            ->assertForbidden();
        $this->postJson('/api/admin/v1/identity/users/'.$target->public_id.'/reset-sessions')
            ->assertForbidden();
    }

    public function test_role_permission_and_user_overrides_follow_deny_then_allow_matrix(): void
    {
        $user = User::factory()->create();
        $role = Role::query()->create(['name' => 'Viewer', 'slug' => 'viewer', 'is_system' => false]);
        $permission = Permission::query()->where('key', 'users.view')->firstOrFail();
        $role->permissions()->attach($permission, ['created_at' => now()]);
        $user->roles()->attach($role, ['created_at' => now()]);
        $this->actingAs($user);

        $this->getJson('/api/admin/v1/identity/users')->assertOk();

        $user->permissionOverrides()->attach($permission, ['is_allowed' => false]);
        $this->getJson('/api/admin/v1/identity/users')->assertForbidden();

        $user->permissionOverrides()->updateExistingPivot($permission->getKey(), ['is_allowed' => true]);
        $this->getJson('/api/admin/v1/identity/users')->assertOk();
    }

    public function test_explicit_allow_grants_without_a_role_and_deny_overrides_every_role_grant(): void
    {
        $permission = Permission::query()->where('key', 'users.view')->firstOrFail();
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->getJson('/api/admin/v1/identity/users')->assertForbidden();

        $user->permissionOverrides()->attach($permission, ['is_allowed' => true]);
        $this->getJson('/api/admin/v1/identity/users')->assertOk();

        $role = Role::query()->create(['name' => 'Viewer', 'slug' => 'override_viewer', 'is_system' => false]);
        $role->permissions()->attach($permission, ['created_at' => now()]);
        $user->roles()->attach($role, ['created_at' => now()]);
        $user->permissionOverrides()->updateExistingPivot($permission->getKey(), ['is_allowed' => false]);

        $this->getJson('/api/admin/v1/identity/users')->assertForbidden();
    }

    public function test_super_admin_bypass_is_explicit_and_audited_once_per_permission_request(): void
    {
        $user = $this->superAdmin();
        $this->actingAs($user);
        Log::spy();

        $this->getJson('/api/admin/v1/identity/users')->assertOk();

        Log::shouldHaveReceived('notice')->once()->withArgs(
            static fn (string $message, array $context): bool => $message === 'Admin identity event.'
                && $context['event'] === 'identity.super_admin_bypass'
                && $context['actor_public_id'] === $user->public_id
                && $context['details']['permission'] === 'users.view',
        );
    }

    public function test_super_admin_bypass_remains_deny_by_default_for_invalid_accounts_and_permissions(): void
    {
        $permissions = app(PermissionService::class);
        $active = $this->superAdmin();

        $this->assertTrue(Gate::forUser($active)->allows('viewAny', User::class));
        $this->assertFalse($permissions->allows($active, 'unknown.view'));

        $inactive = $this->superAdmin();
        $inactive->forceFill(['is_active' => false])->save();
        $this->assertFalse(Gate::forUser($inactive)->allows('viewAny', User::class));

        $locked = $this->superAdmin();
        $locked->forceFill(['locked_at' => now('UTC')])->save();
        $this->assertFalse(Gate::forUser($locked)->allows('viewAny', User::class));

        Permission::query()->where('key', 'users.view')->delete();
        $this->assertFalse(Gate::forUser($active)->allows('viewAny', User::class));
    }

    public function test_super_admin_bypass_has_precedence_over_an_explicit_deny_override(): void
    {
        $user = $this->superAdmin();
        $permission = Permission::query()->where('key', 'users.view')->firstOrFail();
        $user->permissionOverrides()->attach($permission, ['is_allowed' => false]);

        $this->actingAs($user);

        $this->getJson('/api/admin/v1/identity/users')->assertOk();
        $this->assertContains('users.view', app(PermissionService::class)->effectivePermissionKeys($user));
    }

    public function test_inactive_and_locked_users_are_denied_even_when_their_role_grants_permission(): void
    {
        $permission = Permission::query()->where('key', 'users.view')->firstOrFail();
        $role = Role::query()->create(['name' => 'Viewer', 'slug' => 'viewer', 'is_system' => false]);
        $role->permissions()->attach($permission, ['created_at' => now()]);

        foreach ([User::factory()->inactive()->create(), User::factory()->locked()->create()] as $user) {
            $user->roles()->attach($role, ['created_at' => now()]);
            $this->actingAs($user);
            $this->getJson('/api/admin/v1/identity/users')->assertForbidden();
        }
    }

    public function test_permission_seed_is_idempotent_and_all_tables_are_prefixed(): void
    {
        $permissionCount = Permission::query()->count();
        Permission::query()->create([
            'key' => 'legacy.view',
            'module' => 'legacy',
            'action' => 'view',
            'name' => 'Legacy',
            'is_system' => true,
        ]);
        Permission::query()->create([
            'key' => 'reports.view',
            'module' => 'reports',
            'action' => 'view',
            'name' => 'Custom report',
            'is_system' => false,
        ]);

        $this->seed(PermissionSeeder::class);

        $this->assertSame($permissionCount + 1, Permission::query()->count());
        $this->assertDatabaseMissing('hongvan_permissions', ['key' => 'legacy.view']);
        $this->assertDatabaseHas('hongvan_permissions', ['key' => 'reports.view', 'is_system' => false]);
        $this->assertSame(1, Role::query()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->count());
        $this->assertSame($permissionCount, DB::table('hongvan_permission_role')->count());
        $this->assertSame(
            $permissionCount,
            DB::table('hongvan_translation_keys')->where('namespace', 'permissions')->count(),
        );
        $this->assertSame(
            $permissionCount * 3,
            DB::table('hongvan_translation_values')
                ->join('hongvan_translation_keys', 'hongvan_translation_keys.id', '=', 'hongvan_translation_values.translation_key_id')
                ->where('hongvan_translation_keys.namespace', 'permissions')
                ->count(),
        );

        $seededLabels = DB::table('hongvan_translation_values')
            ->join('hongvan_translation_keys', 'hongvan_translation_keys.id', '=', 'hongvan_translation_values.translation_key_id')
            ->join('hongvan_languages', 'hongvan_languages.id', '=', 'hongvan_translation_values.language_id')
            ->where('hongvan_translation_keys.namespace', 'permissions')
            ->get(['hongvan_translation_keys.key', 'hongvan_languages.locale', 'hongvan_translation_values.value'])
            ->groupBy('key');
        foreach (PermissionRegistry::definitions() as $definition) {
            $this->assertEquals(
                $definition['labels'],
                $seededLabels->get($definition['key'])->pluck('value', 'locale')->all(),
                $definition['key'],
            );
        }

        foreach (['roles', 'permissions', 'role_user', 'permission_role', 'user_permission_overrides'] as $table) {
            $this->assertTrue(DB::getSchemaBuilder()->hasTable('hongvan_'.$table));
        }
    }

    public function test_system_permission_api_returns_localized_labels(): void
    {
        $this->actingAs($this->superAdmin());

        $this->withHeader('X-Locale', 'en')
            ->getJson('/api/admin/v1/identity/permissions?filter[module]=users')
            ->assertOk()
            ->assertJsonFragment([
                'key' => 'users.view',
                'name' => 'View users',
                'labels' => [
                    'vi' => 'Xem người dùng',
                    'en' => 'View users',
                    'zh' => '用户查看',
                ],
            ]);
    }

    public function test_last_super_admin_cannot_remove_own_role_lock_or_delete_self(): void
    {
        $user = $this->superAdmin();
        $this->actingAs($user);

        $this->putJson('/api/admin/v1/identity/users/'.$user->public_id, ['role_ids' => []])
            ->assertStatus(409);
        $this->postJson('/api/admin/v1/identity/users/'.$user->public_id.'/lock')
            ->assertForbidden();
        $this->deleteJson('/api/admin/v1/identity/users/'.$user->public_id)
            ->assertForbidden();

        $this->assertDatabaseHas('hongvan_role_user', ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas('hongvan_users', ['id' => $user->getKey(), 'is_active' => true]);
    }

    public function test_lock_revokes_target_sessions_and_tokens(): void
    {
        $actor = $this->superAdmin();
        $target = User::factory()->create();
        $token = $target->createToken('identity-test')->accessToken;
        DB::table('hongvan_sessions')->insert([
            'id' => 'identity-session',
            'user_id' => $target->getKey(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'P11 test',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);
        $this->actingAs($actor);
        Log::spy();

        $response = $this->postJson('/api/admin/v1/identity/users/'.$target->public_id.'/lock')
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseMissing('hongvan_sessions', ['id' => 'identity-session']);
        $this->assertDatabaseMissing('hongvan_personal_access_tokens', ['id' => $token->getKey()]);
        $this->assertDatabaseHas('hongvan_users', ['id' => $target->getKey(), 'is_active' => false]);
        $this->assertNotNull($response->json('data.locked_at'));
        Log::shouldHaveReceived('notice')->withArgs(
            static fn (string $message, array $context): bool => $message === 'Admin identity event.'
                && $context['event'] === 'identity.user.locked'
                && $context['actor_public_id'] === $actor->public_id
                && $context['subject_public_id'] === $target->public_id,
        );
    }

    public function test_activate_and_reset_sessions_are_audited_and_reflect_current_state(): void
    {
        $actor = $this->superAdmin();
        $target = User::factory()->locked()->create(['is_active' => false]);
        $this->actingAs($actor);
        Log::spy();

        $this->postJson('/api/admin/v1/identity/users/'.$target->public_id.'/activate')
            ->assertOk()
            ->assertJsonPath('data.is_active', true)
            ->assertJsonPath('data.locked_at', null);

        Log::shouldHaveReceived('notice')->withArgs(
            static fn (string $message, array $context): bool => $message === 'Admin identity event.'
                && $context['event'] === 'identity.user.activated'
                && $context['actor_public_id'] === $actor->public_id
                && $context['subject_public_id'] === $target->public_id,
        );

        $token = $target->createToken('identity-reset-test')->accessToken;
        DB::table('hongvan_sessions')->insert([
            'id' => 'identity-reset-session',
            'user_id' => $target->getKey(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'T034 test',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);

        $this->postJson('/api/admin/v1/identity/users/'.$target->public_id.'/reset-sessions')
            ->assertOk();

        $this->assertDatabaseMissing('hongvan_sessions', ['id' => 'identity-reset-session']);
        $this->assertDatabaseMissing('hongvan_personal_access_tokens', ['id' => $token->getKey()]);
        Log::shouldHaveReceived('notice')->withArgs(
            static fn (string $message, array $context): bool => $message === 'Admin identity event.'
                && $context['event'] === 'identity.user.sessions_reset'
                && $context['actor_public_id'] === $actor->public_id
                && $context['subject_public_id'] === $target->public_id,
        );
    }

    public function test_super_admin_can_crud_custom_role_and_permission_with_filtering(): void
    {
        $this->actingAs($this->superAdmin());

        $permissionId = $this->postJson('/api/admin/v1/identity/permissions', [
            'module' => 'reports',
            'action' => 'view',
            'name' => 'Xem báo cáo',
        ])->assertCreated()->json('data.public_id');

        $roleId = $this->postJson('/api/admin/v1/identity/roles', [
            'name' => 'Báo cáo',
            'slug' => 'report_viewer',
            'permission_ids' => [$permissionId],
        ])->assertCreated()->json('data.public_id');

        $this->getJson('/api/admin/v1/identity/permissions?filter[module]=reports')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.key', 'reports.view');

        $this->putJson('/api/admin/v1/identity/roles/'.$roleId, ['name' => 'Người xem báo cáo'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Người xem báo cáo');
        $this->deleteJson('/api/admin/v1/identity/roles/'.$roleId)->assertOk();
        $this->deleteJson('/api/admin/v1/identity/permissions/'.$permissionId)->assertOk();
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->firstOrFail();
        $user->roles()->attach($role, ['created_at' => now()]);

        return $user;
    }
}
