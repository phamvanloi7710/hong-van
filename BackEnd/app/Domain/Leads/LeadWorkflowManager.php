<?php

namespace App\Domain\Leads;

use App\Domain\Audit\AuditTrail;
use App\Models\Lead;
use App\Models\LeadNote;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class LeadWorkflowManager
{
    public function __construct(private AuditTrail $audit) {}

    public function transition(Lead $lead, User $actor, string $status): Lead
    {
        $from = $lead->status;
        $allowed = (array) config('leads.transitions.'.$lead->status, []);
        if (! in_array($status, $allowed, true)) {
            throw ValidationException::withMessages(['status' => [__('leads.invalid_transition')]]);
        }

        DB::transaction(function () use ($actor, $from, $lead, $status): void {
            $attributes = ['status' => $status];
            if ($status === 'contacted' && $lead->first_contacted_at === null) {
                $attributes['first_contacted_at'] = now('UTC');
            }
            if (in_array($status, ['done', 'spam', 'archived'], true)) {
                $attributes['resolved_at'] = now('UTC');
            }
            $lead->forceFill($attributes)->save();
            $lead->statusHistories()->create(['from_status' => $from, 'to_status' => $status, 'changed_by' => $actor->getKey(), 'created_at' => now('UTC')]);
        });
        $this->audit->record('lead.status.changed', $actor, 'lead', $lead->public_id, before: ['status' => $from], after: ['status' => $status, 'type' => $lead->type]);

        return $lead->fresh($this->relations());
    }

    public function assign(Lead $lead, User $actor, ?User $assignee): Lead
    {
        DB::transaction(function () use ($actor, $assignee, $lead): void {
            $from = $lead->assigned_to;
            $lead->forceFill(['assigned_to' => $assignee?->getKey()])->save();
            $lead->assignments()->create(['from_user_id' => $from, 'to_user_id' => $assignee?->getKey(), 'assigned_by' => $actor->getKey(), 'created_at' => now('UTC')]);
        });
        $this->audit->record('lead.assignment.changed', $actor, 'lead', $lead->public_id, after: ['type' => $lead->type, 'assigned_user_public_id' => $assignee?->public_id]);

        return $lead->fresh($this->relations());
    }

    public function note(Lead $lead, User $actor, string $body): LeadNote
    {
        $note = $lead->notes()->create(['body' => $body, 'created_by' => $actor->getKey(), 'created_at' => now('UTC')]);
        $this->audit->record('lead.note.added', $actor, 'lead', $lead->public_id, after: ['type' => $lead->type, 'note_public_id' => $note->public_id]);

        return $note->load('author');
    }

    /** @return list<string> */
    private function relations(): array
    {
        return ['assignee', 'contactDetail', 'quoteItems', 'requestLink.transportRequest', 'requestLink.warehouseRequest', 'assignments.fromUser', 'assignments.toUser', 'assignments.actor', 'statusHistories.actor', 'notes.author'];
    }
}
