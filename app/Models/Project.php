<?php
namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'number','name','description','owner_id','status','start_date','end_date','created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function owner()   { return $this->belongsTo(User::class, 'owner_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function tickets() { return $this->hasMany(Ticket::class); }

    public function scopeActive($q)    { return $q->where('status', 'active'); }
    public function scopeOnHold($q)    { return $q->where('status', 'on_hold'); }
    public function scopeCompleted($q) { return $q->where('status', 'completed'); }

    public static function generateNumber(): string
    {
        $count = static::withTrashed()->count() + 1;
        return sprintf('ACHFPL-PRJ-%04d', $count);
    }
}
