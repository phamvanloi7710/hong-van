<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class LeadReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Lead $lead) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return $notifiable instanceof AnonymousNotifiable ? ['mail'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('leads.notification_subject'))
            ->line(__('leads.notification_line', ['type' => $this->lead->type, 'id' => $this->lead->public_id]))
            ->action(__('leads.open_admin'), rtrim((string) config('app.url'), '/').'/admin/leads');
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return ['lead_public_id' => $this->lead->public_id, 'type' => $this->lead->type, 'status' => $this->lead->status, 'url' => '/admin/leads'];
    }
}
