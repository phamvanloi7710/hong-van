<?php

namespace Tests\Feature\Identity;

use App\Domain\Identity\PermissionRegistry;
use App\Domain\Identity\UserManager;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([LanguageSeeder::class, PermissionSeeder::class]);
    }

    public function test_user_endpoints_deny_actor_without_permissions(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create();
        $this->actingAs($actor);

        $this->getJson('/api/admin/v1/identity/users')->assertForbidden();
        $this->postJson('/api/admin/v1/identity/users', [
            'name' => 'Blocked user',
            'email' => 'blocked@example.test',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ])->assertForbidden();
        $this->putJson('/api/admin/v1/identity/users/'.$target->public_id, ['name' => 'Blocked'])
            ->assertForbidden();
        $this->deleteJson('/api/admin/v1/identity/users/'.$target->public_id)->assertForbidden();
        $this->postJson('/api/admin/v1/identity/users/'.$target->public_id.'/activate')->assertForbidden();
        $this->postJson('/api/admin/v1/identity/users/'.$target->public_id.'/lock')->assertForbidden();
        $this->postJson('/api/admin/v1/identity/users/'.$target->public_id.'/reset-sessions')->assertForbidden();

        $this->assertDatabaseMissing('hongvan_users', ['email' => 'blocked@example.test']);
        $this->assertDatabaseHas('hongvan_users', ['id' => $target->getKey(), 'name' => $target->name]);
    }

    public function test_role_and_override_assignment_require_their_own_permissions_and_no_bulk_route_exists(): void
    {
        $actor = $this->actorWithPermissions(['users.create', 'users.update']);
        $target = User::factory()->create();
        $role = Role::query()->create([
            'name' => 'Viewer',
            'slug' => 'user_management_viewer',
            'is_system' => false,
        ]);
        $permission = Permission::query()->where('key', 'users.view')->firstOrFail();
        $this->actingAs($actor);

        $this->postJson('/api/admin/v1/identity/users', [
            'name' => 'Escalated user',
            'email' => 'escalated@example.test',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'role_ids' => [$role->public_id],
        ])->assertForbidden();
        $this->putJson('/api/admin/v1/identity/users/'.$target->public_id, [
            'role_ids' => [$role->public_id],
        ])->assertForbidden();
        $this->putJson('/api/admin/v1/identity/users/'.$target->public_id, [
            'permission_overrides' => [[
                'permission_id' => $permission->public_id,
                'is_allowed' => true,
            ]],
        ])->assertForbidden();
        $this->postJson('/api/admin/v1/identity/users/bulk', [
            'user_ids' => [$target->public_id],
            'action' => 'lock',
        ])->assertStatus(405);

        $this->assertDatabaseMissing('hongvan_users', ['email' => 'escalated@example.test']);
        $this->assertDatabaseMissing('hongvan_role_user', [
            'role_id' => $role->getKey(),
            'user_id' => $target->getKey(),
        ]);
        $this->assertDatabaseMissing('hongvan_user_permission_overrides', [
            'permission_id' => $permission->getKey(),
            'user_id' => $target->getKey(),
        ]);
    }

    public function test_super_admin_can_manage_user_lifecycle_assignments_and_sessions(): void
    {
        $actor = $this->superAdmin();
        $role = Role::query()->create([
            'name' => 'Content viewer',
            'slug' => 'content_viewer',
            'is_system' => false,
        ]);
        $rolePermission = Permission::query()->where('key', 'users.view')->firstOrFail();
        $overridePermission = Permission::query()->where('key', 'roles.view')->firstOrFail();
        $role->permissions()->attach($rolePermission, [
            'granted_by' => $actor->getKey(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->actingAs($actor);

        $userPublicId = $this->postJson('/api/admin/v1/identity/users', [
            'name' => 'Managed User',
            'email' => 'MANAGED@EXAMPLE.TEST',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'is_active' => true,
            'role_ids' => [$role->public_id],
            'permission_overrides' => [[
                'permission_id' => $overridePermission->public_id,
                'is_allowed' => false,
            ]],
        ])->assertCreated()
            ->assertJsonPath('data.email', 'managed@example.test')
            ->assertJsonPath('data.roles.0.public_id', $role->public_id)
            ->assertJsonPath('data.permission_overrides.0.is_allowed', false)
            ->json('data.public_id');

        $target = User::query()->where('public_id', $userPublicId)->firstOrFail();
        $this->assertTrue(Hash::check('SecurePassword123!', $target->password));
        $this->assertDatabaseHas('hongvan_role_user', [
            'role_id' => $role->getKey(),
            'user_id' => $target->getKey(),
            'assigned_by' => $actor->getKey(),
        ]);

        $this->createSessionAndToken($target, 'before-update');
        $this->putJson('/api/admin/v1/identity/users/'.$userPublicId, [
            'name' => 'Updated User',
            'email' => 'UPDATED@EXAMPLE.TEST',
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
            'role_ids' => [],
            'permission_overrides' => [[
                'permission_id' => $overridePermission->public_id,
                'is_allowed' => true,
            ]],
        ])->assertOk()
            ->assertJsonPath('data.name', 'Updated User')
            ->assertJsonPath('data.email', 'updated@example.test')
            ->assertJsonCount(0, 'data.roles')
            ->assertJsonPath('data.permission_overrides.0.is_allowed', true);

        $target = $target->fresh();
        $this->assertNotNull($target);
        $this->assertTrue(Hash::check('NewSecurePassword123!', $target->password));
        $this->assertDatabaseMissing('hongvan_role_user', [
            'role_id' => $role->getKey(),
            'user_id' => $target->getKey(),
        ]);
        $this->assertDatabaseHas('hongvan_user_permission_overrides', [
            'permission_id' => $overridePermission->getKey(),
            'user_id' => $target->getKey(),
            'is_allowed' => true,
            'assigned_by' => $actor->getKey(),
        ]);
        $this->assertSessionAndTokenRevoked('before-update');

        $this->createSessionAndToken($target, 'before-lock');
        $this->postJson('/api/admin/v1/identity/users/'.$userPublicId.'/lock')
            ->assertOk()
            ->assertJsonPath('data.is_active', false);
        $this->assertSessionAndTokenRevoked('before-lock');

        $this->postJson('/api/admin/v1/identity/users/'.$userPublicId.'/activate')
            ->assertOk()
            ->assertJsonPath('data.is_active', true)
            ->assertJsonPath('data.locked_at', null);

        $this->createSessionAndToken($target, 'before-reset');
        $this->withHeader('X-Locale', 'en')
            ->postJson('/api/admin/v1/identity/users/'.$userPublicId.'/reset-sessions')
            ->assertOk()
            ->assertJsonPath('message', 'All login sessions have been revoked.');
        $this->assertSessionAndTokenRevoked('before-reset');

        $this->createSessionAndToken($target, 'before-delete');
        $this->withHeader('X-Locale', 'en')
            ->deleteJson('/api/admin/v1/identity/users/'.$userPublicId)
            ->assertOk()
            ->assertJsonPath('message', 'User deleted.');

        $this->assertDatabaseMissing('hongvan_users', ['id' => $target->getKey()]);
        $this->assertSessionAndTokenRevoked('before-delete');
        foreach (['created', 'updated', 'locked', 'activated', 'sessions_reset', 'deleted'] as $action) {
            $this->assertDatabaseHas('hongvan_audit_logs', [
                'action' => 'identity.user.'.$action,
                'subject_public_id' => $userPublicId,
            ]);
        }
    }

    public function test_last_active_super_admin_cannot_be_demoted_locked_or_deleted(): void
    {
        $target = $this->superAdmin();
        $actor = $this->actorWithPermissions(['users.update', 'users.delete', 'roles.update']);
        $superAdminRole = Role::query()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->firstOrFail();
        $this->actingAs($actor);

        $this->putJson('/api/admin/v1/identity/users/'.$target->public_id, ['role_ids' => []])
            ->assertStatus(409);
        $this->postJson('/api/admin/v1/identity/users/'.$target->public_id.'/lock')
            ->assertStatus(409);
        $this->deleteJson('/api/admin/v1/identity/users/'.$target->public_id)
            ->assertStatus(409);

        $this->assertDatabaseHas('hongvan_users', [
            'id' => $target->getKey(),
            'is_active' => true,
            'locked_at' => null,
        ]);
        $this->assertDatabaseHas('hongvan_role_user', [
            'role_id' => $superAdminRole->getKey(),
            'user_id' => $target->getKey(),
        ]);

        $this->actingAs($target);
        $this->postJson('/api/admin/v1/identity/users/'.$target->public_id.'/lock')->assertForbidden();
        $this->deleteJson('/api/admin/v1/identity/users/'.$target->public_id)->assertForbidden();
    }

    public function test_activate_and_delete_roll_back_when_audit_write_fails(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->locked()->create(['is_active' => false]);
        $failNextAuditInsert = true;
        DB::listen(static function (QueryExecuted $query) use (&$failNextAuditInsert): void {
            if ($failNextAuditInsert && str_contains($query->sql, 'hongvan_audit_logs')) {
                $failNextAuditInsert = false;
                throw new RuntimeException('Forced audit failure.');
            }
        });

        try {
            app(UserManager::class)->activate($actor, $target);
            self::fail('The forced activate audit failure was not raised.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Forced audit failure.', $exception->getMessage());
        }

        $target = $target->fresh();
        $this->assertNotNull($target);
        $this->assertFalse($target->is_active);
        $this->assertNotNull($target->locked_at);

        $failNextAuditInsert = true;
        try {
            app(UserManager::class)->delete($actor, $target);
            self::fail('The forced delete audit failure was not raised.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Forced audit failure.', $exception->getMessage());
        }

        $this->assertFalse($failNextAuditInsert);
        $this->assertDatabaseHas('hongvan_users', [
            'id' => $target->getKey(),
            'is_active' => false,
        ]);
    }

    /** @param list<string> $permissionKeys */
    private function actorWithPermissions(array $permissionKeys): User
    {
        $actor = User::factory()->create();
        $role = Role::query()->create([
            'name' => 'User manager '.strtolower((string) $actor->getKey()),
            'slug' => 'user_manager_'.$actor->getKey(),
            'is_system' => false,
        ]);
        $permissionIds = Permission::query()->whereIn('key', $permissionKeys)->pluck('id')->all();
        $role->permissions()->attach($permissionIds, ['created_at' => now(), 'updated_at' => now()]);
        $actor->roles()->attach($role, ['created_at' => now(), 'updated_at' => now()]);

        return $actor;
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->firstOrFail();
        $user->roles()->attach($role, ['created_at' => now(), 'updated_at' => now()]);

        return $user;
    }

    private function createSessionAndToken(User $user, string $suffix): void
    {
        $user->createToken('user-management-'.$suffix);
        DB::table('hongvan_sessions')->insert([
            'id' => 'user-management-'.$suffix,
            'user_id' => $user->getKey(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'T038 test',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);
    }

    private function assertSessionAndTokenRevoked(string $suffix): void
    {
        $this->assertDatabaseMissing('hongvan_sessions', ['id' => 'user-management-'.$suffix]);
        $this->assertDatabaseMissing('hongvan_personal_access_tokens', ['name' => 'user-management-'.$suffix]);
    }
}
