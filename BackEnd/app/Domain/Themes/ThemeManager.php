<?php

namespace App\Domain\Themes;

use App\Domain\Audit\AuditTrail;
use App\Models\Theme;
use App\Models\ThemeVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class ThemeManager
{
    public function __construct(
        private ThemeTokenSchema $schema,
        private ThemeCssCompiler $compiler,
        private PublicThemeRuntime $runtime,
        private AuditTrail $audit,
    ) {}

    /** @param array<string, mixed> $tokens */
    public function saveDraft(Theme $theme, array $tokens, User $actor): ThemeVersion
    {
        $this->schema->validate($tokens);

        $draft = DB::transaction(function () use ($theme, $tokens, $actor): ThemeVersion {
            $lockedTheme = Theme::query()->lockForUpdate()->findOrFail($theme->getKey());
            $draft = $lockedTheme->versions()->where('status', 'draft')->latest('version_number')->first();

            if (! $draft instanceof ThemeVersion) {
                $draft = $this->newDraft($lockedTheme, $lockedTheme->publishedVersion, $actor);
            }

            $draft->fill([
                'tokens' => $tokens,
                'compiled_css' => $this->compiler->compile($tokens),
                'checksum' => $this->schema->checksum($tokens),
            ])->save();
            $lockedTheme->update(['updated_by' => $actor->getKey()]);

            return $draft->refresh();
        });

        $this->audit->record('theme.draft.updated', $actor, 'theme', $theme->public_id, [], ['version' => $draft->version_number, 'checksum' => $draft->checksum]);

        return $draft;
    }

    public function publish(Theme $theme, User $actor): ThemeVersion
    {
        $published = DB::transaction(function () use ($theme, $actor): ThemeVersion {
            $lockedTheme = Theme::query()->lockForUpdate()->findOrFail($theme->getKey());
            $draft = $lockedTheme->versions()->where('status', 'draft')->latest('version_number')->lockForUpdate()->first();
            if (! $draft instanceof ThemeVersion) {
                throw ValidationException::withMessages(['theme' => ['Không có bản nháp để xuất bản.']]);
            }

            $draft->update(['status' => 'published', 'published_by' => $actor->getKey(), 'published_at' => now('UTC')]);
            Theme::query()->whereKeyNot($lockedTheme->getKey())->where('is_active', true)->update(['is_active' => false]);
            $lockedTheme->update(['is_active' => true, 'published_version_id' => $draft->getKey(), 'updated_by' => $actor->getKey()]);
            $this->newDraft($lockedTheme, $draft, $actor);

            return $draft->refresh();
        });

        $this->runtime->forgetPublished();
        $this->audit->record('theme.published', $actor, 'theme', $theme->public_id, [], ['version' => $published->version_number, 'checksum' => $published->checksum]);

        return $published;
    }

    public function rollback(Theme $theme, ThemeVersion $target, User $actor): ThemeVersion
    {
        if ($target->theme_id !== $theme->getKey() || $target->status !== 'published') {
            throw ValidationException::withMessages(['version' => ['Chỉ có thể rollback tới một phiên bản đã xuất bản của theme này.']]);
        }

        DB::transaction(function () use ($theme, $target, $actor): void {
            $lockedTheme = Theme::query()->lockForUpdate()->findOrFail($theme->getKey());
            $lockedTheme->versions()->where('status', 'draft')->update(['status' => 'discarded']);
            Theme::query()->whereKeyNot($lockedTheme->getKey())->where('is_active', true)->update(['is_active' => false]);
            $lockedTheme->update(['is_active' => true, 'published_version_id' => $target->getKey(), 'updated_by' => $actor->getKey()]);
            $this->newDraft($lockedTheme, $target, $actor);
        });

        $this->runtime->forgetPublished();
        $this->audit->record('theme.rolled_back', $actor, 'theme', $theme->public_id, [], ['version' => $target->version_number, 'checksum' => $target->checksum]);

        return $target->refresh();
    }

    private function newDraft(Theme $theme, ?ThemeVersion $parent, User $actor): ThemeVersion
    {
        $parentTokens = $parent?->getAttribute('tokens');
        $tokens = is_array($parentTokens) ? $parentTokens : $this->schema->defaults();
        $next = ((int) $theme->versions()->max('version_number')) + 1;

        return $theme->versions()->create([
            'version_number' => $next,
            'status' => 'draft',
            'tokens' => $tokens,
            'compiled_css' => $this->compiler->compile($tokens),
            'checksum' => $this->schema->checksum($tokens),
            'parent_version_id' => $parent?->getKey(),
            'created_by' => $actor->getKey(),
        ]);
    }
}
