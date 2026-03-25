<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TatConfiguration extends Model
{
    protected $fillable = ['priority','tat_hours','warning_threshold_pct','escalation_to_role','is_active'];
    protected $casts = ['is_active' => 'boolean', 'tat_hours' => 'float'];

    public static function forPriority(string $priority): ?self
    {
        return static::where('priority', $priority)->where('is_active', true)->first();
    }
}
