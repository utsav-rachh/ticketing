<?php
namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['support_type'=>'application','name'=>'Rapid Sales','sort_order'=>1],
            ['support_type'=>'application','name'=>'FI- Technical','sort_order'=>2],
            ['support_type'=>'application','name'=>'PSD Sales','sort_order'=>3],
            ['support_type'=>'application','name'=>'Credit','sort_order'=>4],
            ['support_type'=>'application','name'=>'OPS','sort_order'=>5],
            ['support_type'=>'application','name'=>'LMS','sort_order'=>6],
            ['support_type'=>'infrastructure','name'=>'Laptop','sort_order'=>1],
            ['support_type'=>'infrastructure','name'=>'Desktop','sort_order'=>2],
            ['support_type'=>'infrastructure','name'=>'Printer','sort_order'=>3],
            ['support_type'=>'infrastructure','name'=>'Internet / Network','sort_order'=>4],
            ['support_type'=>'infrastructure','name'=>'Server','sort_order'=>5],
            ['support_type'=>'infrastructure','name'=>'Accessories','sort_order'=>6],
            ['support_type'=>'admin','name'=>'Facility Management','sort_order'=>1],
            ['support_type'=>'admin','name'=>'Office Supplies','sort_order'=>2],
            ['support_type'=>'admin','name'=>'Travel & Transport','sort_order'=>3],
            ['support_type'=>'admin','name'=>'HR Queries','sort_order'=>4],
            ['support_type'=>'admin','name'=>'Onboarding / Offboarding','sort_order'=>5],
        ];

        foreach ($categories as $cat) {
            Category::create($cat + ['is_active' => true]);
        }
    }
}
