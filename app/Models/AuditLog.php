<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id','auditable_type','auditable_id','action','changes','ip_address','user_agent','created_at'];
    protected $casts = [
        'changes'    => 'array',
        'created_at' => 'datetime',
    ];

    public function user()      { return $this->belongsTo(User::class); }
    public function auditable() { return $this->morphTo(); }
}
