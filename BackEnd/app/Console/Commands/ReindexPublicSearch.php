<?php

namespace App\Console\Commands;

use App\Domain\Search\SearchIndexHealth;
use Illuminate\Console\Command;

final class ReindexPublicSearch extends Command
{
    protected $signature = 'search:reindex {--health : Verify automatic MySQL FULLTEXT indexes without rebuilding them}';

    protected $description = 'Verify the automatically maintained public MySQL FULLTEXT search indexes';

    public function handle(SearchIndexHealth $health): int
    {
        $report = $health->inspect();
        $this->line(__('search.driver', ['driver' => $report['driver'], 'collation' => $report['collation']]));
        foreach ($report['indexes'] as $table => $ready) {
            $this->line(($ready ? '[OK] ' : '[MISSING] ').$table);
        }
        foreach ($report['plans'] as $table => $plan) {
            $this->line("[PLAN] {$table}: access={$plan['access']}; key={$plan['key']}");
        }
        if (! $report['healthy']) {
            $this->error(__('search.health_failed'));

            return self::FAILURE;
        }

        $this->info(__('search.health_passed'));

        return self::SUCCESS;
    }
}
