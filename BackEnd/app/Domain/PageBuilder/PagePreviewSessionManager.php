<?php

namespace App\Domain\PageBuilder;

use App\Models\Page;
use App\Models\PagePreviewSession;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

final readonly class PagePreviewSessionManager
{
    public function __construct(private PageDocumentValidator $validator) {}

    /**
     * @param  array<string, mixed>  $document
     * @return array<string, mixed>
     */
    public function create(User $actor, Page $page, array $document, string $locale): array
    {
        $validated = $this->validator->validate($document);
        $token = Str::random(64);
        $expiresAt = now('UTC')->addSeconds($this->ttlSeconds());
        $session = PagePreviewSession::query()->create([
            'page_id' => $page->getKey(),
            'page_version_id' => $page->draft_version_id,
            'user_id' => $actor->getKey(),
            'token_hash' => hash('sha256', $token),
            'locale' => $locale,
            'expires_at' => $expiresAt,
        ]);
        $payload = $this->payload($session, $validated, 1);
        $this->cache()->put($this->cacheKey($session), $payload, $expiresAt);

        return $this->response($session, $token, 1);
    }

    /**
     * @param  array<string, mixed>  $document
     * @return array<string, mixed>
     */
    public function update(User $actor, PagePreviewSession $session, string $token, array $document): array
    {
        $this->assertUsable($actor, $session, $token);
        $current = $this->cachedPayload($session);
        $validated = $this->validator->validate($document);
        $revision = ((int) ($current['revision'] ?? 0)) + 1;
        $this->cache()->put($this->cacheKey($session), $this->payload($session, $validated, $revision), $this->expiresAt($session));

        return $this->response($session, $token, $revision);
    }

    /** @return array<string, mixed> */
    public function refresh(User $actor, PagePreviewSession $session, string $token): array
    {
        $this->assertUsable($actor, $session, $token);
        $payload = $this->cachedPayload($session);
        $expiresAt = now('UTC')->addSeconds($this->ttlSeconds());
        $session->update(['expires_at' => $expiresAt]);
        $session->refresh();
        $this->cache()->put($this->cacheKey($session), $payload, $expiresAt);

        return $this->response($session, $token, (int) ($payload['revision'] ?? 1));
    }

    public function close(User $actor, PagePreviewSession $session, string $token): void
    {
        $this->assertIdentity($actor, $session, $token);
        $this->cache()->forget($this->cacheKey($session));
        $session->delete();
    }

    /** @return array<string, mixed> */
    public function resolve(User $actor, string $token): array
    {
        $session = PagePreviewSession::query()
            ->with(['page.translations'])
            ->where('token_hash', hash('sha256', $token))
            ->firstOrFail();
        $this->assertUsable($actor, $session, $token);
        $payload = $this->cachedPayload($session);

        $lastViewedAt = $this->lastViewedAt($session);
        if ($lastViewedAt === null || $lastViewedAt->lt(now('UTC')->subMinute())) {
            $session->update(['last_viewed_at' => now('UTC')]);
        }

        return ['session' => $session, ...$payload];
    }

    /** @return array<string, mixed> */
    private function cachedPayload(PagePreviewSession $session): array
    {
        $payload = $this->cache()->get($this->cacheKey($session));
        abort_unless(is_array($payload), 410);

        return $payload;
    }

    private function assertUsable(User $actor, PagePreviewSession $session, string $token): void
    {
        $this->assertIdentity($actor, $session, $token);
        abort_if($this->expiresAt($session)->isPast(), 410);
    }

    private function assertIdentity(User $actor, PagePreviewSession $session, string $token): void
    {
        abort_unless((int) $session->user_id === (int) $actor->getKey(), 404);
        abort_unless(hash_equals((string) $session->token_hash, hash('sha256', $token)), 404);
    }

    /**
     * @param  array<string, mixed>  $document
     * @return array<string, mixed>
     */
    private function payload(PagePreviewSession $session, array $document, int $revision): array
    {
        return [
            'page_id' => $session->page_id,
            'user_id' => $session->user_id,
            'locale' => $session->locale,
            'document' => $document,
            'revision' => $revision,
        ];
    }

    /** @return array<string, mixed> */
    private function response(PagePreviewSession $session, string $token, int $revision): array
    {
        $expiresAt = $this->expiresAt($session);

        return [
            'public_id' => $session->public_id,
            'token' => $token,
            // Relative signatures remain valid when WAMP terminates or rewrites the origin.
            'url' => URL::temporarySignedRoute('preview.page-builder', $expiresAt, ['token' => $token], absolute: false),
            'expires_at' => $expiresAt->utc()->toISOString(),
            'ttl_seconds' => max(0, now('UTC')->diffInSeconds($expiresAt, false)),
            'revision' => $revision,
            'message_schema_version' => (int) config('page_builder.preview.message_schema_version', 1),
        ];
    }

    private function cache(): Repository
    {
        return Cache::store((string) config('page_builder.preview.cache_store', 'redis'));
    }

    private function cacheKey(PagePreviewSession $session): string
    {
        return 'page-builder:preview:'.$session->public_id;
    }

    private function ttlSeconds(): int
    {
        return max(60, (int) config('page_builder.preview.ttl_seconds', 300));
    }

    private function expiresAt(PagePreviewSession $session): CarbonImmutable
    {
        $value = $session->getAttribute('expires_at');
        if ($value instanceof CarbonImmutable) {
            return $value;
        }
        if ($value instanceof Carbon) {
            return $value->toImmutable();
        }

        return CarbonImmutable::parse((string) $value, 'UTC');
    }

    private function lastViewedAt(PagePreviewSession $session): ?CarbonImmutable
    {
        $value = $session->getAttribute('last_viewed_at');
        if ($value === null) {
            return null;
        }
        if ($value instanceof CarbonImmutable) {
            return $value;
        }
        if ($value instanceof Carbon) {
            return $value->toImmutable();
        }

        return CarbonImmutable::parse((string) $value, 'UTC');
    }
}
