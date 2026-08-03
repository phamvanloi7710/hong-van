<?php

namespace App\Domain\Search;

use App\Models\SearchLog;
use Illuminate\Http\Request;

final readonly class SearchAnalyticsRecorder
{
    public function __construct(private SearchTermNormalizer $normalizer) {}

    /** @param list<string> $types */
    public function record(string $term, string $locale, array $types, int $resultsCount, Request $request): void
    {
        if (! config('search.analytics_enabled', false)) {
            return;
        }

        $redacted = $this->normalizer->redactPersonalData($this->normalizer->normalize($term));
        $key = (string) config('search.analytics_hash_key', config('app.key'));
        $ip = trim((string) $request->ip());

        SearchLog::query()->create([
            'locale' => $locale,
            'normalized_term' => $redacted,
            'term_hash' => hash('sha256', mb_strtolower($redacted)),
            'types' => $types === [] ? null : $types,
            'results_count' => max(0, $resultsCount),
            'visitor_hash' => $ip === '' ? null : hash_hmac('sha256', $ip, $key),
            'created_at' => now('UTC'),
        ]);
    }
}
