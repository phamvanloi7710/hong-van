<?php

namespace App\Domain\Posts;

use App\Domain\Audit\AuditTrail;
use App\Models\Post;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final readonly class ScheduledPostPublisher
{
    public function __construct(private AuditTrail $audit) {}

    public function publishDue(): int
    {
        $count = 0;
        Post::query()->where('status', 'scheduled')->whereNotNull('scheduled_for')
            ->where('scheduled_for', '<=', now('UTC'))->orderBy('id')->chunkById(100, function ($posts) use (&$count): void {
                foreach ($posts as $post) {
                    $published = DB::transaction(function () use ($post): bool {
                        $locked = Post::query()->lockForUpdate()->find($post->getKey());
                        if ($locked === null || $locked->status !== 'scheduled') {
                            return false;
                        }
                        $publishedAt = $locked->getAttribute('scheduled_for');
                        if (! $publishedAt instanceof CarbonInterface || $publishedAt->isFuture()) {
                            return false;
                        }
                        $locked->forceFill(['status' => 'published', 'scheduled_for' => null, 'published_at' => $publishedAt])->save();
                        $this->audit->record(
                            action: 'post.scheduled_published',
                            subjectType: $locked->getTable(),
                            subjectPublicId: $locked->public_id,
                            after: ['published_at' => $publishedAt->toISOString()],
                        );

                        return true;
                    });
                    $count += $published ? 1 : 0;
                }
            });

        if ($count > 0) {
            Cache::forever('posts:version', ((int) Cache::get('posts:version', 0)) + 1);
        }

        return $count;
    }
}
