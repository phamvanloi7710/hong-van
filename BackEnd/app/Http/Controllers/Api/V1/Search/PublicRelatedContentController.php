<?php

namespace App\Http\Controllers\Api\V1\Search;

use App\Domain\Localization\LocaleRegistry;
use App\Domain\Search\RelatedContentQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Search\RelatedContentRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class PublicRelatedContentController extends Controller
{
    public function __invoke(string $type, string $publicId, RelatedContentRequest $request, LocaleRegistry $locales, RelatedContentQuery $related, ApiResponse $response): JsonResponse
    {
        $locale = (string) $request->attributes->get('api_locale', app()->getLocale());
        abort_unless($locales->isActive($locale), 404);

        return $response->success($related->resolve($type, $publicId, $locale, (int) $request->validated('limit', 4)));
    }
}
