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

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isMd(): bool { return $this->role === 'md'; }
    public function isLead(): bool { return in_array($this->role, ['it_lead','app_lead','hr_head','ciso']); }
    public function isL1(): bool { return in_array($this->role, ['it_l1','app_l1','admin_l1']); }
    public function canAssign(): bool { return in_array($this->role, ['admin','md','ciso','hr_head','it_lead','app_lead']); }
    public function canExport(): bool { return in_array($this->role, ['admin','md','ciso','hr_head','it_lead','app_lead']); }
}
