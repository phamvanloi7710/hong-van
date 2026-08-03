<?php

namespace App\Domain\Seo;

use App\Domain\Audit\AuditTrail;
use App\Models\RedirectRule;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

final readonly class RedirectManager
{
    public function __construct(private AuditTrail $auditTrail, private SitemapCache $sitemaps) {}

    /** @param array<string, mixed> $payload */
    public function save(?RedirectRule $rule, array $payload, User $actor): RedirectRule
    {
        $source = $this->normalize((string) $payload['source_path']);
        $target = isset($payload['target_path']) && $payload['target_path'] !== '' ? $this->normalize((string) $payload['target_path']) : null;
        $status = (int) $payload['status_code'];
        $locale = (string) $payload['locale'];

        if ($this->reserved($source)) {
            throw ValidationException::withMessages(['source_path' => [__('seo.redirect_reserved')]]);
        }
        if ($status === 410 && $target !== null) {
            throw ValidationException::withMessages(['target_path' => [__('seo.redirect_gone_target')]]);
        }
        if ($status !== 410 && $target === null) {
            throw ValidationException::withMessages(['target_path' => [__('seo.redirect_target_required')]]);
        }
        if ($target === $source) {
            throw ValidationException::withMessages(['target_path' => [__('seo.redirect_loop')]]);
        }

        $candidate = RedirectRule::query()->where('source_path', $source)->where('locale', $locale);
        if ($rule?->exists) {
            $candidate->whereKeyNot($rule->getKey());
        }
        if ($candidate->exists()) {
            throw ValidationException::withMessages(['source_path' => [__('seo.redirect_collision')]]);
        }

        if ($target !== null) {
            $this->assertNoLoop($source, $target, $locale, $rule);
        }

        $model = $rule ?? new RedirectRule;
        $before = $model->exists ? $model->toArray() : [];
        $model->fill(Arr::only($payload, ['note', 'is_active']));
        $model->source_path = $source;
        $model->target_path = $target;
        $model->status_code = $status;
        $model->locale = $locale;
        $model->updated_by = $actor->getKey();
        if (! $model->exists) {
            $model->created_by = $actor->getKey();
        }
        $model->save();
        $this->sitemaps->invalidate();
        $this->auditTrail->record($before === [] ? 'seo.redirect.created' : 'seo.redirect.updated', $actor, 'redirect', $model->public_id, $before, $model->toArray());

        return $model->refresh();
    }

    public function delete(RedirectRule $rule, User $actor): void
    {
        $before = $rule->toArray();
        $publicId = $rule->public_id;
        $rule->delete();
        $this->sitemaps->invalidate();
        $this->auditTrail->record('seo.redirect.deleted', $actor, 'redirect', $publicId, $before);
    }

    public function normalize(string $path): string
    {
        $path = trim($path);
        if ($path === '' || ! str_starts_with($path, '/') || strpbrk($path, '?#\\') !== false || preg_match('/[\x00-\x1F\x7F]/', $path)) {
            throw ValidationException::withMessages(['source_path' => [__('seo.redirect_path')]]);
        }

        $normalized = preg_replace('#/{2,}#', '/', $path) ?: '/';

        return $normalized === '/' ? '/' : rtrim($normalized, '/');
    }

    private function reserved(string $path): bool
    {
        foreach (['/', '/admin', '/api', '/sanctum', '/health', '/sitemap.xml', '/sitemaps', '/robots.txt', '/storage', '/vi', '/en', '/zh'] as $reserved) {
            if ($path === $reserved || ($reserved !== '/' && str_starts_with($path, $reserved.'/'))) {
                return true;
            }
        }

        return false;
    }

    private function assertNoLoop(string $source, string $target, string $locale, ?RedirectRule $current): void
    {
        $visited = [$source];
        $cursor = $target;
        for ($hop = 0; $hop < 50; $hop++) {
            if (in_array($cursor, $visited, true)) {
                throw ValidationException::withMessages(['target_path' => [__('seo.redirect_loop')]]);
            }
            $visited[] = $cursor;
            $next = RedirectRule::query()->where('is_active', true)->where('source_path', $cursor)
                ->whereIn('locale', [$locale, '*'])
                ->when($current?->exists, fn ($query) => $query->whereKeyNot($current->getKey()))
                ->orderByRaw('locale = ? desc', [$locale])->first();
            if (! $next instanceof RedirectRule || $next->status_code === 410 || $next->target_path === null) {
                return;
            }
            $cursor = $next->target_path;
        }
        throw ValidationException::withMessages(['target_path' => [__('seo.redirect_loop')]]);
    }
}
