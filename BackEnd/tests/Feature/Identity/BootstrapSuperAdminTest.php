<?php

namespace Tests\Feature\Identity;

use App\Domain\Identity\PermissionRegistry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BootstrapSuperAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_requires_safe_environment_credentials_and_is_idempotent(): void
    {
        config()->set('identity.super_admin', [
            'email' => 'owner@example.test',
            'name' => 'Project Owner',
            'password' => 'Safe-bootstrap-123!',
        ]);

        $this->artisan('identity:bootstrap-super-admin')->assertSuccessful();
        $this->artisan('identity:bootstrap-super-admin')->assertSuccessful();

        $user = User::query()->where('email', 'owner@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('Safe-bootstrap-123!', $user->password));
        $this->assertSame(1, User::query()->where('email', 'owner@example.test')->count());
        $this->assertTrue($user->roles()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->exists());
    }

    public function test_command_rejects_missing_password_for_new_account(): void
    {
        config()->set('identity.super_admin', [
            'email' => 'owner@example.test',
            'name' => 'Project Owner',
            'password' => '',
        ]);

        $this->artisan('identity:bootstrap-super-admin')->assertFailed();
        $this->assertDatabaseMissing('hongvan_users', ['email' => 'owner@example.test']);
    }
}
