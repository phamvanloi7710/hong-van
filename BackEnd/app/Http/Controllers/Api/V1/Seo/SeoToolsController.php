<?php

namespace App\Http\Controllers\Api\V1\Seo;

use App\Domain\Seo\SitemapCache;
use App\Domain\Seo\SitemapGenerator;
use App\Domain\Seo\StructuredDataBuilder;
use App\Domain\Settings\CompanySettingsService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Seo\SaveRobotsRequest;
use App\Http\Responses\ApiResponse;
use App\Jobs\Seo\RegenerateSitemaps;
use App\Models\SettingGroup;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SeoToolsController extends Controller
{
    public function health(SitemapCache $cache, CompanySettingsService $settings, ApiResponse $response): JsonResponse
    {
        return $response->success(array_merge($cache->health(), [
            'sitemap_url' => url('/sitemap.xml'),
            'robots_url' => url('/robots.txt'),
            'public_indexing_enabled' => (bool) $settings->value('seo_defaults', 'public_indexing_enabled', true),
            'disallow_paths' => (string) $settings->value('seo_defaults', 'robots_disallow_paths', "/admin\n/api\n/preview"),
        ]));
    }

    public function regenerate(SitemapCache $cache, SitemapGenerator $generator, ApiResponse $response): JsonResponse
    {
        $cache->invalidate();
        RegenerateSitemaps::dispatch();

        return $response->success(['queued' => true], __('seo.sitemap_queued'), 202);
    }

    public function saveRobots(SaveRobotsRequest $request, CompanySettingsService $settings, SitemapCache $cache, ApiResponse $response): JsonResponse
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);
        $group = SettingGroup::query()->where('key', 'seo_defaults')->firstOrFail();
        $value = collect(preg_split('/\R/', (string) $request->validated('disallow_paths')) ?: [])
            ->map(static fn (string $line): string => trim($line))->filter()->unique()->implode("\n");
        $settings->updateGroup($group, ['robots_disallow_paths' => $value], $actor);
        $cache->invalidate();

        return $response->success(['disallow_paths' => $value], __('seo.robots_saved'));
    }

    public function schemaPreview(Request $request, StructuredDataBuilder $builder, ApiResponse $response): JsonResponse
    {
        $type = (string) $request->query('type', 'organization');
        $locale = (string) $request->query('locale', 'vi');
        abort_unless(in_array($locale, ['vi', 'en', 'zh'], true), 422);
        $schema = match ($type) {
            'organization' => $builder->organization(),
            'local_business' => $builder->localBusiness(),
            'website' => $builder->website($locale),
            default => abort(422),
        };

        return $response->success(['type' => $type, 'locale' => $locale, 'schema' => $schema, 'json' => $schema === null ? null : $builder->encode($schema)]);
    }
}
