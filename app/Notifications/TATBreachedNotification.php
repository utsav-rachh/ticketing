<?php
namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TATBreachedNotification extends Notification
{
    use Queueable;

    public function __construct(public Ticket $ticket, public bool $isViolation = true) {}

    public function via($notifiable): array { return ['database','mail']; }

    public function toDatabase($notifiable): array
    {
        $tag = $this->isViolation ? 'breached' : 'approaching TAT';
        return [
            'ticket_id'     => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'subject'       => $this->ticket->subject,
            'priority'      => $this->ticket->priority,
            'status'        => $this->ticket->status,
            'message'       => "[Internal SLA] Ticket {$this->ticket->ticket_number} {$tag} (status: {$this->ticket->status}).",
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $verb = $this->isViolation ? 'breached' : 'approaching';
        return (new MailMessage)
            ->subject("[Internal SLA] Ticket #{$this->ticket->ticket_number} {$verb} TAT")
            ->line("This is an internal SLA notification — do not forward to the ticket creator.")
            ->line("Ticket **{$this->ticket->ticket_number}** is {$verb} the TAT for its current status.")
            ->line("**Status:** " . ucfirst(str_replace('_',' ',$this->ticket->status)))
            ->line("**Priority:** " . ucfirst($this->ticket->priority))
            ->action('View Ticket', url("/tickets/{$this->ticket->id}"));
    }
}
