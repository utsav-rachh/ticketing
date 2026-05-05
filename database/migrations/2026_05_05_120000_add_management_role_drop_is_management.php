<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('employee','resolver','admin','management') NOT NULL DEFAULT 'employee'");

        if (Schema::hasColumn('users', 'is_management')) {
            DB::table('users')->where('is_management', true)->update(['role' => 'management']);
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_management');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_management')) {
                $table->boolean('is_management')->default(false)->after('assigned_support_type');
            }
        });

        DB::table('users')->where('role', 'management')->update([
            'is_management' => true,
            'role'          => 'employee',
        ]);

        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('employee','resolver','admin') NOT NULL DEFAULT 'employee'");
    }
};
