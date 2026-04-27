<?php
namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subcategory extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = ['category_id','name','description','default_priority','is_active','sort_order'];
    protected $casts = ['is_active' => 'boolean'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive($query) { return $query->where('is_active', true); }
}
