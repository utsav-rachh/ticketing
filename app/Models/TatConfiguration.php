<?php
namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class TatConfiguration extends Model
{
    use Auditable;

    protected $fillable = [
        'priority','status','applies_to_transition','tat_hours',
        'warning_threshold_pct','escalation_to_role','is_active',
    ];
    protected $casts = ['is_active' => 'boolean', 'tat_hours' => 'float'];

    /** Statuses that participate in SLA. */
    public const SLA_STATUSES = ['open', 'assigned', 'in_progress', 'reopen'];

    /**
     * Resolve the active TAT config for a status. The legacy 'assigned'
     * state shares the 'open' bucket — it is just open + auto-assigned.
     */
    public static function forStatus(string $status): ?self
    {
        $key = $status === 'assigned' ? 'open' : $status;
        return static::where('status', $key)->where('is_active', true)->first();
    }

    /**
     * Deprecated. Priority no longer drives TAT — kept as a no-op so any
     * stale callers fail soft instead of crashing.
     */
    public static function forPriority(string $priority): ?self
    {
        return null;
    }
}
