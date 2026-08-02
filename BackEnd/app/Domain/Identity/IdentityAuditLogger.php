<?php

namespace App\Domain\Identity;

use App\Domain\Audit\AuditTrail;
use App\Models\User;
use App\Support\Http\RequestId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class IdentityAuditLogger
{
    public function __construct(private readonly AuditTrail $auditTrail) {}

    /**
     * @param  array<string, bool|int|string|null>  $details
     */
    public function record(
        string $event,
        ?User $actor = null,
        ?string $subjectType = null,
        ?string $subjectPublicId = null,
        array $details = [],
        ?Request $request = null,
    ): void {
        $request ??= request();

        $this->auditTrail->record(
            $event,
            $actor,
            $subjectType,
            $subjectPublicId,
            after: $details,
            request: $request,
        );

        Log::notice('Admin identity event.', [
            'event' => $event,
            'request_id' => RequestId::getOrCreate($request),
            'actor_public_id' => $actor?->public_id,
            'subject_type' => $subjectType,
            'subject_public_id' => $subjectPublicId,
            'details' => $details,
            'ip_hash' => $this->ipHash($request),
        ]);
    }

    public function superAdminBypass(User $user, string $permission, ?Request $request = null): void
    {
        $request ??= request();
        $attribute = 'identity.super_admin_bypass.'.str_replace('.', '_', $permission);

        if ($request->attributes->getBoolean($attribute)) {
            return;
        }

        $request->attributes->set($attribute, true);
        $this->record('identity.super_admin_bypass', $user, 'permission', null, [
            'permission' => $permission,
        ], $request);
    }

    private function ipHash(Request $request): ?string
    {
        $ip = $request->ip();

        return is_string($ip) && $ip !== ''
            ? hash_hmac('sha256', $ip, (string) config('app.key'))
            : null;
    }
}
