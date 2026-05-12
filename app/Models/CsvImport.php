<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CsvImport extends Model
{
    protected $fillable = [
        'filename', 'total_rows', 'imported', 'duplicates', 'failed', 'status', 'error', 'imported_by',
    ];

    protected $casts = [
        'total_rows' => 'integer',
        'imported'   => 'integer',
        'duplicates' => 'integer',
        'failed'     => 'integer',
    ];

    public function importer() { return $this->belongsTo(User::class, 'imported_by'); }

    public function isProcessing(): bool { return $this->status === 'processing'; }
}
