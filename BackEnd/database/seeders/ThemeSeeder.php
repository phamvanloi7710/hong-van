<?php

namespace Database\Seeders;

use App\Domain\Themes\ThemeCssCompiler;
use App\Domain\Themes\ThemeTokenSchema;
use App\Models\Theme;
use App\Models\ThemeVersion;
use Illuminate\Database\Seeder;

final class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        $schema = app(ThemeTokenSchema::class);
        $compiler = app(ThemeCssCompiler::class);
        $tokens = $schema->defaults();
        $theme = Theme::query()->firstOrCreate(
            ['key' => 'hong-van-public'],
            ['name' => 'Hồng Vân Public', 'description' => 'Theme khởi tạo từ bộ token public P19.', 'is_active' => true],
        );
        $published = $theme->versions()->where('status', 'published')->first();

        if (! $published instanceof ThemeVersion) {
            $published = $theme->versions()->create([
                'version_number' => 1, 'status' => 'published', 'tokens' => $tokens,
                'compiled_css' => $compiler->compile($tokens), 'checksum' => $schema->checksum($tokens),
                'published_at' => now('UTC'),
            ]);
        }

        $theme->update(['is_active' => true, 'published_version_id' => $published->getKey()]);
        if (! $theme->versions()->where('status', 'draft')->exists()) {
            $theme->versions()->create([
                'version_number' => ((int) $theme->versions()->max('version_number')) + 1,
                'status' => 'draft', 'tokens' => $published->tokens, 'compiled_css' => $published->compiled_css,
                'checksum' => $published->checksum, 'parent_version_id' => $published->getKey(),
            ]);
        }
    }
}
