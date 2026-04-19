<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 20)->unique();
            $table->enum('support_type', ['application','infrastructure','admin']);
            $table->foreignId('category_id')->constrained();
            $table->foreignId('subcategory_id')->constrained();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('subject', 500);
            $table->text('description')->nullable();
            $table->string('custom_issue', 500)->nullable();
            $table->enum('priority', ['critical','high','medium','low']);
            $table->enum('status', ['open','assigned','in_progress','pending_info','hold','resolved','closed'])->default('open');
            $table->boolean('is_red_flag')->default(false);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('employee_contact_name')->nullable();
            $table->string('employee_contact_phone', 20)->nullable();
            $table->string('employee_contact_email')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('hold_started_at')->nullable();
            $table->unsignedBigInteger('hold_total_seconds')->default(0);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->decimal('tat_hours', 5, 1);
            $table->timestamp('tat_deadline');
            $table->boolean('is_tat_violated')->default(false);
            $table->timestamp('tat_notified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('support_type');
            $table->index('status');
            $table->index('priority');
            $table->index('created_by');
            $table->index('assigned_to');
            $table->index('branch_id');
            $table->index('vendor_id');
            $table->index('is_red_flag');
            $table->index('is_tat_violated');
            $table->index('tat_deadline');
            $table->index('created_at');
        });
    }
    public function down(): void { Schema::dropIfExists('tickets'); }
};
