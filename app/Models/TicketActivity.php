<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketActivity extends Model
{
    public $timestamps = false;
    protected $fillable = ['ticket_id','user_id','action_type','description','old_value','new_value'];
    protected $casts = ['created_at' => 'datetime'];

    public function ticket() { return $this->belongsTo(Ticket::class); }
    public function user()   { return $this->belongsTo(User::class); }
}
