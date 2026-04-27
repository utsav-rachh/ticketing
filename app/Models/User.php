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
        'name','email','password','role','resolver_level','department','reports_to','phone',
        'employee_id','branch_id','region_id','assigned_region_id','assigned_support_type',
        'is_management','is_active','email_verified_at',
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
        'is_management'     => 'boolean',
    ];

    public function supervisor()      { return $this->belongsTo(User::class, 'reports_to'); }
    public function subordinates()    { return $this->hasMany(User::class, 'reports_to'); }
    public function tickets()         { return $this->hasMany(Ticket::class, 'created_by'); }
    public function assignedTickets() { return $this->hasMany(Ticket::class, 'assigned_to'); }
    public function branch()          { return $this->belongsTo(Branch::class); }
    public function region()          { return $this->belongsTo(Region::class); }
    public function assignedRegion()  { return $this->belongsTo(Region::class, 'assigned_region_id'); }
    public function assignedRegions() { return $this->belongsToMany(Region::class, 'resolver_regions'); }

    public function isEmployee(): bool   { return $this->role === 'employee'; }
    public function isResolver(): bool   { return $this->role === 'resolver'; }
    public function isAdmin(): bool      { return $this->role === 'admin'; }
    public function isManagement(): bool { return (bool) $this->is_management; }
    public function isJunior(): bool     { return $this->isResolver() && $this->resolver_level === 'junior'; }
    public function isTL(): bool         { return $this->isResolver() && $this->resolver_level === 'tl'; }
    public function isITHead(): bool     { return $this->isResolver() && $this->resolver_level === 'it_head'; }

    public function canAssign(): bool    { return $this->isResolver() || $this->isAdmin(); }
    public function canExport(): bool    { return $this->isResolver() || $this->isAdmin(); }
    public function canApproveExpenses(): bool { return $this->isITHead(); }
    public function canManageAdmin(): bool     { return $this->isAdmin(); }
    public function canViewAuditLogs(): bool   { return $this->isAdmin(); }

    /**
     * Branch ids this user can see tickets for, per the 3-level
     * Branch -> Regional -> Head hierarchy.
     *   - employee: their own branch
     *   - regional (no resolver_level but region_id set w/ no branch): all branches in region
     *   - head/admin/it_head: every branch
     */
    public function visibleBranchIds(): array
    {
        if ($this->isAdmin() || $this->isITHead()) {
            return Branch::query()->pluck('id')->all();
        }
        if ($this->branch_id) {
            return [$this->branch_id];
        }
        if ($this->region_id) {
            return Branch::where('region_id', $this->region_id)->pluck('id')->all();
        }
        return [];
    }
}
