<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

#[Fillable(['lead_id', 'company', 'subject', 'message', 'created_at'])]
final class LeadContactDetail extends Model
{
    public $timestamps = false;

    protected $table = 'hongvan_lead_contact_details';

    protected static function booted(): void
    {
        self::updating(fn () => throw new LogicException('Original contact details are immutable.'));
    }

    /** @return BelongsTo<Lead, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    protected function casts(): array
    {
        return ['company' => 'encrypted', 'subject' => 'encrypted', 'message' => 'encrypted', 'created_at' => 'immutable_datetime'];
    }
}
