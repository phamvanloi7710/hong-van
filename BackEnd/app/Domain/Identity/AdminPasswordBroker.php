<?php

namespace App\Domain\Identity;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LogicException;

final class AdminPasswordBroker
{
    public function __construct(private readonly SessionRevoker $sessionRevoker) {}

    public function sendResetLink(string $email): void
    {
        Password::broker()->sendResetLink([
            'email' => $email,
            'is_active' => true,
            'locked_at' => null,
        ]);
    }

    public function reset(
        Request $request,
        string $email,
        string $token,
        string $password,
        string $passwordConfirmation,
    ): void {
        $status = Password::broker()->reset([
            'email' => $email,
            'token' => $token,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
            'is_active' => true,
            'locked_at' => null,
        ], function (CanResetPassword $resettable, string $newPassword): void {
            if (! $resettable instanceof User) {
                throw new LogicException('The admin password broker must resolve an App\\Models\\User.');
            }

            $resettable->forceFill([
                'password' => $newPassword,
                'remember_token' => Str::random(60),
            ])->save();

            $this->sessionRevoker->revoke($resettable);

            event(new PasswordReset($resettable));
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__('auth.password_reset_invalid')],
            ]);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
