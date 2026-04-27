<?php
namespace Database\Seeders;

use App\Models\TatConfiguration;
use Illuminate\Database\Seeder;

class TATConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        // Per-status TAT (working-hour-aware budgets). Open/in_progress/reopen
        // burn the SLA; everything else is dormant.
        $rows = [
            ['status' => 'open',         'applies_to_transition' => 'open->in_progress',         'tat_hours' => 2,  'is_active' => true],
            ['status' => 'in_progress',  'applies_to_transition' => 'in_progress->resolved/hold','tat_hours' => 8,  'is_active' => true],
            ['status' => 'reopen',       'applies_to_transition' => 'reopen->in_progress',       'tat_hours' => 2,  'is_active' => true],
            ['status' => 'pending_info', 'applies_to_transition' => '(no clock)',                'tat_hours' => 0,  'is_active' => false],
            ['status' => 'hold',         'applies_to_transition' => '(no clock)',                'tat_hours' => 0,  'is_active' => false],
            ['status' => 'resolved',     'applies_to_transition' => '(no clock)',                'tat_hours' => 0,  'is_active' => false],
            ['status' => 'closed',       'applies_to_transition' => '(no clock)',                'tat_hours' => 0,  'is_active' => false],
        ];

        foreach ($rows as $row) {
            TatConfiguration::updateOrCreate(
                ['status' => $row['status']],
                $row + ['warning_threshold_pct' => 80, 'escalation_to_role' => 'tl']
            );
        }
    }
}
