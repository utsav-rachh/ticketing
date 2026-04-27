<?php
namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'vendor_code','name','contact_person','phone','email','address','notes','is_active',
    ];
    protected $casts = ['is_active' => 'boolean'];

    public function tickets()     { return $this->hasMany(Ticket::class); }
    public function attachments() { return $this->hasMany(VendorAttachment::class)->orderByDesc('created_at'); }

    public function scopeActive($q) { return $q->where('is_active', true); }
}
