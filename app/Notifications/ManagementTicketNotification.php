<?php
namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ManagementTicketNotification extends Notification
{
    use Queueable;

    public function __construct(public Ticket $ticket) {}

    public function via($notifiable): array { return ['database','mail']; }

    public function toDatabase($notifiable): array
    {
        return [
            'ticket_id'     => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'subject'       => $this->ticket->subject,
            'priority'      => $this->ticket->priority,
            'red_flag'      => true,
            'message'       => "RED FLAG: Management ticket {$this->ticket->ticket_number} raised by "
                . ($this->ticket->creator->name ?? 'Management'),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("RED FLAG — Management Ticket {$this->ticket->ticket_number}")
            ->error()
            ->line("A management-level ticket has been raised and requires immediate attention.")
            ->line("**Ticket:** {$this->ticket->ticket_number}")
            ->line("**Subject:** {$this->ticket->subject}")
            ->line("**Raised by:** " . ($this->ticket->creator->name ?? 'Management'))
            ->action('View Ticket', url("/tickets/{$this->ticket->id}"));
    }
}
