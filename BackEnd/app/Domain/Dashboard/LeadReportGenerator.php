<?php

namespace App\Domain\Dashboard;

use App\Domain\Leads\LeadVisibility;
use App\Models\ReportExport;
use App\Support\CsvCell;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final readonly class LeadReportGenerator
{
    public function __construct(private LeadVisibility $visibility) {}

    public function generate(ReportExport $report): ReportExport
    {
        $actor = $report->requester()->firstOrFail();
        $report->forceFill(['status' => 'processing', 'failure_message' => null])->save();
        $stream = fopen('php://temp', 'w+b');
        if ($stream === false) {
            throw new RuntimeException('Unable to open the report stream.');
        }

        fputcsv($stream, ['public_id', 'type', 'status', 'contact_name', 'contact_phone', 'contact_email', 'assignee', 'next_follow_up_at', 'created_at']);
        $rowCount = 0;
        $query = $this->visibility->queryFor($actor)
            ->leftJoin('hongvan_users AS report_assignee', 'hongvan_leads.assigned_to', '=', 'report_assignee.id')
            ->select(['hongvan_leads.*', 'report_assignee.name AS assignee_name'])
            ->orderBy('hongvan_leads.id');
        $rawFilters = $report->getAttribute('filters');
        $filters = is_array($rawFilters) ? $rawFilters : [];
        $query->when($filters['type'] ?? null, fn ($builder, $type) => $builder->where('hongvan_leads.type', $type));
        $query->when($filters['status'] ?? null, fn ($builder, $status) => $builder->where('hongvan_leads.status', $status));
        if (isset($filters['from_utc'], $filters['to_utc'])) {
            $query->whereBetween('hongvan_leads.created_at', [(string) $filters['from_utc'], (string) $filters['to_utc']]);
        }

        foreach ($query->cursor() as $lead) {
            fputcsv($stream, array_map(CsvCell::sanitize(...), [
                $lead->public_id,
                $lead->type,
                $lead->status,
                $lead->contact_name,
                $lead->contact_phone,
                $lead->contact_email,
                $lead->getAttribute('assignee_name'),
                $lead->next_follow_up_at === null ? null : CarbonImmutable::parse((string) $lead->next_follow_up_at)->utc()->toISOString(),
                $lead->created_at === null ? null : CarbonImmutable::parse((string) $lead->created_at)->utc()->toISOString(),
            ]));
            $rowCount++;
        }

        rewind($stream);
        $path = 'reports/'.$report->requested_by.'/'.$report->public_id.'.csv';
        $stored = Storage::disk($report->disk)->put($path, $stream);
        fclose($stream);
        if (! $stored) {
            throw new RuntimeException('Unable to store the generated report.');
        }

        $report->forceFill(['status' => 'ready', 'row_count' => $rowCount, 'file_path' => $path])->save();

        return $report->fresh() ?? $report;
    }
}
