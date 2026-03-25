<?php
namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAssignedNotification extends Notification
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
            'message'       => "You have been assigned ticket {$this->ticket->ticket_number}",
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Ticket Assigned: {$this->ticket->ticket_number}")
            ->line("You have been assigned ticket **{$this->ticket->ticket_number}**.")
            ->line("**Subject:** {$this->ticket->subject}")
            ->line("**Priority:** " . ucfirst($this->ticket->priority))
            ->action('View Ticket', url("/tickets/{$this->ticket->id}"));
    }
}
