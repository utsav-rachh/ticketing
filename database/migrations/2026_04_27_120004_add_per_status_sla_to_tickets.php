<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'status_entered_at')) {
                $table->timestamp('status_entered_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('tickets', 'status_tat_deadline')) {
                $table->timestamp('status_tat_deadline')->nullable()->after('status_entered_at');
            }
            if (!Schema::hasColumn('tickets', 'reopen_count')) {
                $table->unsignedInteger('reopen_count')->default(0)->after('closed_at');
            }
            if (!Schema::hasColumn('tickets', 'reopened_at')) {
                $table->timestamp('reopened_at')->nullable()->after('reopen_count');
            }
        });

        // Backfill: status_entered_at = updated_at for existing rows.
        DB::statement("UPDATE tickets SET status_entered_at = updated_at WHERE status_entered_at IS NULL");
    }

    public function down(): void {
        Schema::table('tickets', function (Blueprint $table) {
            foreach (['status_entered_at','status_tat_deadline','reopen_count','reopened_at'] as $col) {
                if (Schema::hasColumn('tickets', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
