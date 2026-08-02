<?php

namespace App\Domain\Settings;

use App\Models\User;
use App\Support\Http\RequestId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class CompanySettingsAuditLogger
{
    /** @param list<string> $changedKeys */
    public function record(string $event, User $actor, string $subject, array $changedKeys = [], ?Request $request = null): void
    {
        $request ??= request();

        Log::notice('Admin company settings event.', [
            'event' => $event,
            'request_id' => RequestId::getOrCreate($request),
            'actor_public_id' => $actor->public_id,
            'subject' => $subject,
            'changed_keys' => $changedKeys,
            'values_redacted' => true,
            'ip_address' => $request->ip(),
        ]);
    }
}
