<?php

namespace Database\Seeders;

use App\Domain\Identity\PermissionRegistry;
use App\Models\Language;
use App\Models\Permission;
use App\Models\Role;
use App\Models\TranslationKey;
use App\Models\TranslationValue;
use Illuminate\Database\Seeder;

final class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = PermissionRegistry::definitions();
        $registryKeys = PermissionRegistry::keys();
        $permissionIds = [];
        $languageIds = Language::query()
            ->whereIn('locale', ['vi', 'en', 'zh'])
            ->pluck('id', 'locale');

        foreach ($definitions as $definition) {
            $permission = Permission::query()->updateOrCreate(
                ['key' => $definition['key']],
                [
                    'module' => $definition['module'],
                    'action' => $definition['action'],
                    'name' => $definition['name'],
                    'description' => null,
                    'is_system' => true,
                ],
            );
            $permissionIds[] = $permission->getKey();

            $translationKey = TranslationKey::query()->updateOrCreate(
                ['namespace' => 'permissions', 'key' => $definition['key']],
                [
                    'description' => 'Nhãn đa ngôn ngữ cho permission hệ thống '.$definition['key'].'.',
                    'is_system' => true,
                ],
            );

            foreach ($definition['labels'] as $locale => $label) {
                $languageId = $languageIds->get($locale);
                if ($languageId === null) {
                    continue;
                }

                TranslationValue::query()->updateOrCreate(
                    [
                        'translation_key_id' => $translationKey->getKey(),
                        'language_id' => $languageId,
                    ],
                    [
                        'value' => $label,
                        'is_reviewed' => true,
                        'translated_by' => null,
                    ],
                );
            }
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

        Permission::query()
            ->where('is_system', true)
            ->whereNotIn('key', $registryKeys)
            ->delete();
        TranslationKey::query()
            ->where('namespace', 'permissions')
            ->where('is_system', true)
            ->whereNotIn('key', $registryKeys)
            ->delete();
    }
}
