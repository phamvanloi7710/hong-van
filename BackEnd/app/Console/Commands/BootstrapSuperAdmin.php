<?php

namespace App\Console\Commands;

use App\Domain\Identity\IdentityAuditLogger;
use App\Domain\Identity\PermissionRegistry;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class BootstrapSuperAdmin extends Command
{
    protected $signature = 'identity:bootstrap-super-admin {--rotate-password : Rotate password for an existing account} {--force : Allow execution in production}';

    protected $description = 'Create or promote the configured Super Admin without exposing credentials in command history.';

    public function handle(PermissionSeeder $seeder, IdentityAuditLogger $auditLogger): int
    {
        if ($this->laravel->environment('production') && ! $this->option('force')) {
            $this->components->error('Use --force to bootstrap a Super Admin in production.');

            return self::FAILURE;
        }

        $email = Str::lower(trim((string) config('identity.super_admin.email')));
        $name = trim((string) config('identity.super_admin.name', 'Super Admin'));
        $password = (string) config('identity.super_admin.password');
        $existing = $email !== '' ? User::query()->where('email', $email)->first() : null;
        $needsPassword = $existing === null || (bool) $this->option('rotate-password');

        $validation = Validator::make([
            'email' => $email,
            'name' => $name,
            'password' => $needsPassword ? $password : 'existing-password-not-changed',
        ], [
            'email' => ['required', 'email', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:12', 'max:4096'],
        ]);

        if ($validation->fails()) {
            $this->components->error('SUPER_ADMIN_EMAIL, SUPER_ADMIN_NAME and a password of at least 12 characters are required.');

            return self::FAILURE;
        }

        $seeder->run();
        $role = Role::query()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->firstOrFail();

        $user = $existing ?? new User;
        $user->name = $name;
        $user->email = $email;
        $user->is_active = true;
        $user->locked_at = null;

        if ($needsPassword) {
            $user->password = Hash::make($password);
        }

        $user->save();
        $user->roles()->syncWithoutDetaching([
            $role->getKey() => ['assigned_by' => null],
        ]);

        $auditLogger->record(
            $existing === null ? 'identity.super_admin.created' : 'identity.super_admin.promoted',
            subjectType: 'user',
            subjectPublicId: $user->public_id,
        );

        $this->components->info('Super Admin is ready.');

        return self::SUCCESS;
    }
}
