<?php
namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use Auditable;

    protected $fillable = ['name','code','is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function branches()  { return $this->hasMany(Branch::class); }
    public function users()     { return $this->hasMany(User::class); }

    public function scopeActive($q) { return $q->where('is_active', true); }
}
