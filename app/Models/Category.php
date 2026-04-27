<?php
namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = ['support_type','name','description','is_active','sort_order'];
    protected $casts = ['is_active' => 'boolean'];

    public function subcategories()
    {
        return $this->hasMany(Subcategory::class);
    }

    public function activeSubcategories()
    {
        return $this->hasMany(Subcategory::class)->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeOfType($query, $type) { return $query->where('support_type', $type); }
}
