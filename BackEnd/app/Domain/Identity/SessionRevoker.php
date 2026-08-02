<?php

namespace App\Domain\Identity;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class SessionRevoker
{
    public function revoke(User $user): void
    {
        $user->tokens()->delete();

        DB::table((string) config('session.table', 'hongvan_sessions'))
            ->where('user_id', $user->getKey())
            ->delete();
    }
}
