<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        DB::statement("ALTER TABLE tickets MODIFY status ENUM('open','assigned','in_progress','pending_info','hold','resolved','reopen','closed') NOT NULL DEFAULT 'open'");
    }
    public function down(): void {
        DB::statement("ALTER TABLE tickets MODIFY status ENUM('open','assigned','in_progress','pending_info','hold','resolved','closed') NOT NULL DEFAULT 'open'");
    }
};
