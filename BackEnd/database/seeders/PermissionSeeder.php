<?php

namespace Database\Seeders;

use App\Domain\Identity\PermissionRegistry;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

final class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissionIds = [];

        foreach (PermissionRegistry::definitions() as $definition) {
            $permission = Permission::query()->updateOrCreate(
                ['key' => $definition['key']],
                [
                    ...$definition,
                    'description' => null,
                    'is_system' => true,
                ],
            );
            $permissionIds[] = $permission->getKey();
        }

        $role = Role::query()->updateOrCreate(
            ['slug' => PermissionRegistry::SUPER_ADMIN_ROLE],
            [
                'name' => 'Super Admin',
                'description' => 'Quyền quản trị cao nhất của hệ thống.',
                'is_system' => true,
            ],
        );

        $role->permissions()->syncWithPivotValues($permissionIds, [
            'granted_by' => null,
        ]);
    }
}
