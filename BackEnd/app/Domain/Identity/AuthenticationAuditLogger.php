<?php

namespace App\Domain\Identity;

use App\Domain\Audit\AuditTrail;
use App\Models\User;
use App\Support\Http\RequestId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class AuthenticationAuditLogger
{
    public function __construct(private readonly AuditTrail $auditTrail) {}

    public function loginSucceeded(Request $request, User $user, string $email): void
    {
        $this->write($request, 'auth.login.succeeded', $email, $user);
    }

    public function loginFailed(Request $request, string $email, ?User $user, string $reason): void
    {
        $this->write($request, 'auth.login.failed', $email, $user, $reason);
    }

    public function logoutSucceeded(Request $request, ?User $user): void
    {
        $this->auditTrail->record(
            'auth.logout.succeeded',
            $user,
            'user',
            $user?->public_id,
            request: $request,
        );

        Log::notice('Admin authentication event.', [
            'event' => 'auth.logout.succeeded',
            'request_id' => RequestId::getOrCreate($request),
            'user_id' => $user?->getKey(),
            'user_public_id' => $user?->public_id,
            'ip_hash' => $this->ipHash($request),
        ]);
    }

    private function write(
        Request $request,
        string $event,
        string $email,
        ?User $user,
        ?string $reason = null,
    ): void {
        $context = [
            'event' => $event,
            'request_id' => RequestId::getOrCreate($request),
            'user_id' => $user?->getKey(),
            'user_public_id' => $user?->public_id,
            'email_fingerprint' => hash_hmac(
                'sha256',
                Str::lower(trim($email)),
                (string) config('app.key'),
            ),
            'ip_hash' => $this->ipHash($request),
        ];

        if ($reason !== null) {
            $context['reason'] = $reason;
        }

        $this->auditTrail->record(
            $event,
            $user,
            'user',
            $user?->public_id,
            metadata: [
                'email_fingerprint' => $context['email_fingerprint'],
                'reason' => $reason,
            ],
            request: $request,
        );

        Log::notice('Admin authentication event.', $context);
    }

    private function ipHash(Request $request): ?string
    {
        $ip = $request->ip();

        return is_string($ip) && $ip !== ''
            ? hash_hmac('sha256', $ip, (string) config('app.key'))
            : null;
    }
}
