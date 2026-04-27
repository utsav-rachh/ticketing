<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorAttachment extends Model
{
    public $timestamps = false;
    protected $fillable = ['vendor_id','uploaded_by','file_name','file_path','file_size','mime_type','comment'];
    protected $casts = ['created_at' => 'datetime'];

    public function vendor()   { return $this->belongsTo(Vendor::class); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }
}
