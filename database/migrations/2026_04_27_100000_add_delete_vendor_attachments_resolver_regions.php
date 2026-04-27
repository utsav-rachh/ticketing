<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * - Adds soft-delete to regions, branches, vendors so admins can "delete" them
 *   without losing past ticket references.
 * - Adds vendor_code + address columns to vendors.
 * - Creates vendor_attachments (multi-doc upload).
 * - Creates resolver_regions pivot so a resolver can be auto-assigned tickets
 *   from multiple states.
 * Idempotent — safe to re-run.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            if (!Schema::hasColumn('regions', 'deleted_at')) $table->softDeletes();
        });

        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'deleted_at')) $table->softDeletes();
        });

        Schema::table('vendors', function (Blueprint $table) {
            if (!Schema::hasColumn('vendors', 'vendor_code')) {
                $table->string('vendor_code', 30)->nullable()->after('id');
            }
            if (!Schema::hasColumn('vendors', 'address')) {
                $table->string('address', 500)->nullable()->after('email');
            }
            if (!Schema::hasColumn('vendors', 'deleted_at')) $table->softDeletes();
        });

        if (!Schema::hasTable('vendor_attachments')) {
            Schema::create('vendor_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('file_name', 255);
                $table->string('file_path', 500);
                $table->unsignedBigInteger('file_size')->default(0);
                $table->string('mime_type', 100)->nullable();
                $table->string('comment', 500)->nullable();
                $table->timestamp('created_at')->nullable();
                $table->index('vendor_id');
            });
        }

        if (!Schema::hasTable('resolver_regions')) {
            Schema::create('resolver_regions', function (Blueprint $table) {
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('region_id')->constrained()->cascadeOnDelete();
                $table->primary(['user_id', 'region_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('resolver_regions');
        Schema::dropIfExists('vendor_attachments');

        Schema::table('vendors', function (Blueprint $table) {
            foreach (['address', 'vendor_code', 'deleted_at'] as $col) {
                if (Schema::hasColumn('vendors', $col)) $table->dropColumn($col);
            }
        });
        Schema::table('branches', function (Blueprint $table) {
            if (Schema::hasColumn('branches', 'deleted_at')) $table->dropSoftDeletes();
        });
        Schema::table('regions', function (Blueprint $table) {
            if (Schema::hasColumn('regions', 'deleted_at')) $table->dropSoftDeletes();
        });
    }
};
