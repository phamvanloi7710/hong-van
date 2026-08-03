<?php

namespace App\Domain\Dashboard;

use App\Domain\Leads\LeadVisibility;
use App\Jobs\Dashboard\GenerateLeadReportExport;
use App\Models\ReportExport;
use App\Models\User;

final readonly class ReportExportService
{
    public function __construct(
        private LeadVisibility $visibility,
        private LeadReportGenerator $generator,
    ) {}

    /** @param array<string, mixed> $filters */
    public function create(User $actor, DashboardRange $range, array $filters): ReportExport
    {
        $query = $this->visibility->queryFor($actor)->whereBetween('created_at', [$range->fromUtc, $range->toUtc]);
        $query->when($filters['type'] ?? null, fn ($builder, $type) => $builder->where('type', $type));
        $query->when($filters['status'] ?? null, fn ($builder, $status) => $builder->where('status', $status));
        $rowCount = $query->count();

        $report = ReportExport::query()->create([
            'requested_by' => $actor->getKey(),
            'type' => 'leads',
            'status' => 'queued',
            'filters' => [
                'from_utc' => $range->fromUtc->toISOString(),
                'to_utc' => $range->toUtc->toISOString(),
                'timezone' => $range->timezone,
                'type' => $filters['type'] ?? null,
                'status' => $filters['status'] ?? null,
            ],
            'row_count' => $rowCount,
            'disk' => 'local',
            'expires_at' => now('UTC')->addHours(max(1, (int) config('dashboard.report_retention_hours', 24))),
        ]);

        if ($rowCount <= max(1, (int) config('dashboard.sync_export_limit', 1000))) {
            return $this->generator->generate($report);
        }

        GenerateLeadReportExport::dispatch($report->getKey());

        return $report;
    }
}
