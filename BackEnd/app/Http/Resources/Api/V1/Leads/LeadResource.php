<?php

namespace App\Http\Resources\Api\V1\Leads;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class LeadResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $lead = $this->resource;

        return [
            'public_id' => $lead->public_id,
            'type' => $lead->type,
            'status' => $lead->status,
            'source' => $lead->source,
            'contact' => ['name' => $lead->contact_name, 'phone' => $lead->contact_phone, 'email' => $lead->contact_email],
            'original_payload' => $this->when($request->routeIs('admin.api.v1.leads.show'), $lead->original_payload),
            'contact_detail' => $this->whenLoaded('contactDetail', fn () => $lead->contactDetail ? ['company' => $lead->contactDetail->company, 'subject' => $lead->contactDetail->subject, 'message' => $lead->contactDetail->message] : null),
            'quote_items' => $this->whenLoaded('quoteItems', fn () => $lead->quoteItems->map(fn ($item): array => ['product_name' => $item->product_name_snapshot, 'quantity' => $item->quantity, 'unit' => $item->unit, 'notes' => $item->notes])->all()),
            'linked_request' => $this->whenLoaded('requestLink', fn () => $lead->requestLink ? ['transport_request_id' => $lead->requestLink->transportRequest?->public_id, 'warehouse_request_id' => $lead->requestLink->warehouseRequest?->public_id] : null),
            'assignee' => $lead->assignee ? ['public_id' => $lead->assignee->public_id, 'name' => $lead->assignee->name] : null,
            'allowed_transitions' => array_values((array) config('leads.transitions.'.$lead->status, [])),
            'timeline' => $this->whenLoaded('statusHistories', fn () => $lead->statusHistories->map(fn ($history): array => ['from_status' => $history->from_status, 'to_status' => $history->to_status, 'actor' => $history->actor?->name, 'created_at' => $history->created_at?->utc()->toISOString()])->all()),
            'assignments' => $this->whenLoaded('assignments', fn () => $lead->assignments->map(fn ($assignment): array => ['from' => $assignment->fromUser?->name, 'to' => $assignment->toUser?->name, 'actor' => $assignment->actor?->name, 'created_at' => $assignment->created_at?->utc()->toISOString()])->all()),
            'notes' => $this->whenLoaded('notes', fn () => $lead->notes->map(fn ($note): array => ['public_id' => $note->public_id, 'body' => $note->body, 'author' => $note->author?->name, 'created_at' => $note->created_at?->utc()->toISOString()])->all()),
            'consent_at' => $lead->consent_at?->utc()->toISOString(),
            'privacy_policy_version' => $lead->privacy_policy_version,
            'anonymized_at' => $lead->anonymized_at?->utc()->toISOString(),
            'created_at' => $lead->created_at?->utc()->toISOString(),
            'updated_at' => $lead->updated_at?->utc()->toISOString(),
        ];
    }
}
