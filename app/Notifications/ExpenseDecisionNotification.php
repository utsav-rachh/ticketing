<?php
namespace App\Notifications;

use App\Models\TicketExpense;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpenseDecisionNotification extends Notification
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
            'status'        => $this->expense->status,
            'reason'        => $this->expense->rejection_reason,
            'message'       => 'Your expense has been ' . $this->expense->status
                . ' for ticket ' . ($this->expense->ticket->ticket_number ?? ''),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Expense ' . ucfirst($this->expense->status))
            ->line('Your expense submission has been ' . $this->expense->status . '.')
            ->line('**Ticket:** ' . ($this->expense->ticket->ticket_number ?? ''))
            ->line('**Amount:** ₹' . number_format($this->expense->amount, 2));

        if ($this->expense->status === 'rejected' && $this->expense->rejection_reason) {
            $mail->line('**Reason:** ' . $this->expense->rejection_reason);
        }

        return $mail->action('View Ticket', url('/tickets/' . $this->expense->ticket_id));
    }
}
