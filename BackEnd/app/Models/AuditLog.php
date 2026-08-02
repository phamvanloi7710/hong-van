<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use LogicException;

#[Fillable([
    'actor_type',
    'actor_public_id',
    'action',
    'subject_type',
    'subject_public_id',
    'before_data',
    'after_data',
    'metadata',
    'ip_hash',
    'user_agent_hash',
    'request_id',
    'occurred_at',
])]
final class AuditLog extends Model
{
    use HasPublicId;

    public $timestamps = false;

    protected $table = 'hongvan_audit_logs';

    protected static function booted(): void
    {
        self::updating(static function (): never {
            throw new LogicException('Audit logs are append-only and cannot be updated.');
        });

        self::deleting(static function (): never {
            throw new LogicException('Audit logs are append-only and cannot be deleted.');
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'before_data' => 'array',
            'after_data' => 'array',
            'metadata' => 'array',
            'occurred_at' => 'immutable_datetime',
        ];
    }
}
