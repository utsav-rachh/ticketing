<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            // SQLite has no ENUM constraint — role column already accepts any string.
            return;
        }
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('employee','resolver','admin','management','developer') NOT NULL DEFAULT 'employee'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        DB::table('users')->where('role', 'developer')->update(['role' => 'employee']);
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('employee','resolver','admin','management') NOT NULL DEFAULT 'employee'");
    }
};
