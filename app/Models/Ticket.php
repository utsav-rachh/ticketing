<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ticket_number','support_type','category_id','subcategory_id','branch_id','vendor_id',
        'subject','description','custom_issue','priority','status','is_red_flag',
        'created_by','assigned_to','assigned_by',
        'employee_contact_name','employee_contact_phone','employee_contact_email',
        'assigned_at','hold_started_at','hold_total_seconds',
        'resolved_at','closed_at','tat_hours','tat_deadline','is_tat_violated','tat_notified_at',
    ];

    protected $casts = [
        'assigned_at'        => 'datetime',
        'hold_started_at'    => 'datetime',
        'hold_total_seconds' => 'integer',
        'resolved_at'        => 'datetime',
        'closed_at'          => 'datetime',
        'tat_deadline'       => 'datetime',
        'tat_notified_at'    => 'datetime',
        'is_tat_violated'    => 'boolean',
        'is_red_flag'        => 'boolean',
    ];

    public function creator()    { return $this->belongsTo(User::class, 'created_by'); }
    public function assignee()   { return $this->belongsTo(User::class, 'assigned_to'); }
    public function assigner()   { return $this->belongsTo(User::class, 'assigned_by'); }
    public function category()   { return $this->belongsTo(Category::class); }
    public function subcategory(){ return $this->belongsTo(Subcategory::class); }
    public function branch()     { return $this->belongsTo(Branch::class); }
    public function vendor()     { return $this->belongsTo(Vendor::class); }
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

    /**
     * Seconds elapsed since creation, excluding any time spent on hold.
     */
    public function effectiveElapsedSeconds(): int
    {
        $elapsed = $this->created_at ? $this->created_at->diffInSeconds(now()) : 0;
        $elapsed -= (int) ($this->hold_total_seconds ?? 0);
        if ($this->isOnHold() && $this->hold_started_at) {
            $elapsed -= $this->hold_started_at->diffInSeconds(now());
        }
        return max(0, $elapsed);
    }

    public function isOverdue(): bool
    {
        if (in_array($this->status, ['resolved','closed','hold'])) {
            return false;
        }
        $tatSeconds = (int) round(((float) $this->tat_hours) * 3600);
        return $this->effectiveElapsedSeconds() > $tatSeconds;
    }

    public function tatProgress(): int
    {
        $tatSeconds = (int) round(((float) $this->tat_hours) * 3600);
        if ($tatSeconds <= 0) return 100;
        return min(100, (int) ($this->effectiveElapsedSeconds() / $tatSeconds * 100));
    }

    public static function generateTicketNumber(): string
    {
        $date  = now()->format('Ymd');
        $count = static::whereDate('created_at', today())->count() + 1;
        return sprintf('TKT-%s-%03d', $date, $count);
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
            'closed'       => 'gray',
            default        => 'gray',
        };
    }
}
