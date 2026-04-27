<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The `ticket_expenses` table was originally created with a different shape
 * than the current migration declares. This idempotently adds the missing
 * status / approval columns and renames receipt_path -> invoice_path.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('ticket_expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('ticket_expenses', 'status')) {
                $table->enum('status', ['pending','approved','rejected'])->default('pending')->after('expense_date');
            }
            if (!Schema::hasColumn('ticket_expenses', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('status');
            }
            if (!Schema::hasColumn('ticket_expenses', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('ticket_expenses', 'rejection_reason')) {
                $table->string('rejection_reason', 500)->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('ticket_expenses', 'invoice_path')) {
                $table->string('invoice_path', 500)->nullable()->after('expense_date');
            }
        });

        // Backfill invoice_path from legacy receipt_path if both exist, then drop receipt_path.
        if (Schema::hasColumn('ticket_expenses', 'receipt_path') && Schema::hasColumn('ticket_expenses', 'invoice_path')) {
            \DB::statement("UPDATE ticket_expenses SET invoice_path = receipt_path WHERE invoice_path IS NULL AND receipt_path IS NOT NULL");
            Schema::table('ticket_expenses', function (Blueprint $table) {
                $table->dropColumn('receipt_path');
            });
        }
    }

    public function down(): void
    {
        Schema::table('ticket_expenses', function (Blueprint $table) {
            foreach (['rejection_reason','approved_at','approved_by','status','invoice_path'] as $col) {
                if (Schema::hasColumn('ticket_expenses', $col)) $table->dropColumn($col);
            }
        });
    }
};
