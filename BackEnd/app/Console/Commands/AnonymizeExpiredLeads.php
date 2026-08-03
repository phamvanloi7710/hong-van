<?php

namespace App\Console\Commands;

use App\Domain\Leads\LeadRetentionService;
use Illuminate\Console\Command;

final class AnonymizeExpiredLeads extends Command
{
    protected $signature = 'leads:anonymize-expired';

    protected $description = 'Anonymize personal data in leads whose configured retention period has expired';

    public function handle(LeadRetentionService $retention): int
    {
        $count = $retention->anonymizeExpired();
        $this->info("Anonymized {$count} expired lead(s).");

        return self::SUCCESS;
    }
}
