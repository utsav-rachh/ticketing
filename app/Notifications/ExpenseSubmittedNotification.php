<?php
namespace App\Notifications;

use App\Models\TicketExpense;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpenseSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(public TicketExpense $expense) {}

    public function via($notifiable): array { return ['database','mail']; }

    public function toDatabase($notifiable): array
    {
        return [
            'expense_id'    => $this->expense->id,
            'ticket_id'     => $this->expense->ticket_id,
            'ticket_number' => $this->expense->ticket->ticket_number ?? null,
            'amount'        => (float) $this->expense->amount,
            'submitted_by'  => $this->expense->addedBy->name ?? null,
            'message'       => "Expense of ₹" . number_format($this->expense->amount, 2)
                . " submitted on ticket " . ($this->expense->ticket->ticket_number ?? ''),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Expense pending approval')
            ->line("An expense has been submitted for your approval.")
            ->line("**Ticket:** " . ($this->expense->ticket->ticket_number ?? ''))
            ->line("**Amount:** ₹" . number_format($this->expense->amount, 2))
            ->line("**Submitted by:** " . ($this->expense->addedBy->name ?? ''))
            ->action('Review Expense', url('/expenses/approvals'));
    }
}
