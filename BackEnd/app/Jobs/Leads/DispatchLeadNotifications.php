<?php

namespace App\Jobs\Leads;

use App\Domain\Identity\PermissionService;
use App\Domain\Settings\CompanySettingsService;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\LeadReceivedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

final class DispatchLeadNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $leadId) {}

    /** @return list<int> */
    public function backoff(): array
    {
        return [10, 60, 300];
    }

    public function handle(CompanySettingsService $settings, PermissionService $permissions): void
    {
        if (! (bool) $settings->value('quote', 'lead_notifications_enabled', true)) {
            return;
        }
        $lead = Lead::query()->findOrFail($this->leadId);
        $notification = new LeadReceivedNotification($lead);
        if ((bool) $settings->value('quote', 'database_notifications_enabled', true)) {
            $users = User::query()->where('is_active', true)->whereNull('locked_at')->get()
                ->filter(fn (User $user): bool => $permissions->allows($user, 'leads.view'));
            Notification::send($users, $notification);
        }
        $recipient = $settings->value('quote', 'recipient_email');
        if (is_string($recipient) && $recipient !== '') {
            Notification::route('mail', $recipient)->notify($notification);
        }
    }
}
