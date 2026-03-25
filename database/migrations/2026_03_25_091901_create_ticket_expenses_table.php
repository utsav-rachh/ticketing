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
            $table->string('receipt_path', 500)->nullable();
            $table->timestamps();
            $table->index('ticket_id');
            $table->index('expense_date');
        });
    }
    public function down(): void { Schema::dropIfExists('ticket_expenses'); }
};
