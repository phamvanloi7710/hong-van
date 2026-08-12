<?php

namespace Tests\Feature\Identity;

use App\Domain\Identity\PermissionRegistry;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\SuperAdminSeeder;
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

    public function test_database_seed_never_creates_a_super_admin_in_production(): void
    {
        config()->set('identity.super_admin', [
            'email' => 'owner@example.test',
            'name' => 'Project Owner',
            'password' => 'Safe-bootstrap-123!',
        ]);
        $this->seed(PermissionSeeder::class);
        app()->detectEnvironment(static fn (): string => 'production');

        try {
            app(SuperAdminSeeder::class)->run();
        } finally {
            app()->detectEnvironment(static fn (): string => 'testing');
        }

        $this->assertDatabaseMissing('hongvan_users', ['email' => 'owner@example.test']);
    }

    public function test_production_bootstrap_requires_explicit_force(): void
    {
        config()->set('identity.super_admin', [
            'email' => 'owner@example.test',
            'name' => 'Project Owner',
            'password' => 'Safe-bootstrap-123!',
        ]);
        app()->detectEnvironment(static fn (): string => 'production');

        try {
            $this->artisan('identity:bootstrap-super-admin')->assertFailed();
            $this->artisan('identity:bootstrap-super-admin --force')->assertSuccessful();
        } finally {
            app()->detectEnvironment(static fn (): string => 'testing');
        }

        $this->assertDatabaseHas('hongvan_users', ['email' => 'owner@example.test']);
    }
}
