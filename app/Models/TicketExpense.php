<?php
namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class TicketExpense extends Model
{
    use Auditable;

    protected $fillable = [
        'ticket_id','added_by','description','amount','expense_date','invoice_path',
        'status','approved_by','approved_at','rejection_reason',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount'       => 'decimal:2',
        'approved_at'  => 'datetime',
    ];

    public function ticket()    { return $this->belongsTo(Ticket::class); }
    public function addedBy()   { return $this->belongsTo(User::class, 'added_by'); }
    public function approvedBy(){ return $this->belongsTo(User::class, 'approved_by'); }

    public function scopeApproved($q) { return $q->where('status', 'approved'); }
    public function scopePending($q)  { return $q->where('status', 'pending'); }
    public function scopeRejected($q) { return $q->where('status', 'rejected'); }
}
