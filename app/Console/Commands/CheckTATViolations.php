<?php
namespace App\Console\Commands;

use App\Models\TatConfiguration;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use App\Notifications\TATBreachedNotification;
use App\Services\TatCalculator;
use App\Services\WorkingHoursService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CheckTATViolations extends Command
{
    protected $signature = 'itsm:check-tat';
    protected $description = 'Per-status, working-hour-aware SLA checker. Notifies TLs at warning threshold and TLs+CISO at breach. Internal only.';

    public function handle(TatCalculator $tat, WorkingHoursService $working): void
    {
        // Honour working hours: outside business hours we don't burn the SLA,
        // so we don't fire fresh notifications either.
        if (!$working->isWorkingNow()) {
            $this->info('Outside working hours — skipping.');
            return;
        }

        $tickets = Ticket::query()
            ->whereIn('status', TatConfiguration::SLA_STATUSES)
            ->whereNotNull('status_tat_deadline')
            ->with(['assignee','creator'])
            ->get();

        $warningsSent   = 0;
        $violationsSent = 0;

        foreach ($tickets as $ticket) {
            $cfg = TatConfiguration::forStatus($ticket->status);
            if (!$cfg) continue;

            $pct = $tat->progressPct($ticket);
            if ($pct === null) continue;

            $warningPct = $cfg->warning_threshold_pct ?? 80;

            // VIOLATION (>= 100%): notify TL + CISO, mark violated, log activity.
            if ($pct >= 100 && !$ticket->is_tat_violated) {
                $ticket->update(['is_tat_violated' => true, 'tat_notified_at' => now()]);
                $violationsSent++;

                TicketActivity::create([
                    'ticket_id'   => $ticket->id,
                    'user_id'     => $ticket->assigned_to ?? optional($ticket->creator)->id,
                    'action_type' => 'tat_violated',
                    'description' => "[Internal] SLA violated for status '{$ticket->status}' — escalated to TL + CISO",
                ]);

                Notification::send(
                    $this->internalRecipients($ticket, ['tl','ciso']),
                    new TATBreachedNotification($ticket, true)
                );
                continue;
            }

            // WARNING threshold crossed: notify TL once.
            if ($pct >= $warningPct && !$ticket->tat_notified_at) {
                $ticket->update(['tat_notified_at' => now()]);
                $warningsSent++;

                Notification::send(
                    $this->internalRecipients($ticket, ['tl']),
                    new TATBreachedNotification($ticket, false)
                );
            }
        }

        $this->info("Warnings: {$warningsSent} · Violations: {$violationsSent}");
    }

    /**
     * Build the internal-only recipient list. Always excludes the ticket
     * creator, even if they happen to be a resolver/admin (they would still
     * be the wrong person to escalate to about their own ticket).
     *
     * @param  array<int,string> $levels  resolver levels to include
     */
    protected function internalRecipients(Ticket $ticket, array $levels)
    {
        return User::query()
            ->where('role', 'resolver')
            ->whereIn('resolver_level', $levels)
            ->where('is_active', true)
            ->where(function ($q) use ($ticket) {
                $q->where('assigned_support_type', $ticket->support_type)
                  ->orWhereNull('assigned_support_type');
            })
            ->where('id', '!=', $ticket->created_by)
            ->get()
            ->unique('id');
    }
}
