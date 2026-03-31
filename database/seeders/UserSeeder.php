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
            ['name'=>'Rahul Sharma','email'=>'rahul@altumcredo.com','role'=>'employee','department'=>'Sales'],
            ['name'=>'Priya Mehta','email'=>'priya@altumcredo.com','role'=>'employee','department'=>'Finance'],
            ['name'=>'Amit Joshi','email'=>'amit@altumcredo.com','role'=>'employee','department'=>'Operations'],
        ];

        foreach ($users as $data) {
            User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['role'] . '@123'),
                'role'     => $data['role'],
                'department' => $data['department'],
                'is_active'=> true,
                'email_verified_at' => now(),
            ]);
        }
    }
}
