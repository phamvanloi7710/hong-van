<?php

namespace Database\Seeders;

use App\Domain\Identity\PermissionRegistry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use RuntimeException;

final class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = Str::lower(trim((string) config('identity.super_admin.email')));
        $name = trim((string) config('identity.super_admin.name', 'Super Admin'));
        $password = (string) config('identity.super_admin.password');

        if ($email === '' && $password === '') {
            return;
        }

        $existing = $email !== '' ? User::query()->where('email', $email)->first() : null;
        $validation = Validator::make([
            'email' => $email,
            'name' => $name,
            'password' => $existing === null ? $password : 'existing-password-not-changed',
        ], [
            'email' => ['required', 'email', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:12', 'max:4096'],
        ]);

        if ($validation->fails()) {
            throw new RuntimeException('SUPER_ADMIN_EMAIL, SUPER_ADMIN_NAME and a password of at least 12 characters are required.');
        }

        $role = Role::query()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->firstOrFail();
        $user = $existing ?? new User;

        if ($existing === null) {
            $user->forceFill([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'is_active' => true,
                'locked_at' => null,
            ])->save();
        }

        $user->roles()->syncWithoutDetaching([
            $role->getKey() => ['assigned_by' => null],
        ]);

        foreach ((array) config('admin_preferences.template_defaults', []) as $key => $value) {
            $user->preferences()->firstOrCreate([
                'namespace' => (string) config('admin_preferences.namespace', 'admin'),
                'key' => $key,
            ], ['value' => $value]);
        }
    }
}
