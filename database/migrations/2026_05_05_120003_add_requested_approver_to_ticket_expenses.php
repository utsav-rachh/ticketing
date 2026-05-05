<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ticket_expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('ticket_expenses', 'requested_approver_id')) {
                $table->foreignId('requested_approver_id')->nullable()->after('added_by')
                    ->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ticket_expenses', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_expenses', 'requested_approver_id')) {
                $table->dropConstrainedForeignId('requested_approver_id');
            }
        });
    }
};
