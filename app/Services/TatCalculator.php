<?php
namespace App\Services;

use App\Models\TatConfiguration;
use App\Models\Ticket;
use Illuminate\Support\Carbon;

/**
 * Per-status, working-hour-aware SLA math.
 *
 * Each status has its own TAT budget (open=2h, in_progress=8h, reopen=2h);
 * Hold/Pending Info/Resolved/Closed are budget-less. The clock only burns
 * during configured working windows (Mon-Sat 09:00-18:00 by default).
 *
 * TAT is purely internal: never surface to the ticket creator.
 */
class TatCalculator
{
    public function __construct(protected WorkingHoursService $workingHours) {}

    /**
     * Working-hour deadline for a given status starting at $from. Returns
     * null when the status has no SLA budget (e.g. hold, resolved).
     */
    public function deadlineForStatus(string $status, Carbon $from): ?Carbon
    {
        $cfg = TatConfiguration::forStatus($status);
        if (!$cfg || (float) $cfg->tat_hours <= 0) {
            return null;
        }
        return $this->workingHours->addWorkingHours($from->copy(), (float) $cfg->tat_hours);
    }

    /** Has the current status burned past its deadline? */
    public function isViolated(Ticket $ticket): bool
    {
        if (!in_array($ticket->status, TatConfiguration::SLA_STATUSES, true)) return false;
        if (!$ticket->status_tat_deadline) return false;
        return now()->greaterThan($ticket->status_tat_deadline);
    }

    /**
     * 0..100 SLA progress for the current status, in working-hour seconds.
     * Returns null when the status has no clock.
     */
    public function progressPct(Ticket $ticket): ?int
    {
        if (!in_array($ticket->status, TatConfiguration::SLA_STATUSES, true)) return null;
        $cfg = TatConfiguration::forStatus($ticket->status);
        if (!$cfg || (float) $cfg->tat_hours <= 0) return null;
        $start = $ticket->status_entered_at ?: $ticket->created_at;
        if (!$start) return null;

        $budgetSec  = (int) round($cfg->tat_hours * 3600);
        $elapsedSec = $this->workingHours->workingSecondsBetween($start, now());
        if ($budgetSec <= 0) return 100;
        return min(100, (int) floor($elapsedSec * 100 / $budgetSec));
    }

    /** Working-hour seconds elapsed in the current status. */
    public function elapsedSeconds(Ticket $ticket): int
    {
        $start = $ticket->status_entered_at ?: $ticket->created_at;
        if (!$start) return 0;
        return $this->workingHours->workingSecondsBetween($start, now());
    }

    /** Should warning notifications fire (>= warning threshold of budget)? */
    public function isPastWarning(Ticket $ticket): bool
    {
        if (!in_array($ticket->status, TatConfiguration::SLA_STATUSES, true)) return false;
        $cfg = TatConfiguration::forStatus($ticket->status);
        if (!$cfg || (float) $cfg->tat_hours <= 0) return false;
        $pct = $this->progressPct($ticket);
        return $pct !== null && $pct >= ($cfg->warning_threshold_pct ?? 80);
    }
}
