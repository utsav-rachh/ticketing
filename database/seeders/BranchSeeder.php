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
            ['region' => 'RGN-N', 'name' => 'Delhi HQ',      'code' => 'BR-DEL-01'],
            ['region' => 'RGN-N', 'name' => 'Chandigarh',    'code' => 'BR-CHD-01'],
            ['region' => 'RGN-S', 'name' => 'Bengaluru Main','code' => 'BR-BLR-01'],
            ['region' => 'RGN-S', 'name' => 'Chennai',       'code' => 'BR-CHN-01'],
            ['region' => 'RGN-E', 'name' => 'Kolkata',       'code' => 'BR-KOL-01'],
            ['region' => 'RGN-E', 'name' => 'Bhubaneswar',   'code' => 'BR-BHU-01'],
            ['region' => 'RGN-W', 'name' => 'Mumbai Head',   'code' => 'BR-MUM-01'],
            ['region' => 'RGN-W', 'name' => 'Pune',          'code' => 'BR-PNE-01'],
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
