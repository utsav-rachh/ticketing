<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketExpense extends Model
{
    protected $fillable = ['ticket_id','added_by','description','amount','expense_date','receipt_path'];
    protected $casts = ['expense_date' => 'date', 'amount' => 'decimal:2'];

    public function ticket() { return $this->belongsTo(Ticket::class); }
    public function addedBy(){ return $this->belongsTo(User::class, 'added_by'); }
}
