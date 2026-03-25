<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ticket_number','support_type','category_id','subcategory_id','subject','description',
        'priority','status','created_by','assigned_to','assigned_by','assigned_at',
        'resolved_at','closed_at','tat_hours','tat_deadline','is_tat_violated','tat_notified_at',
    ];

    protected $casts = [
        'assigned_at'    => 'datetime',
        'resolved_at'    => 'datetime',
        'closed_at'      => 'datetime',
        'tat_deadline'   => 'datetime',
        'tat_notified_at'=> 'datetime',
        'is_tat_violated'=> 'boolean',
    ];

    public function creator()    { return $this->belongsTo(User::class, 'created_by'); }
    public function assignee()   { return $this->belongsTo(User::class, 'assigned_to'); }
    public function assigner()   { return $this->belongsTo(User::class, 'assigned_by'); }
    public function category()   { return $this->belongsTo(Category::class); }
    public function subcategory(){ return $this->belongsTo(Subcategory::class); }
    public function activities() { return $this->hasMany(TicketActivity::class)->orderBy('created_at'); }
    public function expenses()   { return $this->hasMany(TicketExpense::class); }
    public function attachments(){ return $this->hasMany(TicketAttachment::class); }

    public function scopeVisibleTo($query, User $user)
    {
        return match($user->role) {
            'admin', 'md'            => $query,
            'ciso'                   => $query->whereIn('support_type', ['application','infrastructure']),
            'hr_head', 'admin_l1'   => $query->where('support_type', 'admin'),
            'it_lead', 'it_l1'      => $query->where('support_type', 'infrastructure'),
            'app_lead', 'app_l1'    => $query->where('support_type', 'application'),
            'employee'               => $query->where('created_by', $user->id),
            default                  => $query->whereRaw('1 = 0'),
        };
    }

    public function isOverdue(): bool
    {
        return !in_array($this->status, ['resolved','closed']) && now()->gt($this->tat_deadline);
    }

    public function tatProgress(): int
    {
        $total   = $this->created_at->diffInMinutes($this->tat_deadline);
        $elapsed = $this->created_at->diffInMinutes(now());
        if ($total <= 0) return 100;
        return min(100, (int) ($elapsed / $total * 100));
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
            'resolved'     => 'green',
            'closed'       => 'gray',
            default        => 'gray',
        };
    }
}
