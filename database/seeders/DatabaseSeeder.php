<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RegionSeeder::class,
            BranchSeeder::class,
            VendorSeeder::class,
            CategorySeeder::class,
            SubcategorySeeder::class,
            TATConfigurationSeeder::class,
            UserSeeder::class,
        ]);
    }
}
