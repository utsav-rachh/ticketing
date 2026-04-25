<?php
namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Region;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            // Andhra Pradesh
            ['region' => 'ST-AP', 'name' => 'Srikakulam',    'code' => 'BR-AP-01'],
            ['region' => 'ST-AP', 'name' => 'Ongole',        'code' => 'BR-AP-02'],
            ['region' => 'ST-AP', 'name' => 'Kadapa',        'code' => 'BR-AP-03'],
            ['region' => 'ST-AP', 'name' => 'Tirupati',      'code' => 'BR-AP-04'],
            ['region' => 'ST-AP', 'name' => 'Vizianagaram',  'code' => 'BR-AP-05'],
            ['region' => 'ST-AP', 'name' => 'Visakhapatnam', 'code' => 'BR-AP-06'],
            ['region' => 'ST-AP', 'name' => 'Vijayawada',    'code' => 'BR-AP-07'],
            ['region' => 'ST-AP', 'name' => 'Nellore',       'code' => 'BR-AP-08'],
            ['region' => 'ST-AP', 'name' => 'Kurnool',       'code' => 'BR-AP-09'],
            ['region' => 'ST-AP', 'name' => 'Guntur',        'code' => 'BR-AP-10'],
            ['region' => 'ST-AP', 'name' => 'Rajahmundry',   'code' => 'BR-AP-11'],

            // Karnataka
            ['region' => 'ST-KA', 'name' => 'Tumkur',        'code' => 'BR-KA-01'],
            ['region' => 'ST-KA', 'name' => 'Nelamangala',   'code' => 'BR-KA-02'],
            ['region' => 'ST-KA', 'name' => 'Bengaluru Main','code' => 'BR-KA-03'],
            ['region' => 'ST-KA', 'name' => 'Devanahalli',   'code' => 'BR-KA-04'],
            ['region' => 'ST-KA', 'name' => 'Mysore',        'code' => 'BR-KA-05'],
            ['region' => 'ST-KA', 'name' => 'Hoskote',       'code' => 'BR-KA-06'],
            ['region' => 'ST-KA', 'name' => 'Chandapura',    'code' => 'BR-KA-07'],
            ['region' => 'ST-KA', 'name' => 'Shivamogga',    'code' => 'BR-KA-08'],
            ['region' => 'ST-KA', 'name' => 'Ramanagara',    'code' => 'BR-KA-09'],
            ['region' => 'ST-KA', 'name' => 'Hassan',        'code' => 'BR-KA-10'],
            ['region' => 'ST-KA', 'name' => 'Davangere',     'code' => 'BR-KA-11'],

            // Maharashtra
            ['region' => 'ST-MH', 'name' => 'Nashik',        'code' => 'BR-MH-01'],
            ['region' => 'ST-MH', 'name' => 'Solapur',       'code' => 'BR-MH-02'],
            ['region' => 'ST-MH', 'name' => 'Kesnand',       'code' => 'BR-MH-03'],
            ['region' => 'ST-MH', 'name' => 'Chakan',        'code' => 'BR-MH-04'],
            ['region' => 'ST-MH', 'name' => 'Maharashtra RO','code' => 'BR-MH-05'],
            ['region' => 'ST-MH', 'name' => 'Dhule',         'code' => 'BR-MH-06'],
            ['region' => 'ST-MH', 'name' => 'Jalgaon',       'code' => 'BR-MH-07'],
            ['region' => 'ST-MH', 'name' => 'Malegaon',      'code' => 'BR-MH-08'],
            ['region' => 'ST-MH', 'name' => 'Aurangabad',    'code' => 'BR-MH-09'],
            ['region' => 'ST-MH', 'name' => 'Nagpur',        'code' => 'BR-MH-10'],
            ['region' => 'ST-MH', 'name' => 'Sangli',        'code' => 'BR-MH-11'],

            // Rajasthan
            ['region' => 'ST-RJ', 'name' => 'Bhilwara',      'code' => 'BR-RJ-01'],
            ['region' => 'ST-RJ', 'name' => 'Rajsamand',     'code' => 'BR-RJ-02'],
            ['region' => 'ST-RJ', 'name' => 'Alwar',         'code' => 'BR-RJ-03'],
            ['region' => 'ST-RJ', 'name' => 'Agra Road',     'code' => 'BR-RJ-04'],
            ['region' => 'ST-RJ', 'name' => 'Shahpura',      'code' => 'BR-RJ-05'],
            ['region' => 'ST-RJ', 'name' => 'Mandalgarh',    'code' => 'BR-RJ-06'],
            ['region' => 'ST-RJ', 'name' => 'Ajmer',         'code' => 'BR-RJ-07'],
            ['region' => 'ST-RJ', 'name' => 'Jaipur Main',   'code' => 'BR-RJ-08'],
            ['region' => 'ST-RJ', 'name' => 'Sikar',         'code' => 'BR-RJ-09'],
            ['region' => 'ST-RJ', 'name' => 'Chittorgarh',   'code' => 'BR-RJ-10'],
            ['region' => 'ST-RJ', 'name' => 'Jodhpur',       'code' => 'BR-RJ-11'],

            // Tamil Nadu
            ['region' => 'ST-TN', 'name' => 'Erode',         'code' => 'BR-TN-01'],
            ['region' => 'ST-TN', 'name' => 'Coimbatore Main','code' => 'BR-TN-02'],
            ['region' => 'ST-TN', 'name' => 'Thanjavur',     'code' => 'BR-TN-03'],
            ['region' => 'ST-TN', 'name' => 'Tirunelveli',   'code' => 'BR-TN-04'],
            ['region' => 'ST-TN', 'name' => 'Sivakasi',      'code' => 'BR-TN-05'],
            ['region' => 'ST-TN', 'name' => 'Tiruppur',      'code' => 'BR-TN-06'],
            ['region' => 'ST-TN', 'name' => 'Salem',         'code' => 'BR-TN-07'],
            ['region' => 'ST-TN', 'name' => 'Madurai',       'code' => 'BR-TN-08'],
            ['region' => 'ST-TN', 'name' => 'Trichy',        'code' => 'BR-TN-09'],
            ['region' => 'ST-TN', 'name' => 'Namakkal',      'code' => 'BR-TN-10'],

            // Telangana
            ['region' => 'ST-TG', 'name' => 'Jagtial',       'code' => 'BR-TG-01'],
            ['region' => 'ST-TG', 'name' => 'Suryapet',      'code' => 'BR-TG-02'],
            ['region' => 'ST-TG', 'name' => 'Wanaparthy',    'code' => 'BR-TG-03'],
            ['region' => 'ST-TG', 'name' => 'Nalgonda',      'code' => 'BR-TG-04'],
            ['region' => 'ST-TG', 'name' => 'Nizamabad',     'code' => 'BR-TG-05'],
            ['region' => 'ST-TG', 'name' => 'Siddipet',      'code' => 'BR-TG-06'],
            ['region' => 'ST-TG', 'name' => 'Mahbubnagar',   'code' => 'BR-TG-07'],
            ['region' => 'ST-TG', 'name' => 'Hyderabad Main','code' => 'BR-TG-08'],
            ['region' => 'ST-TG', 'name' => 'Karimnagar',    'code' => 'BR-TG-09'],
            ['region' => 'ST-TG', 'name' => 'Khammam',       'code' => 'BR-TG-10'],
            ['region' => 'ST-TG', 'name' => 'L B Nagar',     'code' => 'BR-TG-11'],
            ['region' => 'ST-TG', 'name' => 'Warangal',      'code' => 'BR-TG-12'],
        ];

        foreach ($branches as $data) {
            $region = Region::where('code', $data['region'])->first();
            if (!$region) continue;
            Branch::updateOrCreate(
                ['code' => $data['code']],
                [
                    'region_id' => $region->id,
                    'name'      => $data['name'],
                    'is_active' => true,
                ]
            );
        }
    }
}
