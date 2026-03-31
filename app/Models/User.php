<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name','email','password','role','department','reports_to','phone','is_active','email_verified_at',
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'reports_to');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'reports_to');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'created_by');
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function isResolver(): bool { return $this->role === 'resolver'; }
    public function isEmployee(): bool { return $this->role === 'employee'; }
    public function canAssign(): bool { return $this->role === 'resolver'; }
    public function canExport(): bool { return $this->role === 'resolver'; }
}
