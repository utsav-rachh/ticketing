<?php
namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = ['region_id','name','code','address','is_active'];
    protected $casts = ['is_active' => 'boolean'];

    // withTrashed so historical tickets/users still resolve a soft-deleted state name.
    public function region()   { return $this->belongsTo(Region::class)->withTrashed(); }
    public function users()    { return $this->hasMany(User::class); }
    public function tickets()  { return $this->hasMany(Ticket::class); }

    public function scopeActive($q) { return $q->where('is_active', true); }
}
