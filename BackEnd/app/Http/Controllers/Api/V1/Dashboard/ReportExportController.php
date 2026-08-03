<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Domain\Dashboard\DashboardRange;
use App\Domain\Dashboard\ReportExportService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Dashboard\CreateLeadReportRequest;
use App\Http\Responses\ApiResponse;
use App\Models\ReportExport;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReportExportController extends Controller
{
    public function store(CreateLeadReportRequest $request, ReportExportService $exports, ApiResponse $response): JsonResponse
    {
        $actor = $this->actor($request);
        $validated = $request->validated();
        $report = $exports->create($actor, DashboardRange::fromValidated($validated), $validated);

        return $response->success($this->serialize($report), null, $report->status === 'ready' ? 201 : 202);
    }

    public function show(Request $request, ReportExport $reportExport, ApiResponse $response): JsonResponse
    {
        $this->ensureOwner($request, $reportExport);

        return $response->success($this->serialize($reportExport));
    }

    public function download(Request $request, ReportExport $reportExport): StreamedResponse
    {
        $this->ensureOwner($request, $reportExport);
        abort_unless($reportExport->status === 'ready' && CarbonImmutable::parse((string) $reportExport->expires_at)->isFuture(), 404);
        abort_unless(is_string($reportExport->file_path) && Storage::disk($reportExport->disk)->exists($reportExport->file_path), 404);

        return Storage::disk($reportExport->disk)->download(
            $reportExport->file_path,
            'hong-van-leads-'.$reportExport->created_at?->format('Ymd-His').'.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    /** @return array<string, mixed> */
    private function serialize(ReportExport $report): array
    {
        return [
            'public_id' => $report->public_id,
            'type' => $report->type,
            'status' => $report->status,
            'row_count' => $report->row_count,
            'expires_at' => CarbonImmutable::parse((string) $report->expires_at)->utc()->toISOString(),
            'created_at' => CarbonImmutable::parse((string) $report->created_at)->utc()->toISOString(),
            'download_url' => $report->status === 'ready' ? '/api/admin/v1/dashboard/reports/'.$report->public_id.'/download' : null,
        ];
    }

    private function ensureOwner(Request $request, ReportExport $report): void
    {
        abort_unless($report->requested_by === $this->actor($request)->getKey(), 404);
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}
