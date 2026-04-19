<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ticket_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('added_by')->constrained('users');
            $table->string('description', 500);
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->string('invoice_path', 500)->nullable();
            $table->enum('status', ['pending','approved','rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('rejection_reason', 500)->nullable();
            $table->timestamps();
            $table->index('ticket_id');
            $table->index('expense_date');
            $table->index('status');
        });
    }
    public function down(): void { Schema::dropIfExists('ticket_expenses'); }
};
