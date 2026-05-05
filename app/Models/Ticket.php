<?php
namespace App\Models;

use App\Services\TatCalculator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ticket_number','support_type','category_id','subcategory_id','branch_id','vendor_id','vendor_reference',
        'project_id',
        'subject','description','custom_issue','priority','status','is_red_flag',
        'created_by','assigned_to','assigned_by',
        'employee_contact_name','employee_contact_phone','employee_contact_email','employee_contact_employee_id',
        'assigned_at','hold_started_at','hold_total_seconds',
        'resolved_at','closed_at','reopen_count','reopened_at',
        'tat_hours','tat_deadline','is_tat_violated','tat_notified_at',
        'status_entered_at','status_tat_deadline',
    ];

    protected $casts = [
        'assigned_at'         => 'datetime',
        'hold_started_at'     => 'datetime',
        'hold_total_seconds'  => 'integer',
        'resolved_at'         => 'datetime',
        'closed_at'           => 'datetime',
        'reopened_at'         => 'datetime',
        'reopen_count'        => 'integer',
        'tat_deadline'        => 'datetime',
        'tat_notified_at'     => 'datetime',
        'status_entered_at'   => 'datetime',
        'status_tat_deadline' => 'datetime',
        'is_tat_violated'     => 'boolean',
        'is_red_flag'         => 'boolean',
    ];

    public function creator()    { return $this->belongsTo(User::class, 'created_by'); }
    public function assignee()   { return $this->belongsTo(User::class, 'assigned_to'); }
    public function assigner()   { return $this->belongsTo(User::class, 'assigned_by'); }
    public function category()   { return $this->belongsTo(Category::class)->withTrashed(); }
    public function subcategory(){ return $this->belongsTo(Subcategory::class)->withTrashed(); }
    public function branch()     { return $this->belongsTo(Branch::class)->withTrashed(); }
    public function vendor()     { return $this->belongsTo(Vendor::class)->withTrashed(); }
    public function project()    { return $this->belongsTo(Project::class)->withTrashed(); }
    public function activities() { return $this->hasMany(TicketActivity::class)->orderBy('created_at'); }
    public function updates()    { return $this->hasMany(TicketUpdate::class)->orderBy('created_at'); }
    public function expenses()   { return $this->hasMany(TicketExpense::class); }
    public function attachments(){ return $this->hasMany(TicketAttachment::class); }

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isAdmin() || $user->isITHead()) {
            return $query;
        }
        if ($user->isTL()) {
            return $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('support_type', $user->assigned_support_type);
            });
        }
        if ($user->isJunior()) {
            return $query->where('assigned_to', $user->id);
        }
        if ($user->isManagement()) {
            return $query->where('created_by', $user->id);
        }
        if ($user->isEmployee()) {
            $branchIds = $user->visibleBranchIds();
            return $query->where(function ($q) use ($user, $branchIds) {
                $q->where('created_by', $user->id);
                if (!empty($branchIds)) {
                    $q->orWhereIn('branch_id', $branchIds);
                }
            });
        }
        return $query->whereRaw('1 = 0');
    }

    public function isOnHold(): bool
    {
        return $this->status === 'hold';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /** SLA progress for the current status (0..100, or null when no clock). */
    public function tatProgress(): ?int
    {
        return app(TatCalculator::class)->progressPct($this);
    }

    /** Past the working-hour deadline of the current status. */
    public function isOverdue(): bool
    {
        return app(TatCalculator::class)->isViolated($this);
    }

    public static function generateTicketNumber(): string
    {
        // Sequential global counter: ACHFPL-0001, ACHFPL-0002, ...
        // withTrashed so soft-deleted tickets don't reuse numbers.
        $count = static::withTrashed()->count() + 1;
        return sprintf('ACHFPL-%04d', $count);
    }

    /**
     * Whole days since the ticket was raised (used by list view + Excel export).
     */
    public function getAgingDaysAttribute(): int
    {
        if (!$this->created_at) return 0;
        return (int) $this->created_at->diffInDays(now());
    }

    /**
     * Compact human-friendly aging string: "12d 3h", "4h 21m", "12m".
     */
    public function getAgingHumanAttribute(): string
    {
        if (!$this->created_at) return '—';
        $diff = $this->created_at->diff(now());
        if ($diff->days >= 1)   return $diff->days . 'd ' . $diff->h . 'h';
        if ($diff->h    >= 1)   return $diff->h . 'h ' . $diff->i . 'm';
        return max(1, $diff->i) . 'm';
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'critical' => 'red',
            'high'     => 'orange',
            'medium'   => 'yellow',
            'low'      => 'green',
            default    => 'gray',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'open'         => 'blue',
            'assigned'     => 'indigo',
            'in_progress'  => 'yellow',
            'pending_info' => 'orange',
            'hold'         => 'purple',
            'resolved'     => 'green',
            'reopen'       => 'pink',
            'closed'       => 'gray',
            default        => 'gray',
        };
    }
}
