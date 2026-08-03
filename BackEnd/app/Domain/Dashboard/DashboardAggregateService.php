<?php

namespace App\Domain\Dashboard;

use App\Domain\Analytics\AnalyticsConfiguration;
use App\Domain\Identity\PermissionService;
use App\Domain\Leads\LeadVisibility;
use App\Models\AuditLog;
use App\Models\Lead;
use App\Models\Post;
use App\Models\Product;
use App\Models\SearchLog;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final readonly class DashboardAggregateService
{
    public function __construct(
        private DashboardCache $cache,
        private PermissionService $permissions,
        private LeadVisibility $leadVisibility,
        private AnalyticsConfiguration $analytics,
    ) {}

    /** @return array<string, mixed> */
    public function get(User $actor, DashboardRange $range): array
    {
        $permissionKeys = $this->permissions->effectivePermissionKeys($actor);
        $cacheKey = hash('sha256', implode('|', [
            (string) $actor->getKey(),
            ...$range->toArray(),
            ...$permissionKeys,
        ]));

        return $this->cache->remember($cacheKey, fn (): array => $this->resolve($actor, $range, $permissionKeys));
    }

    /**
     * @param  list<string>  $permissionKeys
     * @return array<string, mixed>
     */
    private function resolve(User $actor, DashboardRange $range, array $permissionKeys): array
    {
        $canProducts = in_array('products.view', $permissionKeys, true);
        $canPosts = in_array('posts.view', $permissionKeys, true);
        $canLeads = in_array('leads.view', $permissionKeys, true);
        $canAudit = in_array('audit.view', $permissionKeys, true);
        $canAnalytics = in_array('seo.view', $permissionKeys, true);

        $leadQuery = $canLeads ? $this->leadVisibility->queryFor($actor) : null;
        $analyticsEnabled = $canAnalytics
            && $this->analytics->get()['enabled']
            && (bool) config('search.analytics_enabled', false);

        return [
            'range' => $range->toArray(),
            'capabilities' => [
                'products' => $canProducts,
                'content' => $canPosts,
                'leads' => $canLeads,
                'activity' => $canAudit,
                'analytics' => $analyticsEnabled,
                'pages' => false,
                'top_viewed' => false,
            ],
            'cards' => [
                'products' => $canProducts ? [
                    'total' => Product::query()->count(),
                    'published' => Product::query()->where('status', 'published')->count(),
                ] : null,
                'content' => $canPosts ? [
                    'drafts' => Post::query()->where('status', 'draft')->count(),
                    'scheduled' => Post::query()->where('status', 'scheduled')->count(),
                    'pages' => null,
                ] : null,
                'leads' => $leadQuery instanceof Builder ? $this->leadCards(clone $leadQuery, $range) : null,
            ],
            'charts' => [
                'leads' => $leadQuery instanceof Builder ? $this->dailySeries(clone $leadQuery, $range, 'created_at') : [],
                'published_products' => $canProducts
                    ? $this->dailySeries(Product::query()->where('status', 'published'), $range, 'published_at')
                    : [],
            ],
            'recent_activity' => $canAudit ? $this->recentActivity($range, $permissionKeys) : [],
            'analytics' => [
                'enabled' => $analyticsEnabled,
                'top_search_terms' => $analyticsEnabled ? $this->topSearchTerms($range) : [],
                'top_viewed' => [],
            ],
            'generated_at' => now('UTC')->toISOString(),
            'cache_ttl_seconds' => max(15, (int) config('dashboard.cache_ttl_seconds', 60)),
        ];
    }

    /**
     * @param  Builder<Lead>  $query
     * @return array<string, mixed>
     */
    private function leadCards(Builder $query, DashboardRange $range): array
    {
        $withinRange = (clone $query)->whereBetween('created_at', [$range->fromUtc, $range->toUtc]);

        return [
            'total' => (clone $query)->count(),
            'new_in_range' => (clone $withinRange)->where('status', 'new')->count(),
            'overdue_follow_up' => (clone $query)
                ->whereNotNull('next_follow_up_at')
                ->where('next_follow_up_at', '<', now('UTC'))
                ->whereNotIn('status', ['done', 'spam', 'archived'])
                ->count(),
            'by_type' => (clone $withinRange)->selectRaw('type, COUNT(*) AS aggregate')->groupBy('type')->pluck('aggregate', 'type'),
            'by_status' => (clone $withinRange)->selectRaw('status, COUNT(*) AS aggregate')->groupBy('status')->pluck('aggregate', 'status'),
        ];
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return list<array{date: string, value: int}>
     */
    private function dailySeries(Builder $query, DashboardRange $range, string $column): array
    {
        $offset = $range->fromUtc->setTimezone($range->timezone)->format('P');
        $rows = $query
            ->whereNotNull($column)
            ->whereBetween($column, [$range->fromUtc, $range->toUtc])
            ->selectRaw("DATE(CONVERT_TZ({$column}, '+00:00', ?)) AS aggregate_date, COUNT(*) AS aggregate", [$offset])
            ->groupBy('aggregate_date')
            ->orderBy('aggregate_date')
            ->get();

        return $rows->map(static fn ($row): array => [
            'date' => (string) $row->getAttribute('aggregate_date'),
            'value' => (int) $row->getAttribute('aggregate'),
        ])->all();
    }

    /**
     * @param  list<string>  $permissionKeys
     * @return list<array{public_id: string, action: string, subject_type: string|null, subject_public_id: string|null, actor_public_id: string|null, occurred_at: string}>
     */
    private function recentActivity(DashboardRange $range, array $permissionKeys): array
    {
        $subjectTypes = ['authentication'];
        $moduleSubjects = [
            'users.view' => ['user', 'role', 'permission'],
            'settings.view' => ['company_settings'],
            'products.view' => ['product', 'product_category', 'brand', 'product_tag', 'product_attribute'],
            'posts.view' => ['post', 'post_category', 'post_tag'],
            'leads.view' => ['lead'],
            'media.view' => ['media', 'media_folder'],
            'showcase.view' => ['gallery', 'gallery_item', 'partner', 'certification', 'project'],
        ];
        foreach ($moduleSubjects as $permission => $types) {
            if (in_array($permission, $permissionKeys, true)) {
                array_push($subjectTypes, ...$types);
            }
        }

        return AuditLog::query()
            ->whereBetween('occurred_at', [$range->fromUtc, $range->toUtc])
            ->whereIn('subject_type', array_values(array_unique($subjectTypes)))
            ->where('action', '!=', 'identity.super_admin_bypass')
            ->latest('occurred_at')
            ->limit(8)
            ->get(['public_id', 'action', 'subject_type', 'subject_public_id', 'actor_public_id', 'occurred_at'])
            ->map(static fn (AuditLog $log): array => [
                'public_id' => $log->public_id,
                'action' => $log->action,
                'subject_type' => $log->subject_type,
                'subject_public_id' => $log->subject_public_id,
                'actor_public_id' => $log->actor_public_id,
                'occurred_at' => CarbonImmutable::parse((string) $log->occurred_at)->utc()->toISOString(),
            ])->all();
    }

    /** @return list<array{term: string, searches: int, results: int}> */
    private function topSearchTerms(DashboardRange $range): array
    {
        return SearchLog::query()
            ->whereBetween('created_at', [$range->fromUtc, $range->toUtc])
            ->select(['normalized_term'])
            ->selectRaw('COUNT(*) AS searches, SUM(results_count) AS results')
            ->groupBy('normalized_term')
            ->orderByDesc('searches')
            ->limit(10)
            ->get()
            ->map(static fn (SearchLog $log): array => [
                'term' => $log->normalized_term,
                'searches' => (int) $log->getAttribute('searches'),
                'results' => (int) $log->getAttribute('results'),
            ])->all();
    }
}
