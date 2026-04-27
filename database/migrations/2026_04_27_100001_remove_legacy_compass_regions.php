<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Old seeders shipped East/West/North/South regions (codes RGN-E/W/N/S).
 * These have been superseded by Indian-state regions (ST-AP, ST-KA, …).
 * Soft-delete any leftover legacy rows so they no longer pollute dropdowns.
 * Past tickets keep their branch_id; the soft-deleted state name is still
 * resolvable via withTrashed() on the relation.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('regions') || !Schema::hasColumn('regions', 'deleted_at')) {
            return;
        }

        $legacyCodes = ['RGN-N', 'RGN-S', 'RGN-E', 'RGN-W'];
        $regionIds = DB::table('regions')->whereIn('code', $legacyCodes)->whereNull('deleted_at')->pluck('id');

        if ($regionIds->isEmpty()) return;

        DB::table('regions')->whereIn('id', $regionIds)
            ->update(['is_active' => false, 'deleted_at' => now()]);

        if (Schema::hasColumn('branches', 'deleted_at')) {
            DB::table('branches')->whereIn('region_id', $regionIds)->whereNull('deleted_at')
                ->update(['is_active' => false, 'deleted_at' => now()]);
        }
    }

    public function down(): void
    {
        // No-op: we don't auto-resurrect legacy regions.
    }
};
