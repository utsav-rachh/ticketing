<?php
namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use Auditable;

    protected $fillable = ['region_id','name','code','address','is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function region()   { return $this->belongsTo(Region::class); }
    public function users()    { return $this->hasMany(User::class); }
    public function tickets()  { return $this->hasMany(Ticket::class); }

    public function scopeActive($q) { return $q->where('is_active', true); }
}
