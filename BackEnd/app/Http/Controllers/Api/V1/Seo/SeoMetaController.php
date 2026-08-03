<?php

namespace App\Http\Controllers\Api\V1\Seo;

use App\Domain\Seo\SeoEntityRegistry;
use App\Domain\Seo\SeoMetaManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Seo\IndexSeoEntityRequest;
use App\Http\Requests\Api\V1\Seo\SaveSeoMetaRequest;
use App\Http\Resources\Api\V1\Seo\SeoMetaResource;
use App\Http\Responses\ApiResponse;
use App\Models\SeoMeta;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SeoMetaController extends Controller
{
    public function entities(IndexSeoEntityRequest $request, SeoEntityRegistry $registry, ApiResponse $response): JsonResponse
    {
        $type = (string) $request->validated('type');
        $locale = (string) $request->validated('locale');
        $paginator = $registry->paginate($type, $locale, $request->validated('search'), (int) ($request->validated('per_page') ?? 50));

        return $response->paginated(
            collect($paginator->items())->map(fn ($entity): array => $registry->serialize($entity, $locale))->all(),
            $paginator,
        );
    }

    public function show(Request $request, string $type, string $publicId, SeoEntityRegistry $registry, ApiResponse $response): JsonResponse
    {
        $entity = $registry->find($type, $publicId);
        $locale = $this->locale($request);
        $meta = SeoMeta::query()
            ->with(['ogImage.variants'])
            ->where('seoable_type', $type)
            ->where('seoable_id', $entity->getKey())
            ->where('locale', $locale)
            ->first();

        return $response->success($meta === null ? $this->defaults($locale) : SeoMetaResource::make($meta)->resolve($request));
    }

    public function update(SaveSeoMetaRequest $request, string $type, string $publicId, SeoEntityRegistry $registry, SeoMetaManager $manager, ApiResponse $response): JsonResponse
    {
        $entity = $registry->find($type, $publicId);
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);
        $meta = $manager->save($type, $entity, $actor, $request->validated());

        return $response->success(SeoMetaResource::make($meta)->resolve($request), __('seo.updated'));
    }

    private function locale(Request $request): string
    {
        $locale = (string) $request->query('locale', 'vi');
        abort_unless(in_array($locale, ['vi', 'en', 'zh'], true), 422);

        return $locale;
    }

    /** @return array<string, mixed> */
    private function defaults(string $locale): array
    {
        return [
            'public_id' => null, 'locale' => $locale, 'meta_title' => null, 'meta_description' => null,
            'canonical_url' => null, 'robots_index' => true, 'robots_follow' => true, 'og_title' => null,
            'og_description' => null, 'og_image' => null, 'og_type' => 'website',
            'twitter_card' => 'summary_large_image', 'twitter_title' => null, 'twitter_description' => null,
            'focus_keywords' => [], 'updated_at' => null,
        ];
    }
}
