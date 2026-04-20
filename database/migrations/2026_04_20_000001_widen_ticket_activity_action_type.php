<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        DB::statement("ALTER TABLE ticket_activities MODIFY action_type VARCHAR(50) NOT NULL");
    }
    public function down(): void {
        DB::statement("ALTER TABLE ticket_activities MODIFY action_type ENUM('created','assigned','status_changed','note_added','expense_added','resolved','closed','reopened') NOT NULL");
    }
};
