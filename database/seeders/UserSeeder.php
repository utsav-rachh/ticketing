<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name'=>'Resolver','email'=>'resolver@altumcredo.com','role'=>'resolver','department'=>'IT'],
            ['name'=>'Vishal','email'=>'vishal@altumcredo.com','role'=>'employee','department'=>'IT'],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'password' => Hash::make($data['role'] . '@123'),
                    'role'     => $data['role'],
                    'department' => $data['department'],
                    'is_active'=> true,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
