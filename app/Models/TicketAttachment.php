<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketAttachment extends Model
{
    public $timestamps = false;
    protected $fillable = ['ticket_id','update_id','uploaded_by','file_name','file_path','file_size','mime_type'];
    protected $casts = ['created_at' => 'datetime'];

    public function ticket()     { return $this->belongsTo(Ticket::class); }
    public function uploader()   { return $this->belongsTo(User::class, 'uploaded_by'); }
    public function update()     { return $this->belongsTo(TicketUpdate::class, 'update_id'); }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }
}
