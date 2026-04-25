<?php
namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            ['name' => 'Andhra Pradesh', 'code' => 'ST-AP'],
            ['name' => 'Karnataka',      'code' => 'ST-KA'],
            ['name' => 'Maharashtra',    'code' => 'ST-MH'],
            ['name' => 'Rajasthan',      'code' => 'ST-RJ'],
            ['name' => 'Tamil Nadu',     'code' => 'ST-TN'],
            ['name' => 'Telangana',      'code' => 'ST-TG'],
        ];

        foreach ($regions as $data) {
            Region::updateOrCreate(['code' => $data['code']], $data + ['is_active' => true]);
        }
    }
}
