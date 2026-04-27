<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketAttachment extends Model
{
    public $timestamps = false;
    protected $fillable = ['ticket_id','update_id','uploaded_by','file_name','file_path','file_size','mime_type'];
    protected $casts = ['created_at' => 'datetime'];

    public function ticket()       { return $this->belongsTo(Ticket::class); }
    public function uploader()     { return $this->belongsTo(User::class, 'uploaded_by'); }
    public function ticketUpdate() { return $this->belongsTo(TicketUpdate::class, 'update_id'); }

    public function isImage(): bool
    {
        return $this->previewKind() === 'image';
    }

    /** Used by the timeline view to pick a preview tile (icon vs thumbnail vs embed). */
    public function previewKind(): string
    {
        $mime = strtolower((string) $this->mime_type);
        $ext  = strtolower(pathinfo((string) $this->file_name, PATHINFO_EXTENSION));

        if (str_starts_with($mime, 'image/') || in_array($ext, ['png','jpg','jpeg','gif','webp'], true)) {
            return 'image';
        }
        if ($mime === 'application/pdf' || $ext === 'pdf') {
            return 'pdf';
        }
        if (in_array($ext, ['xlsx','xls','csv'], true)
            || in_array($mime, ['application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'], true)) {
            return 'excel';
        }
        if (in_array($ext, ['docx','doc'], true)
            || in_array($mime, ['application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document'], true)) {
            return 'word';
        }
        return 'other';
    }
}
