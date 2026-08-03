<?php

namespace App\Http\Controllers\Api\V1\Themes;

use App\Domain\Themes\ThemeManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Themes\UpdateThemeDraftRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Theme;
use App\Models\ThemeVersion;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

final class ThemeController extends Controller
{
    public function __construct(private readonly ThemeManager $manager) {}

    public function active(ApiResponse $response): JsonResponse
    {
        return $response->success($this->payload(Theme::query()->where('is_active', true)->firstOrFail()));
    }

    public function show(Theme $theme, ApiResponse $response): JsonResponse
    {
        return $response->success($this->payload($theme));
    }

    public function updateDraft(UpdateThemeDraftRequest $request, Theme $theme, ApiResponse $response): JsonResponse
    {
        $actor = $this->actor($request);
        $this->manager->saveDraft($theme, (array) $request->validated('tokens'), $actor);

        return $response->success($this->payload($theme->refresh()), __('theme.draft_saved'));
    }

    public function preview(Request $request, Theme $theme, ApiResponse $response): JsonResponse
    {
        $draft = $theme->versions()->where('status', 'draft')->latest('version_number')->firstOrFail();
        $url = URL::temporarySignedRoute('preview.theme', now()->addMinutes(20), ['theme' => $theme->public_id, 'version' => $draft->public_id]);

        return $response->success(['url' => $url, 'expires_in_seconds' => 1200]);
    }

    public function publish(Request $request, Theme $theme, ApiResponse $response): JsonResponse
    {
        $this->manager->publish($theme, $this->actor($request));

        return $response->success($this->payload($theme->refresh()), __('theme.published'));
    }

    public function rollback(Request $request, Theme $theme, ThemeVersion $version, ApiResponse $response): JsonResponse
    {
        $this->manager->rollback($theme, $version, $this->actor($request));

        return $response->success($this->payload($theme->refresh()), __('theme.rolled_back'));
    }

    /** @return array<string, mixed> */
    private function payload(Theme $theme): array
    {
        $theme->load(['publishedVersion', 'versions' => fn ($query) => $query->latest('version_number')]);
        $draft = $theme->versions->firstWhere('status', 'draft');

        return [
            'public_id' => $theme->public_id, 'key' => $theme->key, 'name' => $theme->name,
            'description' => $theme->description, 'is_active' => $theme->is_active,
            'draft' => $draft ? $this->version($draft, true) : null,
            'published' => $theme->publishedVersion ? $this->version($theme->publishedVersion, true) : null,
            'versions' => $theme->versions->map(fn (ThemeVersion $version): array => $this->version($version, false))->values(),
        ];
    }

    /** @return array<string, mixed> */
    private function version(ThemeVersion $version, bool $withTokens): array
    {
        $data = [
            'public_id' => $version->public_id, 'version_number' => $version->version_number,
            'status' => $version->status, 'checksum' => $version->checksum,
            'published_at' => $this->isoDate($version->getAttribute('published_at')),
            'updated_at' => $this->isoDate($version->getAttribute('updated_at')),
        ];
        if ($withTokens) {
            $data['tokens'] = $version->tokens;
        }

        return $data;
    }

    private function isoDate(mixed $value): ?string
    {
        return $value instanceof DateTimeInterface ? $value->format(DATE_ATOM) : null;
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}
