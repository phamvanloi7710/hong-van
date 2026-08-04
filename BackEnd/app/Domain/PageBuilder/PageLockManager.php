<?php

namespace App\Domain\PageBuilder;

use App\Domain\Audit\AuditTrail;
use App\Exceptions\ConflictException;
use App\Models\Page;
use App\Models\PageLock;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class PageLockManager
{
    private const TTL_SECONDS = 300;

    public function __construct(private AuditTrail $audit) {}

    /** @return array{lock: PageLock, token: string} */
    public function acquire(User $actor, Page $page): array
    {
        $result = DB::transaction(function () use ($actor, $page): array {
            $existing = PageLock::query()->where('page_id', $page->getKey())->lockForUpdate()->first();
            if ($existing !== null && $existing->expires_at->isFuture()) {
                if ($existing->user_id !== $actor->getKey()) {
                    throw new ConflictException('This page is currently being edited by another administrator.');
                }
                $existing->delete();
            }
            if ($existing !== null) {
                $existing->delete();
            }
            $token = Str::random(64);
            $lock = PageLock::query()->create([
                'page_id' => $page->getKey(), 'user_id' => $actor->getKey(), 'token_hash' => hash('sha256', $token),
                'expires_at' => now('UTC')->addSeconds(self::TTL_SECONDS), 'refreshed_at' => now('UTC'),
            ]);

            return ['lock' => $lock->load('user'), 'token' => $token];
        });
        $this->audit->record('page.lock.acquired', $actor, 'page', $page->public_id, [], ['lock_id' => $result['lock']->public_id]);

        return $result;
    }

    public function heartbeat(User $actor, Page $page, string $token): PageLock
    {
        $lock = PageLock::query()->where('page_id', $page->getKey())->first();
        if ($lock === null || $lock->expires_at->isPast() || $lock->user_id !== $actor->getKey() || ! hash_equals($lock->token_hash, hash('sha256', $token))) {
            throw new ConflictException('The page edit lock has expired or belongs to another administrator.');
        }
        $lock->update(['expires_at' => now('UTC')->addSeconds(self::TTL_SECONDS), 'refreshed_at' => now('UTC')]);

        return $lock->refresh()->load('user');
    }

    public function release(User $actor, Page $page, string $token): void
    {
        $lock = PageLock::query()->where('page_id', $page->getKey())->first();
        if ($lock !== null && $lock->user_id === $actor->getKey() && hash_equals($lock->token_hash, hash('sha256', $token))) {
            $lock->delete();
        }
    }

    public function forceRelease(User $actor, Page $page): void
    {
        $lock = PageLock::query()->where('page_id', $page->getKey())->first();
        if ($lock === null) {
            return;
        }
        $before = ['owner_id' => $lock->user_id, 'lock_id' => $lock->public_id];
        $lock->delete();
        $this->audit->record('page.lock.force_released', $actor, 'page', $page->public_id, $before, []);
    }

    public function current(Page $page): ?PageLock
    {
        $lock = PageLock::query()->with('user')->where('page_id', $page->getKey())->first();
        if ($lock !== null && $lock->expires_at->isPast()) {
            $lock->delete();

            return null;
        }

        return $lock;
    }

    public function assertCanEdit(User $actor, Page $page, ?string $token): void
    {
        $lock = $this->current($page);
        if ($lock === null) {
            return;
        }
        if ($lock->user_id !== $actor->getKey() || $token === null || ! hash_equals($lock->token_hash, hash('sha256', $token))) {
            throw new ConflictException('This page has an active edit lock owned by another session.');
        }
    }

    public static function ttlSeconds(): int
    {
        return self::TTL_SECONDS;
    }
}
