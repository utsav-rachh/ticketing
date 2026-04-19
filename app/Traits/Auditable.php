<?php
namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn ($model)  => $model->writeAudit('created', $model->getAttributes()));
        static::updated(fn ($model)  => $model->writeAudit('updated', $model->getChanges()));
        static::deleted(function ($model) {
            $action = method_exists($model, 'isForceDeleting') && $model->isForceDeleting() ? 'force_deleted' : 'deleted';
            $model->writeAudit($action, $model->getOriginal());
        });
    }

    public function writeAudit(string $action, array $changes = []): void
    {
        $user = Auth::user();
        AuditLog::create([
            'user_id'        => $user?->id,
            'auditable_type' => static::class,
            'auditable_id'   => $this->getKey(),
            'action'         => $action,
            'changes'        => $changes ?: null,
            'ip_address'     => Request::ip(),
            'user_agent'     => substr((string) Request::header('User-Agent'), 0, 500),
            'created_at'     => now(),
        ]);
    }
}
