<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id','auditable_type','auditable_id','action','changes','user_agent','created_at'];
    protected $casts = [
        'changes'    => 'array',
        'created_at' => 'datetime',
    ];

    /** Friendly labels per model for the "Model" column. */
    public const MODEL_LABELS = [
        \App\Models\Branch::class            => 'Branch',
        \App\Models\Category::class          => 'Category',
        \App\Models\Project::class           => 'Project',
        \App\Models\Region::class            => 'State',
        \App\Models\Subcategory::class       => 'Issue Type',
        \App\Models\Vendor::class            => 'Vendor',
        \App\Models\TatConfiguration::class  => 'TAT Config',
        \App\Models\TicketExpense::class     => 'Expense',
        \App\Models\User::class              => 'User',
        \App\Models\WorkingHour::class       => 'Working Hours',
    ];

    /** Friendly field labels for diff output. */
    public const FIELD_LABELS = [
        \App\Models\User::class => [
            'name' => 'Name', 'email' => 'Email', 'role' => 'Role',
            'resolver_level' => 'Level', 'department' => 'Department',
            'phone' => 'Phone', 'employee_id' => 'Employee ID',
            'branch_id' => 'Branch', 'region_id' => 'State',
            'assigned_region_id' => 'Assigned State',
            'assigned_support_type' => 'Support Type',
            'is_active' => 'Active',
            'reports_to' => 'Reports To',
        ],
        \App\Models\Branch::class => [
            'name' => 'Name', 'code' => 'Code', 'region_id' => 'State', 'is_active' => 'Active',
        ],
        \App\Models\Region::class => [
            'name' => 'Name', 'code' => 'Code', 'is_active' => 'Active',
        ],
        \App\Models\Category::class => [
            'name' => 'Name', 'support_type' => 'Support Type', 'is_active' => 'Active', 'sort_order' => 'Order',
        ],
        \App\Models\Subcategory::class => [
            'name' => 'Name', 'category_id' => 'Category', 'default_priority' => 'Default Priority', 'is_active' => 'Active',
        ],
        \App\Models\Vendor::class => [
            'name' => 'Name', 'email' => 'Email', 'phone' => 'Phone', 'address' => 'Address', 'is_active' => 'Active',
        ],
        \App\Models\TatConfiguration::class => [
            'priority' => 'Priority', 'status' => 'Status',
            'tat_hours' => 'TAT (hrs)', 'warning_threshold_pct' => 'Warning %',
            'escalation_to_role' => 'Escalate to', 'is_active' => 'Active',
            'applies_to_transition' => 'Transition',
        ],
        \App\Models\TicketExpense::class => [
            'description' => 'Description', 'amount' => 'Amount',
            'expense_date' => 'Expense Date', 'status' => 'Status',
            'approved_by' => 'Approved By', 'rejection_reason' => 'Rejection',
            'requested_approver_id' => 'Requested Approver',
        ],
        \App\Models\Project::class => [
            'number' => 'Number', 'name' => 'Name', 'description' => 'Description',
            'owner_id' => 'Owner', 'status' => 'Status',
            'start_date' => 'Start Date', 'end_date' => 'End Date',
        ],
        \App\Models\WorkingHour::class => [
            'day_of_week' => 'Day', 'is_working_day' => 'Working',
            'start_time' => 'Start', 'end_time' => 'End',
        ],
    ];

    public function user()      { return $this->belongsTo(User::class); }
    public function auditable() { return $this->morphTo(); }

    /**
     * The DB column is named 'changes', which collides with Eloquent's
     * internal `protected $changes` dirty-tracking property. Accessing
     * `$this->changes` inside model methods returns the EMPTY internal
     * tracker, not the cast attribute. This accessor reads via the
     * attribute pipeline so calls from inside the class work correctly.
     */
    public function changes(): ?array
    {
        $value = $this->getAttribute('changes');
        return is_array($value) ? $value : null;
    }

    public function modelLabel(): string
    {
        return self::MODEL_LABELS[$this->auditable_type] ?? class_basename($this->auditable_type);
    }

    public function entityLabel(): string
    {
        $name = $this->auditable?->name ?? null;
        $changes = $this->changes();
        if (!$name && is_array($changes)) {
            // 'updated' diffs are { field: { from, to } }; created/deleted payloads are { field: value }.
            $raw = $changes['name'] ?? $changes['email'] ?? null;
            if (is_array($raw)) {
                $name = $raw['from'] ?? $raw['to'] ?? null;
            } elseif (is_string($raw)) {
                $name = $raw;
            }
        }
        return $name ? "'{$name}'" : "#{$this->auditable_id}";
    }

    public function fieldLabel(string $field): string
    {
        return self::FIELD_LABELS[$this->auditable_type][$field] ?? str_replace('_', ' ', $field);
    }

    public function actionVerb(): string
    {
        return match ($this->action) {
            'created'       => 'Created',
            'updated'       => 'Updated',
            'deleted'       => 'Deleted',
            'force_deleted' => 'Permanently Deleted',
            default         => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    /**
     * One-line human-readable summary, used as the row headline in the UI.
     * Examples:
     *   "Created Branch 'Andheri East'"
     *   "Updated Vendor 'Acme': name 'X' -> 'Y', email 'a@x' -> 'b@x'"
     *   "Deleted User 'priya@altumcredo.com'"
     */
    public function humanLabel(): string
    {
        $verb   = $this->actionVerb();
        $model  = $this->modelLabel();
        $entity = $this->entityLabel();
        $diff   = $this->changes();

        if ($this->action === 'updated' && is_array($diff)) {
            $bits = [];
            foreach ($diff as $field => $pair) {
                $from = is_array($pair) ? ($pair['from'] ?? null) : null;
                $to   = is_array($pair) ? ($pair['to']   ?? null) : $pair;
                $bits[] = $this->fieldLabel($field) . ': '
                       . $this->renderValue($from) . ' -> ' . $this->renderValue($to);
            }
            return "{$verb} {$model} {$entity}" . (empty($bits) ? '' : ': ' . implode(', ', $bits));
        }
        if ($this->action === 'created' && is_array($diff)) {
            $count = count($diff);
            return "{$verb} {$model} {$entity}" . ($count ? " ({$count} fields)" : '');
        }
        return "{$verb} {$model} {$entity}";
    }

    public function renderValue($value): string
    {
        if ($value === null || $value === '') return "''";
        if (is_bool($value)) return $value ? 'true' : 'false';
        if (is_array($value)) return json_encode($value, JSON_UNESCAPED_UNICODE);
        $str = (string) $value;
        if (mb_strlen($str) > 60) $str = mb_substr($str, 0, 57) . '...';
        return "'{$str}'";
    }
}
