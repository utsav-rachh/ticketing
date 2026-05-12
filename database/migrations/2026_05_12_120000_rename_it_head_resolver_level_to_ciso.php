<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * "IT Head" was renamed to "CISO" across the app. This converts the stored
 * resolver_level value 'it_head' -> 'ciso' and tightens the MySQL enum.
 */
return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            // SQLite has no ENUM constraint — just remap the data.
            DB::table('users')->where('resolver_level', 'it_head')->update(['resolver_level' => 'ciso']);
            return;
        }
        // Widen the enum so both old + new values are valid, remap rows, then drop the legacy value.
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `resolver_level` ENUM('junior','tl','it_head','ciso') NULL");
        DB::table('users')->where('resolver_level', 'it_head')->update(['resolver_level' => 'ciso']);
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `resolver_level` ENUM('junior','tl','ciso') NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            DB::table('users')->where('resolver_level', 'ciso')->update(['resolver_level' => 'it_head']);
            return;
        }
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `resolver_level` ENUM('junior','tl','it_head','ciso') NULL");
        DB::table('users')->where('resolver_level', 'ciso')->update(['resolver_level' => 'it_head']);
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `resolver_level` ENUM('junior','tl','it_head') NULL");
    }
};
