<?php
namespace App\Console\Commands;

use App\Jobs\SendTATNotification;
use App\Models\Ticket;
use App\Models\TicketActivity;
use Illuminate\Console\Command;

class CheckTATViolations extends Command
{
    protected $signature = 'itsm:check-tat';
    protected $description = 'Check for TAT violations and send notifications';

    public function handle(): void
    {
        $violated = Ticket::query()
            ->whereNotIn('status', ['resolved','closed'])
            ->where('tat_deadline', '<', now())
            ->where('is_tat_violated', false)
            ->get();

        foreach ($violated as $ticket) {
            $ticket->update(['is_tat_violated' => true, 'tat_notified_at' => now()]);

            TicketActivity::create([
                'ticket_id'   => $ticket->id,
                'user_id'     => 1,
                'action_type' => 'note_added',
                'description' => "TAT VIOLATED — Exceeded {$ticket->tat_hours}h deadline",
            ]);

            SendTATNotification::dispatch($ticket);
        }

        $this->info("Checked: {$violated->count()} violations found.");
    }
}
