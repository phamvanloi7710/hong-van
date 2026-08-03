<?php

namespace App\Console\Commands;

use App\Domain\Posts\ScheduledPostPublisher;
use Illuminate\Console\Command;

final class PublishScheduledPosts extends Command
{
    protected $signature = 'posts:publish-scheduled';

    protected $description = 'Publish all due scheduled posts idempotently';

    public function handle(ScheduledPostPublisher $publisher): int
    {
        $this->info(__('posts.scheduled_published_count', ['count' => $publisher->publishDue()]));

        return self::SUCCESS;
    }
}
