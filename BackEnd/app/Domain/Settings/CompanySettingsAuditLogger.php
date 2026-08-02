<?php

namespace App\Domain\Settings;

use App\Domain\Audit\AuditTrail;
use App\Models\User;
use App\Support\Http\RequestId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class CompanySettingsAuditLogger
{
    public function __construct(private readonly AuditTrail $auditTrail) {}

    /** @param list<string> $changedKeys */
    public function record(string $event, User $actor, string $subject, array $changedKeys = [], ?Request $request = null): void
    {
        $request ??= request();

        $this->auditTrail->record(
            $event,
            $actor,
            'company_settings',
            $subject,
            after: [
                'changed_keys' => $changedKeys,
                'values_redacted' => true,
            ],
            request: $request,
        );

        Log::notice('Admin company settings event.', [
            'event' => $event,
            'request_id' => RequestId::getOrCreate($request),
            'actor_public_id' => $actor->public_id,
            'subject' => $subject,
            'changed_keys' => $changedKeys,
            'values_redacted' => true,
            'ip_hash' => $this->ipHash($request),
        ]);
    }

    private function ipHash(Request $request): ?string
    {
        $ip = $request->ip();

        return is_string($ip) && $ip !== ''
            ? hash_hmac('sha256', $ip, (string) config('app.key'))
            : null;
    }
}
