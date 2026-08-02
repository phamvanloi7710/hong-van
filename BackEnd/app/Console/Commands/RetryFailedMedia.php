<?php

namespace App\Console\Commands;

use App\Domain\Media\MediaMaintenanceService;
use Illuminate\Console\Command;

final class RetryFailedMedia extends Command
{
    protected $signature = 'media:retry';

    protected $description = 'Queue retries for media with failed variant generation';

    public function handle(MediaMaintenanceService $maintenance): int
    {
        $this->info(__('media.retry_complete', ['count' => $maintenance->retryFailed()]));

        return self::SUCCESS;
    }
}
