<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use LogicException;

#[Fillable(['type', 'status', 'source', 'contact_name', 'contact_phone', 'contact_email', 'original_payload', 'idempotency_key_hash', 'dedupe_hash', 'ip_hash', 'user_agent_hash', 'consent_at', 'privacy_policy_version', 'assigned_to', 'first_contacted_at', 'next_follow_up_at', 'resolved_at', 'retention_until', 'anonymized_at'])]
final class Lead extends Model
{
    use HasPublicId;

    public const TYPES = ['contact', 'product_quote', 'transport', 'warehouse'];

    public const STATUSES = ['new', 'contacted', 'qualified', 'processing', 'done', 'spam', 'archived'];

    private const IMMUTABLE = ['type', 'source', 'contact_name', 'contact_phone', 'contact_email', 'original_payload', 'idempotency_key_hash', 'ip_hash', 'user_agent_hash', 'consent_at', 'privacy_policy_version', 'retention_until'];

    protected $table = 'hongvan_leads';

    protected static function booted(): void
    {
        self::updating(function (Lead $lead): void {
            foreach (self::IMMUTABLE as $attribute) {
                if ($lead->isDirty($attribute)) {
                    throw new LogicException('Original lead submission fields are immutable.');
                }
            }
        });
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** @return HasOne<LeadContactDetail, $this> */
    public function contactDetail(): HasOne
    {
        return $this->hasOne(LeadContactDetail::class);
    }

    /** @return HasMany<LeadQuoteItem, $this> */
    public function quoteItems(): HasMany
    {
        return $this->hasMany(LeadQuoteItem::class);
    }

    /** @return HasOne<LeadRequestLink, $this> */
    public function requestLink(): HasOne
    {
        return $this->hasOne(LeadRequestLink::class);
    }

    /** @return HasMany<LeadAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(LeadAssignment::class);
    }

    /** @return HasMany<LeadStatusHistory, $this> */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(LeadStatusHistory::class);
    }

    /** @return HasMany<LeadNote, $this> */
    public function notes(): HasMany
    {
        return $this->hasMany(LeadNote::class);
    }

    protected function casts(): array
    {
        return [
            'contact_name' => 'encrypted', 'contact_phone' => 'encrypted', 'contact_email' => 'encrypted',
            'original_payload' => 'encrypted:array', 'consent_at' => 'immutable_datetime',
            'first_contacted_at' => 'immutable_datetime', 'next_follow_up_at' => 'immutable_datetime', 'resolved_at' => 'immutable_datetime',
            'retention_until' => 'immutable_datetime', 'anonymized_at' => 'immutable_datetime',
        ];
    }
}
