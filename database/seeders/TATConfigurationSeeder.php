<?php
namespace Database\Seeders;

use App\Models\TatConfiguration;
use Illuminate\Database\Seeder;

class TATConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            ['priority'=>'critical','tat_hours'=>2,  'warning_threshold_pct'=>50,'escalation_to_role'=>'resolver'],
            ['priority'=>'high',    'tat_hours'=>4,  'warning_threshold_pct'=>75,'escalation_to_role'=>'resolver'],
            ['priority'=>'medium',  'tat_hours'=>8,  'warning_threshold_pct'=>80,'escalation_to_role'=>'resolver'],
            ['priority'=>'low',     'tat_hours'=>24, 'warning_threshold_pct'=>80,'escalation_to_role'=>'resolver'],
        ];

        foreach ($configs as $config) {
            TatConfiguration::create($config + ['is_active' => true]);
        }
    }
}
