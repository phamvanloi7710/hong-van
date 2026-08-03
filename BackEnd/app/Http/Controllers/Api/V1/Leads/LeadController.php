<?php

namespace App\Http\Controllers\Api\V1\Leads;

use App\Domain\Leads\LeadVisibility;
use App\Domain\Leads\LeadWorkflowManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Leads\AssignLeadRequest;
use App\Http\Requests\Api\V1\Leads\StoreLeadNoteRequest;
use App\Http\Requests\Api\V1\Leads\UpdateLeadFollowUpRequest;
use App\Http\Requests\Api\V1\Leads\UpdateLeadStatusRequest;
use App\Http\Resources\Api\V1\Leads\LeadResource;
use App\Http\Responses\ApiResponse;
use App\Models\Lead;
use App\Models\User;
use App\Support\CsvCell;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class LeadController extends Controller
{
    public function __construct(
        private readonly LeadVisibility $visibility,
    ) {}

    public function index(Request $request, ApiResponse $response): JsonResponse
    {
        $filters = $request->validate(['type' => ['nullable', 'in:'.implode(',', Lead::TYPES)], 'status' => ['nullable', 'in:'.implode(',', Lead::STATUSES)], 'assignment' => ['nullable', 'in:mine,unassigned,assigned'], 'per_page' => ['nullable', 'integer', 'between:1,100']]);
        $actor = $this->actor($request);
        $query = $this->visibility->queryFor($actor)->with('assignee')->latest();
        $query->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type));
        $query->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status));
        $query->when(($filters['assignment'] ?? null) === 'mine', fn ($query) => $query->where('assigned_to', $actor->getKey()));
        $query->when(($filters['assignment'] ?? null) === 'unassigned', fn ($query) => $query->whereNull('assigned_to'));
        $query->when(($filters['assignment'] ?? null) === 'assigned', fn ($query) => $query->whereNotNull('assigned_to'));
        $items = $query->paginate((int) ($filters['per_page'] ?? 25));

        return $response->success(['items' => LeadResource::collection($items->getCollection())->resolve($request), 'meta' => ['current_page' => $items->currentPage(), 'last_page' => $items->lastPage(), 'total' => $items->total()]]);
    }

    public function show(Request $request, Lead $lead, ApiResponse $response): JsonResponse
    {
        $this->ensureVisible($request, $lead);

        return $response->success(LeadResource::make($lead->load($this->relations()))->resolve($request));
    }

    public function status(UpdateLeadStatusRequest $request, Lead $lead, LeadWorkflowManager $workflow, ApiResponse $response): JsonResponse
    {
        $this->ensureVisible($request, $lead);

        return $response->success(LeadResource::make($workflow->transition($lead, $this->actor($request), (string) $request->validated('status')))->resolve($request), __('leads.updated'));
    }

    public function assign(AssignLeadRequest $request, Lead $lead, LeadWorkflowManager $workflow, ApiResponse $response): JsonResponse
    {
        abort_unless($this->visibility->canView($this->actor($request), $lead) && $this->canViewAll($request), 404);
        $publicId = $request->validated('user_id');
        $assignee = is_string($publicId) ? User::query()->where('public_id', $publicId)->firstOrFail() : null;

        return $response->success(LeadResource::make($workflow->assign($lead, $this->actor($request), $assignee))->resolve($request), __('leads.updated'));
    }

    public function note(StoreLeadNoteRequest $request, Lead $lead, LeadWorkflowManager $workflow, ApiResponse $response): JsonResponse
    {
        $this->ensureVisible($request, $lead);
        $workflow->note($lead, $this->actor($request), (string) $request->validated('body'));

        return $response->success(LeadResource::make($lead->fresh()->load($this->relations()))->resolve($request), __('leads.updated'), 201);
    }

    public function followUp(UpdateLeadFollowUpRequest $request, Lead $lead, LeadWorkflowManager $workflow, ApiResponse $response): JsonResponse
    {
        $this->ensureVisible($request, $lead);

        $nextFollowUpAt = $request->validated('next_follow_up_at');

        return $response->success(LeadResource::make($workflow->scheduleFollowUp($lead, $this->actor($request), is_string($nextFollowUpAt) ? $nextFollowUpAt : null))->resolve($request), __('leads.updated'));
    }

    public function metrics(Request $request, ApiResponse $response): JsonResponse
    {
        $query = $this->visibility->queryFor($this->actor($request));
        $byStatus = (clone $query)->selectRaw('status, COUNT(*) AS aggregate')->groupBy('status')->pluck('aggregate', 'status');
        $byType = (clone $query)->selectRaw('type, COUNT(*) AS aggregate')->groupBy('type')->pluck('aggregate', 'type');

        return $response->success(['total' => (clone $query)->count(), 'unassigned' => (clone $query)->whereNull('assigned_to')->count(), 'new_today' => (clone $query)->where('created_at', '>=', now('UTC')->startOfDay())->count(), 'by_status' => $byStatus, 'by_type' => $byType]);
    }

    public function assignees(Request $request, ApiResponse $response): JsonResponse
    {
        if (! $this->canViewAll($request)) {
            $actor = $this->actor($request);

            return $response->success([['public_id' => $actor->public_id, 'name' => $actor->name, 'email' => $actor->email]]);
        }

        return $response->success(User::query()->where('is_active', true)->whereNull('locked_at')->orderBy('name')->get()->map(fn (User $user): array => ['public_id' => $user->public_id, 'name' => $user->name, 'email' => $user->email])->all());
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->validate(['type' => ['nullable', 'in:'.implode(',', Lead::TYPES)], 'status' => ['nullable', 'in:'.implode(',', Lead::STATUSES)]]);
        $query = $this->visibility->queryFor($this->actor($request))->with('assignee')->latest();
        $query->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type));
        $query->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status));

        return response()->streamDownload(function () use ($query): void {
            $stream = fopen('php://output', 'wb');
            abort_unless($stream !== false, 500);
            fputcsv($stream, ['public_id', 'type', 'status', 'contact_name', 'contact_phone', 'contact_email', 'assignee', 'created_at']);
            foreach ($query->limit(max(1, (int) config('leads.export_limit', 5000)))->get() as $lead) {
                fputcsv($stream, array_map(CsvCell::sanitize(...), [$lead->public_id, $lead->type, $lead->status, $lead->contact_name, $lead->contact_phone, $lead->contact_email, $lead->assignee?->name, $lead->created_at?->utc()->toISOString()]));
            }
            fclose($stream);
        }, 'leads-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function ensureVisible(Request $request, Lead $lead): void
    {
        abort_unless($this->visibility->canView($this->actor($request), $lead), 404);
    }

    private function canViewAll(Request $request): bool
    {
        return $this->visibility->canViewAll($this->actor($request));
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }

    /** @return list<string> */
    private function relations(): array
    {
        return ['assignee', 'contactDetail', 'quoteItems', 'requestLink.transportRequest', 'requestLink.warehouseRequest', 'assignments.fromUser', 'assignments.toUser', 'assignments.actor', 'statusHistories.actor', 'notes.author'];
    }
}
