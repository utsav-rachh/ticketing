<?php
namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            ['name' => 'North',  'code' => 'RGN-N'],
            ['name' => 'South',  'code' => 'RGN-S'],
            ['name' => 'East',   'code' => 'RGN-E'],
            ['name' => 'West',   'code' => 'RGN-W'],
        ];

        foreach ($regions as $data) {
            Region::updateOrCreate(['code' => $data['code']], $data + ['is_active' => true]);
        }
    }
}
