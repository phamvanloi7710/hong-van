<?php

namespace App\Domain\Search;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final readonly class PublicSearchQuery
{
    public function __construct(private SearchTermNormalizer $normalizer) {}

    /**
     * @param  list<string>  $types
     * @return LengthAwarePaginator<int, object>
     */
    public function paginate(string $term, string $locale, array $types = [], int $perPage = 12): LengthAwarePaginator
    {
        $booleanQuery = $this->normalizer->booleanQuery($this->normalizer->normalize($term));
        /** @var list<string> $allowedTypes */
        $allowedTypes = config('search.types', []);
        $selectedTypes = $types === [] ? $allowedTypes : array_values(array_intersect($allowedTypes, $types));
        $safePerPage = max(1, min($perPage, (int) config('search.max_per_page', 24)));

        if ($booleanQuery === '' || $selectedTypes === []) {
            return DB::query()->whereRaw('1 = 0')->paginate($safePerPage);
        }

        $queries = array_map(fn (string $type): Builder => $this->typeQuery($type, $locale, $booleanQuery), $selectedTypes);
        /** @var Builder $union */
        $union = array_shift($queries);
        foreach ($queries as $query) {
            $union->unionAll($query);
        }

        return DB::query()
            ->fromSub($union, 'public_search_results')
            ->orderByDesc('relevance')
            ->orderByDesc('published_at')
            ->orderBy('type')
            ->orderBy('public_id')
            ->paginate($safePerPage);
    }

    public function highlight(?string $value, string $term): string
    {
        $escaped = e(strip_tags((string) $value));
        preg_match_all('/[\p{L}\p{N}]+/u', $this->normalizer->normalize($term), $matches);
        $tokens = array_values(array_filter($matches[0], static fn (string $token): bool => mb_strlen($token) >= 2));

        foreach ($tokens as $token) {
            $escaped = preg_replace('/('.preg_quote(e($token), '/').')/iu', '<mark>$1</mark>', $escaped) ?? $escaped;
        }

        return $escaped;
    }

    private function typeQuery(string $type, string $locale, string $booleanQuery): Builder
    {
        return match ($type) {
            'products' => $this->translationQuery($type, 'hongvan_products', 'hongvan_product_translations', 'product_id', 'name', 'short_description', $locale, $booleanQuery, true),
            'crop_solutions' => $this->translationQuery($type, 'hongvan_crop_solutions', 'hongvan_crop_solution_translations', 'crop_solution_id', 'title', 'summary', $locale, $booleanQuery, true),
            'services' => $this->translationQuery($type, 'hongvan_services', 'hongvan_service_translations', 'service_id', 'name', 'summary', $locale, $booleanQuery, true),
            'posts' => $this->translationQuery($type, 'hongvan_posts', 'hongvan_post_translations', 'post_id', 'title', 'excerpt', $locale, $booleanQuery, true),
            'projects' => $this->translationQuery($type, 'hongvan_projects', 'hongvan_project_translations', 'project_id', 'title', 'summary', $locale, $booleanQuery, false),
            default => throw new \InvalidArgumentException('Unsupported public search type.'),
        };
    }

    private function translationQuery(
        string $type,
        string $baseTable,
        string $translationTable,
        string $foreignKey,
        string $titleColumn,
        string $excerptColumn,
        string $locale,
        string $booleanQuery,
        bool $hasUnpublishedAt,
    ): Builder {
        $match = 'MATCH (translation.search_text) AGAINST (? IN BOOLEAN MODE)';
        $query = DB::table($baseTable.' as content')
            ->join($translationTable.' as translation', 'translation.'.$foreignKey, '=', 'content.id')
            ->selectRaw('? as type', [$type])
            ->addSelect([
                'content.public_id',
                'translation.'.$titleColumn.' as title',
                'translation.slug',
                'translation.'.$excerptColumn.' as excerpt',
                'content.published_at',
            ])
            ->selectRaw($match.' as relevance', [$booleanQuery])
            ->where('translation.locale', $locale)
            ->where('content.status', 'published')
            ->whereNotNull('content.published_at')
            ->where('content.published_at', '<=', now('UTC'))
            ->whereNull('content.deleted_at')
            ->whereRaw($match, [$booleanQuery]);

        if ($hasUnpublishedAt) {
            $query->where(static fn (Builder $builder) => $builder
                ->whereNull('content.unpublished_at')
                ->orWhere('content.unpublished_at', '>', now('UTC')));
        }

        return $query;
    }
}
