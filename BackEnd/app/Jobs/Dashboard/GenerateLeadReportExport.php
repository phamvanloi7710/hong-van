<?php

namespace App\Jobs\Dashboard;

use App\Domain\Dashboard\LeadReportGenerator;
use App\Models\ReportExport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

final class GenerateLeadReportExport implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $reportExportId) {}

    public function handle(LeadReportGenerator $generator): void
    {
        $generator->generate(ReportExport::query()->findOrFail($this->reportExportId));
    }

    public function failed(Throwable $exception): void
    {
        ReportExport::query()->whereKey($this->reportExportId)->update([
            'status' => 'failed',
            'failure_message' => mb_substr($exception->getMessage(), 0, 1000),
        ]);
    }
}
