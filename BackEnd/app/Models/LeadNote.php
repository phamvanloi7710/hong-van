<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

#[Fillable(['lead_id', 'body', 'created_by', 'created_at'])]
final class LeadNote extends Model
{
    use HasPublicId;

    public $timestamps = false;

    protected $table = 'hongvan_lead_notes';

    protected static function booted(): void
    {
        self::updating(fn () => throw new LogicException('Lead notes are append-only.'));
    }

    /** @return BelongsTo<Lead, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected function casts(): array
    {
        return ['body' => 'encrypted', 'created_at' => 'immutable_datetime'];
    }
}
