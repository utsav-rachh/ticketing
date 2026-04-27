<?php
namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    /** Fields that should never be persisted to the audit log. */
    protected static array $auditIgnore = ['updated_at', 'created_at', 'password', 'remember_token'];

    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->writeAudit('created', static::scrubAuditValues($model->getAttributes()));
        });
        static::updated(function ($model) {
            $changes = $model->getChanges();
            $original = $model->getOriginal();
            $diff = [];
            foreach ($changes as $field => $newValue) {
                if (in_array($field, static::$auditIgnore, true)) continue;
                $diff[$field] = [
                    'from' => $original[$field] ?? null,
                    'to'   => $newValue,
                ];
            }
            if (!empty($diff)) {
                $model->writeAudit('updated', $diff);
            }
        });
        static::deleted(function ($model) {
            $action = method_exists($model, 'isForceDeleting') && $model->isForceDeleting() ? 'force_deleted' : 'deleted';
            $model->writeAudit($action, static::scrubAuditValues($model->getOriginal()));
        });
    }

    protected static function scrubAuditValues(array $values): array
    {
        return collect($values)
            ->reject(fn ($_, $key) => in_array($key, static::$auditIgnore, true))
            ->all();
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
            'user_agent'     => substr((string) Request::header('User-Agent'), 0, 500),
            'created_at'     => now(),
        ]);
    }
}
