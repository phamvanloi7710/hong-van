<?php

namespace App\Domain\Leads;

use App\Domain\Audit\AuditTrail;
use App\Models\Lead;

final readonly class LeadRetentionService
{
    public function __construct(private AuditTrail $audit) {}

    public function anonymizeExpired(): int
    {
        $count = 0;
        Lead::query()->whereNull('anonymized_at')->where('retention_until', '<=', now('UTC'))->chunkById(100, function ($leads) use (&$count): void {
            foreach ($leads as $lead) {
                Lead::withoutEvents(fn () => $lead->forceFill([
                    'contact_name' => 'Anonymized', 'contact_phone' => null, 'contact_email' => null,
                    'original_payload' => [], 'dedupe_hash' => null, 'anonymized_at' => now('UTC'),
                ])->save());
                $count++;
            }
        });
        if ($count > 0) {
            $this->audit->record('lead.retention.anonymized', subjectType: 'lead_batch', after: ['count' => $count]);
        }

        return $count;
    }
}
