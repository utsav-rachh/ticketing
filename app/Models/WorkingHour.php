<?php
namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class WorkingHour extends Model
{
    use Auditable;

    protected $fillable = ['day_of_week','is_working_day','start_time','end_time'];
    protected $casts = [
        'day_of_week'    => 'integer',
        'is_working_day' => 'boolean',
    ];

    public const DAY_NAMES = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    public function getDayNameAttribute(): string
    {
        return self::DAY_NAMES[$this->day_of_week] ?? '?';
    }
}
