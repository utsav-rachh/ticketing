<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('ticket_attachments', function (Blueprint $table) {
            $table->unsignedBigInteger('update_id')->nullable()->after('ticket_id');
            $table->index('update_id');
        });
    }
    public function down(): void {
        Schema::table('ticket_attachments', function (Blueprint $table) {
            $table->dropIndex(['update_id']);
            $table->dropColumn('update_id');
        });
    }
};
