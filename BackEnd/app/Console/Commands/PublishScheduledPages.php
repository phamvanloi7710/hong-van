<?php

namespace App\Console\Commands;

use App\Domain\PageBuilder\PagePublishingManager;
use App\Models\PagePublishSchedule;
use Illuminate\Console\Command;

final class PublishScheduledPages extends Command
{
    protected $signature = 'pages:publish-scheduled';

    protected $description = 'Publish due Page Builder versions idempotently';

    public function handle(PagePublishingManager $manager): int
    {
        PagePublishSchedule::query()->where('status', 'pending')->where('scheduled_at', '<=', now())->orderBy('id')->chunkById(100, function ($schedules) use ($manager): void {
            foreach ($schedules as $schedule) {
                $manager->processSchedule($schedule);
            }
        });

        return self::SUCCESS;
    }
}
