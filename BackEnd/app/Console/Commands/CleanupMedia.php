<?php

namespace App\Console\Commands;

use App\Domain\Media\MediaMaintenanceService;
use Illuminate\Console\Command;

final class CleanupMedia extends Command
{
    protected $signature = 'media:cleanup';

    protected $description = 'Permanently remove unused media whose trash retention has expired';

    public function handle(MediaMaintenanceService $maintenance): int
    {
        $this->info(__('media.cleanup_complete', ['count' => $maintenance->cleanup()]));

        return self::SUCCESS;
    }
}
