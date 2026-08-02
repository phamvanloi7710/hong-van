<?php

namespace App\Domain\Audit;

use App\Models\AuditLog;
use App\Models\User;
use App\Support\Http\RequestId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class AuditTrail
{
    public function __construct(private AuditRedactor $redactor) {}

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        string $action,
        ?User $actor = null,
        ?string $subjectType = null,
        ?string $subjectPublicId = null,
        array $before = [],
        array $after = [],
        array $metadata = [],
        ?Request $request = null,
    ): AuditLog {
        $request ??= request();
        $requestId = RequestId::getOrCreate($request);
        $key = (string) config('app.key');
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $requestUser = $request->user();
        $resolvedActor = $actor ?? ($requestUser instanceof User ? $requestUser : null);
        $actorType = $resolvedActor instanceof User
            ? 'user'
            : (app()->runningInConsole() ? 'system' : 'anonymous');

        $audit = AuditLog::query()->create([
            'actor_type' => $actorType,
            'actor_public_id' => $resolvedActor?->public_id,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_public_id' => $subjectPublicId,
            'before_data' => $before === [] ? null : $this->redactor->redact($before),
            'after_data' => $after === [] ? null : $this->redactor->redact($after),
            'metadata' => $metadata === [] ? null : $this->redactor->redact($metadata),
            'ip_hash' => is_string($ip) && $ip !== '' ? hash_hmac('sha256', $ip, $key) : null,
            'user_agent_hash' => is_string($userAgent) && $userAgent !== '' ? hash_hmac('sha256', $userAgent, $key) : null,
            'request_id' => $requestId,
            'occurred_at' => now('UTC'),
        ]);

        $this->writeSecurityLog($audit);

        return $audit;
    }

    private function writeSecurityLog(AuditLog $audit): void
    {
        try {
            Log::channel((string) config('security.logging.channel', 'security'))->notice('Security audit event.', [
                'audit_public_id' => $audit->public_id,
                'action' => $audit->action,
                'actor_public_id' => $audit->actor_public_id,
                'subject_type' => $audit->subject_type,
                'subject_public_id' => $audit->subject_public_id,
                'request_id' => $audit->request_id,
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
