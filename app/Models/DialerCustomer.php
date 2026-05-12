<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DialerCustomer extends Model
{
    protected $fillable = [
        'name', 'phone', 'email', 'company', 'notes', 'imported_from', 'created_by',
    ];

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function tickets() { return $this->hasMany(DialerTicket::class, 'customer_id'); }

    /**
     * Strip spaces / dashes / plus, and drop a leading 91 country code so all
     * Indian numbers collapse to a 10-digit key for dedup + matching.
     */
    public static function normalizePhone(?string $phone): string
    {
        $clean = preg_replace('/\D+/', '', (string) $phone);
        if (str_starts_with($clean, '91') && strlen($clean) === 12) {
            $clean = substr($clean, 2);
        }
        if (str_starts_with($clean, '0') && strlen($clean) === 11) {
            $clean = substr($clean, 1);
        }
        return $clean;
    }

    public static function findByPhone(?string $phone): ?self
    {
        $norm = static::normalizePhone($phone);
        return $norm === '' ? null : static::where('phone', $norm)->first();
    }
}
