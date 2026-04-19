<?php
namespace App\Console\Commands;

use App\Models\TatConfiguration;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use App\Notifications\TATBreachedNotification;
use App\Services\TatCalculator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CheckTATViolations extends Command
{
    protected $signature = 'itsm:check-tat';
    protected $description = 'Send TAT warning notifications to TL and escalation to IT Head when thresholds are crossed.';

    public function handle(): void
    {
        $tickets = Ticket::query()
            ->whereNotIn('status', ['resolved','closed','hold'])
            ->with(['assignee'])
            ->get();

        $warningsSent = 0;
        $violationsSent = 0;

        foreach ($tickets as $ticket) {
            $cfg = TatConfiguration::forPriority($ticket->priority);
            $warningPct = $cfg?->warning_threshold_pct ?? 75;
            $totalSec = (int) round($ticket->tat_hours * 3600);
            if ($totalSec <= 0) continue;

            $elapsedSec = $ticket->effectiveElapsedSeconds();
            $pct = $elapsedSec * 100 / $totalSec;

            // VIOLATION (>= 100%): notify IT Head(s), mark violated
            if ($pct >= 100 && !$ticket->is_tat_violated) {
                $ticket->update(['is_tat_violated' => true, 'tat_notified_at' => now()]);
                $violationsSent++;

                TicketActivity::create([
                    'ticket_id'   => $ticket->id,
                    'user_id'     => $ticket->assigned_to ?? 1,
                    'action_type' => 'note_added',
                    'description' => "TAT VIOLATED — escalated to IT Head",
                ]);

                $itHeads = User::where('role','resolver')->where('resolver_level','it_head')
                    ->where('is_active', true)->get();
                Notification::send($itHeads, new TATBreachedNotification($ticket));

                // also loop TL in
                if ($ticket->assignee && $ticket->assignee->isJunior()) {
                    $tl = User::where('role','resolver')->where('resolver_level','tl')
                        ->where('assigned_support_type', $ticket->support_type)
                        ->where('is_active', true)->first();
                    $tl?->notify(new TATBreachedNotification($ticket));
                }
                continue;
            }

            // WARNING threshold crossed: notify TL once (we flag with tat_notified_at)
            if ($pct >= $warningPct && !$ticket->tat_notified_at) {
                $ticket->update(['tat_notified_at' => now()]);
                $warningsSent++;

                $tl = User::where('role','resolver')->where('resolver_level','tl')
                    ->where('assigned_support_type', $ticket->support_type)
                    ->where('is_active', true)->first();
                $tl?->notify(new TATBreachedNotification($ticket));
            }
        }

        $this->info("Warnings: {$warningsSent} · Violations: {$violationsSent}");
    }
}
