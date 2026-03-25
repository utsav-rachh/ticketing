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
            ['name'=>'Admin','email'=>'admin@altumcredo.com','role'=>'admin','department'=>'IT'],
            ['name'=>'Managing Director','email'=>'md@altumcredo.com','role'=>'md','department'=>'Management'],
            ['name'=>'Yogesh Pawar','email'=>'yogesh.pawar@altumcredo.com','role'=>'ciso','department'=>'IT'],
            ['name'=>'Aditi Talvalkar','email'=>'aditi.talvalkar@altumcredo.com','role'=>'hr_head','department'=>'HR'],
            ['name'=>'Vishal','email'=>'vishal@altumcredo.com','role'=>'it_lead','department'=>'IT'],
            ['name'=>'Prashant','email'=>'prashant@altumcredo.com','role'=>'app_lead','department'=>'IT'],
            ['name'=>'Kumar','email'=>'kumar@altumcredo.com','role'=>'it_l1','department'=>'IT'],
            ['name'=>'Vishal Giri','email'=>'vishal.giri@altumcredo.com','role'=>'it_l1','department'=>'IT'],
            ['name'=>'Saili','email'=>'saili@altumcredo.com','role'=>'app_l1','department'=>'IT'],
            ['name'=>'Kalyani','email'=>'kalyani@altumcredo.com','role'=>'app_l1','department'=>'IT'],
            ['name'=>'Omkar','email'=>'omkar@altumcredo.com','role'=>'app_l1','department'=>'IT'],
            ['name'=>'Shubham','email'=>'shubham@altumcredo.com','role'=>'admin_l1','department'=>'Admin'],
            ['name'=>'Nilesh','email'=>'nilesh@altumcredo.com','role'=>'admin_l1','department'=>'Admin'],
            ['name'=>'Neelam','email'=>'neelam@altumcredo.com','role'=>'admin_l1','department'=>'Admin'],
            // Employees who raise tickets
            ['name'=>'Rahul Sharma','email'=>'rahul@altumcredo.com','role'=>'employee','department'=>'Sales'],
            ['name'=>'Priya Mehta','email'=>'priya@altumcredo.com','role'=>'employee','department'=>'Finance'],
            ['name'=>'Amit Joshi','email'=>'amit@altumcredo.com','role'=>'employee','department'=>'Operations'],
        ];

        foreach ($users as $data) {
            User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make('password'),
                'role'     => $data['role'],
                'department' => $data['department'],
                'is_active'=> true,
                'email_verified_at' => now(),
            ]);
        }

        // Set reports_to hierarchy
        $md       = User::where('email','md@altumcredo.com')->first();
        $ciso     = User::where('email','yogesh.pawar@altumcredo.com')->first();
        $hrHead   = User::where('email','aditi.talvalkar@altumcredo.com')->first();
        $itLead   = User::where('email','vishal@altumcredo.com')->first();
        $appLead  = User::where('email','prashant@altumcredo.com')->first();

        $ciso->update(['reports_to' => $md->id]);
        $hrHead->update(['reports_to' => $md->id]);
        $itLead->update(['reports_to' => $ciso->id]);
        $appLead->update(['reports_to' => $ciso->id]);

        User::where('role','it_l1')->update(['reports_to' => $itLead->id]);
        User::where('role','app_l1')->update(['reports_to' => $appLead->id]);
        User::where('role','admin_l1')->update(['reports_to' => $hrHead->id]);
    }
}
