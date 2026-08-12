<?php

namespace Tests\Feature\Identity;

use App\Domain\Identity\PermissionRegistry;
use App\Domain\Identity\RoleManager;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([LanguageSeeder::class, PermissionSeeder::class]);
    }

    public function test_role_endpoints_deny_user_without_permissions(): void
    {
        $actor = User::factory()->create();
        $role = Role::query()->create([
            'name' => 'Viewer',
            'slug' => 'viewer',
            'is_system' => false,
        ]);
        $this->actingAs($actor);

        $this->getJson('/api/admin/v1/identity/roles')->assertForbidden();
        $this->postJson('/api/admin/v1/identity/roles', [
            'name' => 'Editor',
            'slug' => 'editor',
        ])->assertForbidden();
        $this->putJson('/api/admin/v1/identity/roles/'.$role->public_id, [
            'name' => 'Changed',
        ])->assertForbidden();
        $this->deleteJson('/api/admin/v1/identity/roles/'.$role->public_id)->assertForbidden();

        $this->assertDatabaseHas('hongvan_roles', [
            'id' => $role->getKey(),
            'name' => 'Viewer',
        ]);
    }

    public function test_super_admin_can_crud_role_assign_permissions_and_revoke_affected_sessions(): void
    {
        $actor = $this->superAdmin();
        $initialPermission = Permission::query()->where('key', 'users.view')->firstOrFail();
        $replacementPermission = Permission::query()->where('key', 'roles.view')->firstOrFail();
        $this->actingAs($actor);

        $rolePublicId = $this->postJson('/api/admin/v1/identity/roles', [
            'name' => 'Identity viewer',
            'slug' => 'identity_viewer',
            'description' => 'Read-only identity access',
            'permission_ids' => [$initialPermission->public_id],
        ])->assertCreated()
            ->assertJsonPath('data.is_system', false)
            ->assertJsonPath('data.permissions.0.public_id', $initialPermission->public_id)
            ->json('data.public_id');

        $role = Role::query()->where('public_id', $rolePublicId)->firstOrFail();
        $target = User::factory()->create();
        $target->roles()->attach($role, [
            'assigned_by' => $actor->getKey(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $token = $target->createToken('role-management-test')->accessToken;
        DB::table('hongvan_sessions')->insert([
            'id' => 'role-management-session',
            'user_id' => $target->getKey(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'T037 test',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);

        $this->putJson('/api/admin/v1/identity/roles/'.$rolePublicId, [
            'name' => 'Role viewer',
            'slug' => 'role_viewer',
            'description' => null,
            'permission_ids' => [$replacementPermission->public_id],
        ])->assertOk()
            ->assertJsonPath('data.name', 'Role viewer')
            ->assertJsonPath('data.permissions.0.public_id', $replacementPermission->public_id);

        $this->assertDatabaseMissing('hongvan_permission_role', [
            'role_id' => $role->getKey(),
            'permission_id' => $initialPermission->getKey(),
        ]);
        $this->assertDatabaseHas('hongvan_permission_role', [
            'role_id' => $role->getKey(),
            'permission_id' => $replacementPermission->getKey(),
            'granted_by' => $actor->getKey(),
        ]);
        $this->assertDatabaseMissing('hongvan_sessions', ['id' => 'role-management-session']);
        $this->assertDatabaseMissing('hongvan_personal_access_tokens', ['id' => $token->getKey()]);

        $target->roles()->detach($role);
        $this->withHeader('X-Locale', 'en')
            ->deleteJson('/api/admin/v1/identity/roles/'.$rolePublicId)
            ->assertOk()
            ->assertJsonPath('message', 'Role deleted.');

        $this->assertDatabaseMissing('hongvan_roles', ['id' => $role->getKey()]);
        foreach (['identity.role.created', 'identity.role.updated', 'identity.role.deleted'] as $action) {
            $this->assertDatabaseHas('hongvan_audit_logs', [
                'action' => $action,
                'subject_public_id' => $rolePublicId,
            ]);
        }
    }

    public function test_system_role_cannot_be_updated_or_deleted(): void
    {
        $actor = $this->superAdmin();
        $role = Role::query()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->firstOrFail();
        $originalName = $role->name;
        $originalPermissionCount = $role->permissions()->count();
        $this->actingAs($actor);

        $this->putJson('/api/admin/v1/identity/roles/'.$role->public_id, [
            'name' => 'Compromised administrator',
            'permission_ids' => [],
        ])->assertStatus(409);
        $this->deleteJson('/api/admin/v1/identity/roles/'.$role->public_id)->assertForbidden();

        $this->assertDatabaseHas('hongvan_roles', [
            'id' => $role->getKey(),
            'name' => $originalName,
            'is_system' => true,
        ]);
        $this->assertSame($originalPermissionCount, $role->fresh()->permissions()->count());
    }

    public function test_assigned_role_cannot_be_deleted(): void
    {
        $actor = $this->superAdmin();
        $role = Role::query()->create([
            'name' => 'Assigned role',
            'slug' => 'assigned_role',
            'is_system' => false,
        ]);
        $target = User::factory()->create();
        $target->roles()->attach($role, [
            'assigned_by' => $actor->getKey(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->actingAs($actor);

        $this->deleteJson('/api/admin/v1/identity/roles/'.$role->public_id)->assertStatus(409);

        $this->assertDatabaseHas('hongvan_roles', ['id' => $role->getKey()]);
        $this->assertDatabaseHas('hongvan_role_user', [
            'role_id' => $role->getKey(),
            'user_id' => $target->getKey(),
        ]);
    }

    public function test_delete_rolls_back_role_and_permissions_when_audit_write_fails(): void
    {
        $actor = User::factory()->create();
        $permission = Permission::query()->where('key', 'roles.view')->firstOrFail();
        $role = Role::query()->create([
            'name' => 'Transactional role',
            'slug' => 'transactional_role',
            'is_system' => false,
        ]);
        $role->permissions()->attach($permission, [
            'granted_by' => $actor->getKey(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $failNextAuditInsert = true;
        DB::listen(static function (QueryExecuted $query) use (&$failNextAuditInsert): void {
            if ($failNextAuditInsert && str_contains($query->sql, 'hongvan_audit_logs')) {
                $failNextAuditInsert = false;
                throw new RuntimeException('Forced audit failure.');
            }
        });

        try {
            app(RoleManager::class)->delete($actor, $role);
            self::fail('The forced audit failure was not raised.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Forced audit failure.', $exception->getMessage());
        }

        $this->assertFalse($failNextAuditInsert);
        $this->assertDatabaseHas('hongvan_roles', ['id' => $role->getKey()]);
        $this->assertDatabaseHas('hongvan_permission_role', [
            'role_id' => $role->getKey(),
            'permission_id' => $permission->getKey(),
        ]);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->firstOrFail();
        $user->roles()->attach($role, ['created_at' => now(), 'updated_at' => now()]);

        return $user;
    }
}
