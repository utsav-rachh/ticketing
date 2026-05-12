<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DialerTicket extends Model
{
    protected $fillable = [
        'ticket_number', 'customer_id', 'customer_phone', 'customer_name',
        'direction', 'call_status', 'duration', 'recording_url',
        'smartping_call_id', 'agent_id', 'agent_name', 'notes',
    ];

    protected $casts = [
        'duration' => 'integer',
    ];

    public const STATUSES   = ['initiated', 'ringing', 'answered', 'completed', 'missed', 'busy', 'failed'];
    public const DIRECTIONS  = ['inbound', 'outbound'];

    public function customer() { return $this->belongsTo(DialerCustomer::class, 'customer_id'); }
    public function agent()    { return $this->belongsTo(User::class, 'agent_id'); }
    public function logs()     { return $this->hasMany(DialerCallLog::class, 'ticket_id')->latest(); }

    public function scopeInbound($q)  { return $q->where('direction', 'inbound'); }
    public function scopeOutbound($q) { return $q->where('direction', 'outbound'); }
    public function scopeMissed($q)   { return $q->where('call_status', 'missed'); }

    public function isMissed(): bool   { return $this->call_status === 'missed'; }
    public function isLive(): bool      { return in_array($this->call_status, ['initiated', 'ringing', 'answered'], true); }
    public function hasRecording(): bool { return filled($this->recording_url); }

    public function durationLabel(): string
    {
        if (! $this->duration) return '—';
        $m = intdiv($this->duration, 60);
        $s = $this->duration % 60;
        return $m > 0 ? sprintf('%dm %02ds', $m, $s) : sprintf('%ds', $s);
    }

    /**
     * Next DLR-#### number. Derived from the highest existing number rather
     * than a row count so deletes don't recycle a number.
     */
    public static function generateNumber(): string
    {
        $last = static::orderByDesc('id')->value('ticket_number');
        $n = $last ? ((int) substr($last, strrpos($last, '-') + 1)) + 1 : 1;
        return sprintf('DLR-%04d', $n);
    }

    public function logEvent(string $event, array $data = []): DialerCallLog
    {
        return $this->logs()->create(['event' => $event, 'data' => $data]);
    }
}
