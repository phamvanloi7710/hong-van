<?php

namespace App\Domain\Identity;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final readonly class AdminAuthenticator
{
    public function __construct(private AuthenticationAuditLogger $auditLogger) {}

    public function login(Request $request, string $email, string $password, bool $remember): User
    {
        $user = User::query()->where('email', $email)->first();
        $reason = match (true) {
            $user === null => 'credentials',
            ! Hash::check($password, $user->getAuthPassword()) => 'credentials',
            ! $user->is_active => 'inactive',
            $user->locked_at !== null => 'locked',
            default => null,
        };

        if ($reason !== null) {
            $this->auditLogger->loginFailed($request, $email, $user, $reason);

            throw ValidationException::withMessages([
                'email' => [__('auth.credentials_invalid')],
            ]);
        }

        Auth::guard('web')->login($user, $remember);
        $request->session()->regenerate();
        $this->auditLogger->loginSucceeded($request, $user, $email);

        return $user;
    }

    public function logout(Request $request): void
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Auth::forgetGuards();
    }
}
