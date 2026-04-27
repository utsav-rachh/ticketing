<?php
use App\Models\TatConfiguration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 1) Drop the unique on priority so we can rekey by status.
        Schema::table('tat_configurations', function (Blueprint $table) {
            try { $table->dropUnique(['priority']); } catch (\Throwable $e) {}
        });
        // 2) Allow priority to be null (legacy column, kept for now).
        DB::statement("ALTER TABLE tat_configurations MODIFY priority ENUM('critical','high','medium','low') NULL");

        // 3) Add status + transition columns.
        Schema::table('tat_configurations', function (Blueprint $table) {
            if (!Schema::hasColumn('tat_configurations', 'status')) {
                $table->string('status', 30)->nullable()->after('id');
            }
            if (!Schema::hasColumn('tat_configurations', 'applies_to_transition')) {
                $table->string('applies_to_transition', 60)->nullable()->after('status');
            }
        });
        // 4) Make status unique (allowing nulls for legacy rows).
        try {
            Schema::table('tat_configurations', function (Blueprint $table) {
                $table->unique('status');
            });
        } catch (\Throwable $e) {}

        // 5) Seed the per-status rows. Open/in_progress/reopen are the only
        //    SLA-bearing states. Others are recorded as inactive markers so
        //    the admin UI can show them as "no TAT".
        $rows = [
            ['status' => 'open',         'applies_to_transition' => 'open->in_progress',         'tat_hours' => 2,  'is_active' => true],
            ['status' => 'in_progress',  'applies_to_transition' => 'in_progress->resolved/hold','tat_hours' => 8,  'is_active' => true],
            ['status' => 'reopen',       'applies_to_transition' => 'reopen->in_progress',       'tat_hours' => 2,  'is_active' => true],
            ['status' => 'pending_info', 'applies_to_transition' => '(no clock)',                'tat_hours' => 0,  'is_active' => false],
            ['status' => 'hold',         'applies_to_transition' => '(no clock)',                'tat_hours' => 0,  'is_active' => false],
            ['status' => 'resolved',     'applies_to_transition' => '(no clock)',                'tat_hours' => 0,  'is_active' => false],
            ['status' => 'closed',       'applies_to_transition' => '(no clock)',                'tat_hours' => 0,  'is_active' => false],
        ];

        foreach ($rows as $r) {
            TatConfiguration::updateOrCreate(
                ['status' => $r['status']],
                $r + ['warning_threshold_pct' => 80, 'escalation_to_role' => 'tl']
            );
        }
    }

    public function down(): void {
        Schema::table('tat_configurations', function (Blueprint $table) {
            try { $table->dropUnique(['status']); } catch (\Throwable $e) {}
            if (Schema::hasColumn('tat_configurations', 'applies_to_transition')) {
                $table->dropColumn('applies_to_transition');
            }
            if (Schema::hasColumn('tat_configurations', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
