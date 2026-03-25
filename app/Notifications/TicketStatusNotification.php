<?php
namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusNotification extends Notification
{
    use Queueable;

    public function __construct(public Ticket $ticket, public string $oldStatus, public string $newStatus) {}

    public function via($notifiable): array { return ['database','mail']; }

    public function toDatabase($notifiable): array
    {
        return [
            'ticket_id'     => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'message'       => "Ticket {$this->ticket->ticket_number} status changed to {$this->newStatus}",
            'old_status'    => $this->oldStatus,
            'new_status'    => $this->newStatus,
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Ticket Update: {$this->ticket->ticket_number}")
            ->line("Your ticket **{$this->ticket->ticket_number}** status changed to **{$this->newStatus}**.")
            ->action('View Ticket', url("/tickets/{$this->ticket->id}"));
    }
}
