<?php
namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = [
            ['name' => 'Dell India Services',        'contact_person' => 'Rajesh Menon', 'phone' => '9812300001', 'email' => 'support@dell.in'],
            ['name' => 'HP Enterprise Support',      'contact_person' => 'Priya Nair',   'phone' => '9812300002', 'email' => 'enterprise@hp.in'],
            ['name' => 'Airtel Business Connectivity','contact_person' => 'Arjun Verma', 'phone' => '9812300003', 'email' => 'biz@airtel.in'],
        ];

        foreach ($vendors as $data) {
            Vendor::updateOrCreate(['name' => $data['name']], $data + ['is_active' => true]);
        }
    }
}
