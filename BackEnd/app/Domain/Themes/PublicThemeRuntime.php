<?php

namespace App\Domain\Themes;

use App\Models\Theme;
use App\Models\ThemeVersion;
use Illuminate\Support\Facades\Cache;

final readonly class PublicThemeRuntime
{
    public function __construct(private ThemeCssCompiler $compiler, private ThemeTokenSchema $schema) {}

    /** @return array{version: string, css: string} */
    public function published(): array
    {
        return Cache::rememberForever((string) config('public_theme.cache_key'), function (): array {
            $theme = Theme::query()->where('is_active', true)->with('publishedVersion')->first();
            $version = $theme?->publishedVersion;

            return $version instanceof ThemeVersion
                ? Cache::rememberForever(
                    (string) config('public_theme.cache_key').'.version.'.$version->checksum,
                    static fn (): array => ['version' => $version->public_id, 'css' => $version->compiled_css],
                )
                : ['version' => 'config-default', 'css' => $this->compiler->compile($this->schema->defaults())];
        });
    }

    public function forgetPublished(): void
    {
        Cache::forget((string) config('public_theme.cache_key'));
    }
}
