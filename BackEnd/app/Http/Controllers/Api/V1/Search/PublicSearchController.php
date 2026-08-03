<?php

namespace App\Http\Controllers\Api\V1\Search;

use App\Domain\Localization\LocaleRegistry;
use App\Domain\Search\PublicSearchQuery;
use App\Domain\Search\SearchAnalyticsRecorder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Search\IndexPublicSearchRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class PublicSearchController extends Controller
{
    public function __invoke(IndexPublicSearchRequest $request, LocaleRegistry $locales, PublicSearchQuery $query, SearchAnalyticsRecorder $analytics, ApiResponse $response): JsonResponse
    {
        $locale = (string) $request->attributes->get('api_locale', app()->getLocale());
        abort_unless($locales->isActive($locale), 404);
        $validated = $request->validated();
        $term = (string) $validated['q'];
        /** @var list<string> $types */
        $types = array_values($validated['types'] ?? []);
        $paginator = $query->paginate($term, $locale, $types, (int) ($validated['per_page'] ?? config('search.default_per_page', 12)));
        $items = [];
        foreach ($paginator->items() as $item) {
            $row = (array) $item;
            $title = is_string($row['title'] ?? null) ? $row['title'] : null;
            $excerpt = is_string($row['excerpt'] ?? null) ? $row['excerpt'] : null;
            $items[] = [
                'type' => (string) ($row['type'] ?? ''),
                'public_id' => (string) ($row['public_id'] ?? ''),
                'title' => $title,
                'slug' => is_string($row['slug'] ?? null) ? $row['slug'] : null,
                'excerpt' => $excerpt,
                'highlighted_title' => $query->highlight($title, $term),
                'highlighted_excerpt' => $query->highlight($excerpt, $term),
            ];
        }
        $analytics->record($term, $locale, $types, $paginator->total(), $request);

        return $response->paginated($items, $paginator);
    }
}
