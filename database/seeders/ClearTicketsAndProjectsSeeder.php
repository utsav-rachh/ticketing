<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Wipes all ticket + project data while leaving the org setup intact
 * (users, branches, regions, categories, vendors, TAT config, working hours,
 * audit logs).
 *
 * Run on the server with:
 *     php artisan db:seed --class=ClearTicketsAndProjectsSeeder
 *
 * Tables emptied: tickets, ticket_activities, ticket_updates, ticket_expenses,
 * ticket_attachments, projects, notifications (every notification in this app
 * is ticket/expense related). Uploaded attachment files on disk are NOT removed.
 */
class ClearTicketsAndProjectsSeeder extends Seeder
{
    public function run(): void
    {
        $tables = [
            'ticket_attachments',
            'ticket_expenses',
            'ticket_updates',
            'ticket_activities',
            'tickets',
            'projects',
            'notifications',
        ];

        Schema::disableForeignKeyConstraints();
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        Schema::enableForeignKeyConstraints();

        $this->command?->info('Cleared tickets, projects and related rows. Users / org config kept.');
    }
}
