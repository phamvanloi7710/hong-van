<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['requested_by', 'type', 'status', 'filters', 'row_count', 'disk', 'file_path', 'failure_message', 'expires_at'])]
final class ReportExport extends Model
{
    use HasPublicId;

    public const STATUSES = ['queued', 'processing', 'ready', 'failed'];

    protected $table = 'hongvan_report_exports';

    /** @return BelongsTo<User, $this> */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'row_count' => 'integer',
            'expires_at' => 'immutable_datetime',
        ];
    }
}
