<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

final class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        Language::query()->update(['is_default' => false]);

        foreach ([
            ['locale' => 'vi', 'name' => 'Tiếng Việt', 'native_name' => 'Tiếng Việt', 'is_default' => true, 'sort_order' => 10],
            ['locale' => 'en', 'name' => 'Tiếng Anh', 'native_name' => 'English', 'is_default' => false, 'sort_order' => 20],
            ['locale' => 'zh', 'name' => 'Tiếng Trung', 'native_name' => '中文', 'is_default' => false, 'sort_order' => 30],
        ] as $language) {
            Language::query()->updateOrCreate(
                ['locale' => $language['locale']],
                [...$language, 'is_active' => true],
            );
        }
    }
}
