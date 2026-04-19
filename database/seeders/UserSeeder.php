<?php
namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $north  = Region::where('code', 'RGN-N')->first();
        $south  = Region::where('code', 'RGN-S')->first();
        $east   = Region::where('code', 'RGN-E')->first();
        $west   = Region::where('code', 'RGN-W')->first();
        $mumbai = Branch::where('code', 'BR-MUM-01')->first();

        $users = [
            // Admin
            ['name' => 'Admin',
             'email' => 'admin@altumcredo.com',
             'role' => 'admin',
             'password' => 'admin@123',
             'department' => 'IT'],

            // IT Head — Yogesh
            ['name' => 'Yogesh',
             'email' => 'yogesh@altumcredo.com',
             'role' => 'resolver',
             'resolver_level' => 'it_head',
             'password' => 'ithead@123',
             'department' => 'IT'],

            // Senior Application Support (TL) — Prashant
            ['name' => 'Prashant',
             'email' => 'prashant@altumcredo.com',
             'role' => 'resolver',
             'resolver_level' => 'tl',
             'assigned_support_type' => 'application',
             'password' => 'tl@123',
             'department' => 'IT'],

            // Junior Application Support — Sayali (all regions by default)
            ['name' => 'Sayali',
             'email' => 'sayali@altumcredo.com',
             'role' => 'resolver',
             'resolver_level' => 'junior',
             'assigned_support_type' => 'application',
             'password' => 'junior@123',
             'department' => 'IT'],

            // Senior IT Infrastructure (TL) — Vishal
            ['name' => 'Vishal',
             'email' => 'vishal@altumcredo.com',
             'role' => 'resolver',
             'resolver_level' => 'tl',
             'assigned_support_type' => 'infrastructure',
             'password' => 'tl@123',
             'department' => 'IT'],

            // Junior IT Infrastructure — Utsav
            ['name' => 'Utsav',
             'email' => 'utsav@altumcredo.com',
             'role' => 'resolver',
             'resolver_level' => 'junior',
             'assigned_support_type' => 'infrastructure',
             'password' => 'junior@123',
             'department' => 'IT'],

            // Senior Admin/HR (TL) — placeholder
            ['name' => 'TL Admin HR',
             'email' => 'tl.adminhr@altumcredo.com',
             'role' => 'resolver',
             'resolver_level' => 'tl',
             'assigned_support_type' => 'admin',
             'password' => 'tl@123',
             'department' => 'HR'],

            // Junior Admin/HR — placeholder
            ['name' => 'Junior Admin HR',
             'email' => 'junior.adminhr@altumcredo.com',
             'role' => 'resolver',
             'resolver_level' => 'junior',
             'assigned_support_type' => 'admin',
             'password' => 'junior@123',
             'department' => 'HR'],

            // Management / MD
            ['name' => 'Managing Director',
             'email' => 'md@altumcredo.com',
             'role' => 'employee',
             'password' => 'md@123',
             'is_management' => true,
             'department' => 'Management',
             'branch_id' => $mumbai?->id,
             'region_id' => $west?->id],
        ];

        foreach ($users as $data) {
            $password = $data['password'];
            unset($data['password']);
            User::updateOrCreate(
                ['email' => $data['email']],
                $data + [
                    'password'          => Hash::make($password),
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
