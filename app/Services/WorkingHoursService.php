<?php
namespace App\Services;

use App\Models\WorkingHour;
use Illuminate\Support\Carbon;

/**
 * Working-hours math, used by TAT for SLA budgets that should only burn
 * during business hours (Mon-Sat 09:00-18:00 by default).
 *
 * The clock pauses outside working windows and on non-working days. All
 * math is done in seconds and Carbon, no DB writes.
 */
class WorkingHoursService
{
    /** @var array<int, array{is_working: bool, start: string, end: string}>|null */
    protected static ?array $cache = null;

    protected function rules(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        $rules = [];
        foreach (WorkingHour::all() as $row) {
            $rules[(int) $row->day_of_week] = [
                'is_working' => (bool) $row->is_working_day,
                'start'      => substr((string) $row->start_time, 0, 8),
                'end'        => substr((string) $row->end_time, 0, 8),
            ];
        }
        // Defaults if table is empty: Mon-Sat 09:00-18:00, Sun off.
        for ($d = 0; $d <= 6; $d++) {
            if (!isset($rules[$d])) {
                $rules[$d] = [
                    'is_working' => $d !== 0,
                    'start'      => '09:00:00',
                    'end'        => '18:00:00',
                ];
            }
        }
        return self::$cache = $rules;
    }

    public static function clearCache(): void { self::$cache = null; }

    protected function dayWindow(Carbon $date): ?array
    {
        $r = $this->rules()[(int) $date->dayOfWeek];
        if (!$r['is_working']) return null;
        return [
            'start' => $date->copy()->setTimeFromTimeString($r['start']),
            'end'   => $date->copy()->setTimeFromTimeString($r['end']),
        ];
    }

    public function isWorkingNow(?Carbon $at = null): bool
    {
        $at = $at ? $at->copy() : now();
        $window = $this->dayWindow($at);
        if (!$window) return false;
        return $at->greaterThanOrEqualTo($window['start']) && $at->lessThan($window['end']);
    }

    /**
     * Add N working hours to $from, skipping nights, weekends, and any
     * non-working day. Returns the resulting Carbon timestamp.
     */
    public function addWorkingHours(Carbon $from, float $hours): Carbon
    {
        $remaining = (int) round($hours * 3600);
        $cursor    = $from->copy();
        $guard     = 0;

        while ($remaining > 0) {
            if (++$guard > 1000) break; // safety

            $window = $this->dayWindow($cursor);
            if (!$window) {
                // Non-working day -> jump to start of next day
                $cursor = $cursor->copy()->addDay()->startOfDay();
                continue;
            }

            // If we're before today's window, skip to its start
            if ($cursor->lessThan($window['start'])) {
                $cursor = $window['start']->copy();
                continue;
            }
            // If we're at/after today's window, jump to start of next day
            if ($cursor->greaterThanOrEqualTo($window['end'])) {
                $cursor = $cursor->copy()->addDay()->startOfDay();
                continue;
            }

            $secondsLeftToday = $window['end']->diffInSeconds($cursor, false);
            // diffInSeconds(false) returns negative if $window['end'] < $cursor; we already handled that.
            $secondsLeftToday = abs($secondsLeftToday);

            if ($remaining <= $secondsLeftToday) {
                $cursor = $cursor->copy()->addSeconds($remaining);
                $remaining = 0;
                break;
            }

            $remaining -= $secondsLeftToday;
            $cursor = $cursor->copy()->addDay()->startOfDay();
        }

        return $cursor;
    }

    /**
     * Working-hour seconds elapsed between two timestamps. Used for SLA progress
     * (elapsed / budget). Order-independent (returns 0 if to <= from).
     */
    public function workingSecondsBetween(Carbon $from, Carbon $to): int
    {
        if ($to->lessThanOrEqualTo($from)) return 0;

        $cursor = $from->copy();
        $total  = 0;
        $guard  = 0;

        while ($cursor->lessThan($to)) {
            if (++$guard > 1000) break;

            $window = $this->dayWindow($cursor);
            if (!$window) {
                $cursor = $cursor->copy()->addDay()->startOfDay();
                continue;
            }
            if ($cursor->lessThan($window['start'])) {
                $cursor = $window['start']->copy();
                continue;
            }
            if ($cursor->greaterThanOrEqualTo($window['end'])) {
                $cursor = $cursor->copy()->addDay()->startOfDay();
                continue;
            }

            $segmentEnd = $window['end']->lessThan($to) ? $window['end'] : $to;
            $total += (int) abs($segmentEnd->diffInSeconds($cursor, false));
            $cursor = $segmentEnd->copy();
        }

        return $total;
    }
}
