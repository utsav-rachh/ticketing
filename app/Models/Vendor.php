<?php
namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use Auditable;

    protected $fillable = ['name','contact_person','phone','email','notes','is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function tickets() { return $this->hasMany(Ticket::class); }

    public function scopeActive($q) { return $q->where('is_active', true); }
}
