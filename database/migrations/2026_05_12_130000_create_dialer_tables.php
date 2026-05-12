<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dialer module (Smartping cloud telephony). Lives in the same database as
 * the ticketing app but is a separate surface — see the Smartping integration
 * reference. Tables: dialer_customers, dialer_tickets, dialer_call_logs,
 * csv_imports.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('dialer_customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 32)->unique();           // normalised; used for dedup
            $table->string('email')->nullable();
            $table->string('company')->nullable();
            $table->text('notes')->nullable();
            $table->string('imported_from')->nullable();       // csv filename or "manual"
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('company');
        });

        Schema::create('dialer_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 20)->unique();     // DLR-0001 ...
            $table->foreignId('customer_id')->nullable()->constrained('dialer_customers')->nullOnDelete();
            $table->string('customer_phone', 32)->nullable();  // snapshot — inbound calls may not match a customer yet
            $table->string('customer_name')->nullable();
            $table->enum('direction', ['inbound', 'outbound']);
            $table->enum('call_status', ['initiated', 'ringing', 'answered', 'completed', 'missed', 'busy', 'failed'])
                  ->default('initiated');
            $table->unsignedInteger('duration')->nullable();   // seconds
            $table->string('recording_url')->nullable();
            $table->string('smartping_call_id')->nullable()->index();  // sessionId from Smartping
            $table->foreignId('agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('agent_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['direction', 'call_status']);
            $table->index('created_at');
        });

        Schema::create('dialer_call_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->nullable()->constrained('dialer_tickets')->cascadeOnDelete();
            $table->string('event');                           // call_initiated, call_answered, call_ended, recording_ready, missed, ...
            $table->json('data')->nullable();                  // raw payload from Smartping
            $table->timestamps();

            $table->index('event');
        });

        Schema::create('csv_imports', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('imported')->default(0);
            $table->unsignedInteger('duplicates')->default(0);
            $table->unsignedInteger('failed')->default(0);
            $table->enum('status', ['processing', 'completed', 'failed'])->default('processing');
            $table->text('error')->nullable();
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('csv_imports');
        Schema::dropIfExists('dialer_call_logs');
        Schema::dropIfExists('dialer_tickets');
        Schema::dropIfExists('dialer_customers');
    }
};
