<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tat_configurations', function (Blueprint $table) {
            $table->id();
            $table->enum('priority', ['critical','high','medium','low'])->unique();
            $table->decimal('tat_hours', 5, 1);
            $table->integer('warning_threshold_pct')->default(80);
            $table->string('escalation_to_role', 50)->default('it_lead');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('tat_configurations'); }
};
