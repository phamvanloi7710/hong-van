<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\SettingGroup;
use Illuminate\Database\Seeder;

final class CompanySettingsSeeder extends Seeder
{
    public function run(): void
    {
        $groups = config('company_settings.groups', []);

        foreach (array_values(array_keys($groups)) as $groupOrder => $groupKey) {
            $definition = $groups[$groupKey];
            $group = SettingGroup::query()->updateOrCreate(
                ['key' => $groupKey],
                ['label' => $definition['label'], 'description' => $definition['description'], 'is_active' => true, 'sort_order' => $groupOrder],
            );

            foreach (array_values(array_keys($definition['settings'])) as $settingOrder => $settingKey) {
                $setting = $definition['settings'][$settingKey];
                Setting::query()->updateOrCreate(
                    ['setting_group_id' => $group->getKey(), 'key' => $settingKey],
                    [
                        'label' => $setting['label'],
                        'description' => null,
                        'value' => $this->encode($setting['default']),
                        'value_type' => $setting['type'],
                        'is_public' => $setting['public'],
                        'is_locked' => false,
                        'sort_order' => $settingOrder,
                    ],
                );
            }
        }
    }

    private function encode(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return is_bool($value) ? ($value ? '1' : '0') : (string) $value;
    }
}
