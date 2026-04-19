<?php
namespace App\Services;

use App\Models\TatConfiguration;
use App\Models\Ticket;

/**
 * Hold-aware TAT math. The wall-clock elapsed time is reduced by any
 * seconds the ticket has spent in 'hold' status so infrastructure
 * procurement waits don't burn the SLA.
 */
class TatCalculator
{
    public static function tatSeconds(string $priority): int
    {
        $cfg = TatConfiguration::forPriority($priority);
        $hours = $cfg?->tat_hours ?? 24;
        return (int) round(((float) $hours) * 3600);
    }

    public static function deadline(Ticket $ticket): \Illuminate\Support\Carbon
    {
        $base = $ticket->created_at ?: now();
        return $base->copy()->addSeconds((int) round($ticket->tat_hours * 3600));
    }

    public static function effectiveElapsedSeconds(Ticket $ticket): int
    {
        return $ticket->effectiveElapsedSeconds();
    }

    public static function isPastWarning(Ticket $ticket): bool
    {
        $cfg = TatConfiguration::forPriority($ticket->priority);
        $pct = $cfg?->warning_threshold_pct ?? 75;
        $totalSeconds = (int) round($ticket->tat_hours * 3600);
        if ($totalSeconds <= 0) return false;
        return self::effectiveElapsedSeconds($ticket) >= ($totalSeconds * $pct / 100);
    }

    public static function isViolated(Ticket $ticket): bool
    {
        $totalSeconds = (int) round($ticket->tat_hours * 3600);
        return self::effectiveElapsedSeconds($ticket) > $totalSeconds;
    }

    /**
     * Called when a ticket is being moved OUT of 'hold'. Accumulates
     * hold duration into hold_total_seconds on the ticket.
     */
    public static function releaseHold(Ticket $ticket): void
    {
        if ($ticket->hold_started_at) {
            $ticket->hold_total_seconds = (int) ($ticket->hold_total_seconds ?? 0)
                + $ticket->hold_started_at->diffInSeconds(now());
            $ticket->hold_started_at = null;
        }
    }

    /**
     * Called when a ticket is being moved INTO 'hold'.
     */
    public static function beginHold(Ticket $ticket): void
    {
        $ticket->hold_started_at = now();
    }
}
