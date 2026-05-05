<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Sync schema to match codebase.
 *
 * The "create_complete_ticketing_schema" migration that was supposed to add
 * branch/region/vendor columns to users + tickets was deleted before its
 * effects could be verified, leaving the DB short of what the models declare.
 * This migration is idempotent — every column add is guarded by hasColumn —
 * so it's safe to re-run.
 */
return new class extends Migration {
    public function up(): void
    {
        // ---- users ----
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'resolver_level'))         $table->string('resolver_level', 20)->nullable()->after('role');
            if (!Schema::hasColumn('users', 'employee_id'))            $table->string('employee_id', 50)->nullable()->after('phone');
            if (!Schema::hasColumn('users', 'branch_id'))              $table->unsignedBigInteger('branch_id')->nullable()->after('employee_id');
            if (!Schema::hasColumn('users', 'region_id'))              $table->unsignedBigInteger('region_id')->nullable()->after('branch_id');
            if (!Schema::hasColumn('users', 'assigned_region_id'))     $table->unsignedBigInteger('assigned_region_id')->nullable()->after('region_id');
            if (!Schema::hasColumn('users', 'assigned_support_type'))  $table->string('assigned_support_type', 20)->nullable()->after('assigned_region_id');
        });

        // ---- tickets ----
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'branch_id'))                     $table->unsignedBigInteger('branch_id')->nullable()->after('subcategory_id');
            if (!Schema::hasColumn('tickets', 'vendor_id'))                     $table->unsignedBigInteger('vendor_id')->nullable()->after('branch_id');
            // vendor_reference is added by 2026_04_20_000000_add_vendor_reference_to_tickets_table.
            if (!Schema::hasColumn('tickets', 'custom_issue'))                  $table->string('custom_issue', 500)->nullable()->after('description');
            if (!Schema::hasColumn('tickets', 'is_red_flag'))                   $table->boolean('is_red_flag')->default(false)->after('status');
            if (!Schema::hasColumn('tickets', 'hold_started_at'))               $table->timestamp('hold_started_at')->nullable()->after('assigned_at');
            if (!Schema::hasColumn('tickets', 'hold_total_seconds'))            $table->unsignedBigInteger('hold_total_seconds')->default(0)->after('hold_started_at');
            if (!Schema::hasColumn('tickets', 'employee_contact_name'))         $table->string('employee_contact_name')->nullable();
            if (!Schema::hasColumn('tickets', 'employee_contact_phone'))        $table->string('employee_contact_phone', 20)->nullable();
            if (!Schema::hasColumn('tickets', 'employee_contact_email'))        $table->string('employee_contact_email')->nullable();
            if (!Schema::hasColumn('tickets', 'employee_contact_employee_id'))  $table->string('employee_contact_employee_id', 50)->nullable();
        });

        // Widen tickets.status enum to include 'hold' (added later in the codebase).
        try {
            DB::statement("ALTER TABLE `tickets` MODIFY COLUMN `status` ENUM('open','assigned','in_progress','pending_info','hold','resolved','closed') NOT NULL DEFAULT 'open'");
        } catch (\Throwable $e) {
            // Non-MySQL drivers / already widened — ignore.
        }
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            foreach ([
                'employee_contact_employee_id','employee_contact_email','employee_contact_phone','employee_contact_name',
                'hold_total_seconds','hold_started_at','is_red_flag','custom_issue',
                'vendor_id','branch_id',
            ] as $col) {
                if (Schema::hasColumn('tickets', $col)) $table->dropColumn($col);
            }
        });
        Schema::table('users', function (Blueprint $table) {
            foreach ([
                'assigned_support_type','assigned_region_id',
                'region_id','branch_id','employee_id','resolver_level',
            ] as $col) {
                if (Schema::hasColumn('users', $col)) $table->dropColumn($col);
            }
        });
    }
};
