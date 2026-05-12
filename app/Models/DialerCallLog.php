<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DialerCallLog extends Model
{
    protected $fillable = ['ticket_id', 'event', 'data'];

    protected $casts = ['data' => 'array'];

    public function ticket() { return $this->belongsTo(DialerTicket::class, 'ticket_id'); }
}
