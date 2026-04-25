<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketUpdate extends Model
{
    public $timestamps = false;
    protected $fillable = ['ticket_id','user_id','status_from','status_to','note','created_at'];
    protected $casts = ['created_at' => 'datetime'];

    public function ticket()      { return $this->belongsTo(Ticket::class); }
    public function user()        { return $this->belongsTo(User::class); }
    public function attachments() { return $this->hasMany(TicketAttachment::class, 'update_id'); }
}
