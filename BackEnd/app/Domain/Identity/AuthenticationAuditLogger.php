<?php

namespace App\Domain\Identity;

use App\Models\User;
use App\Support\Http\RequestId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class AuthenticationAuditLogger
{
    public function loginSucceeded(Request $request, User $user, string $email): void
    {
        $this->write($request, 'auth.login.succeeded', $email, $user);
    }

    public function loginFailed(Request $request, string $email, ?User $user, string $reason): void
    {
        $this->write($request, 'auth.login.failed', $email, $user, $reason);
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
            'ip_address' => $request->ip(),
        ];

        if ($reason !== null) {
            $context['reason'] = $reason;
        }

        Log::notice('Admin authentication event.', $context);
    }
}
