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
        $regions = Region::pluck('id', 'code'); // 'ST-MH' => 1, ...
        $puneCorp = Branch::where('code', 'BR-MH-01')->first();   // Pune Corporate Office
        $hydRO    = Branch::where('code', 'BR-TG-01')->first();   // Hyderabad RO

        // ─── Admin ────────────────────────────────────────────────
        $this->upsert([
            'name'       => 'Admin',
            'email'      => 'admin@altumcredo.com',
            'password'   => 'admin@123',
            'role'       => 'admin',
            'department' => 'IT',
        ]);

        // ─── Resolver hierarchy ───────────────────────────────────
        // IT Head — supervises both TLs
        $yogesh = $this->upsert([
            'name'           => 'Yogesh K Pawar',
            'email'          => 'yogesh@altumcredo.com',
            'password'       => 'ithead@123',
            'role'           => 'resolver',
            'resolver_level' => 'it_head',
            'department'     => 'IT',
            'employee_id'    => '11362',
            'phone'          => '1234561234',
            'branch_id'      => $puneCorp?->id,
            'region_id'      => $regions['ST-MH'] ?? null,
        ]);

        // Application TL — Prashant
        $prashant = $this->upsert([
            'name'                  => 'Prashant Vijay Diwase',
            'email'                 => 'prashant.d@altumcredo.com',
            'password'              => 'tl@123',
            'role'                  => 'resolver',
            'resolver_level'        => 'tl',
            'assigned_support_type' => 'application',
            'department'            => 'IT',
            'employee_id'           => '11630',
            'reports_to'            => $yogesh->id,
            'branch_id'             => $puneCorp?->id,
            'region_id'             => $regions['ST-MH'] ?? null,
        ]);

        // Infra TL — Vishal Savale
        $vishalSavale = $this->upsert([
            'name'                  => 'Vishal Savale',
            'email'                 => 'vishal@altumcredo.com',
            'password'              => 'tl@123',
            'role'                  => 'resolver',
            'resolver_level'        => 'tl',
            'assigned_support_type' => 'infrastructure',
            'department'            => 'IT',
            'employee_id'           => '11727',
            'reports_to'            => $yogesh->id,
            'branch_id'             => $puneCorp?->id,
            'region_id'             => $regions['ST-MH'] ?? null,
        ]);

        // ─── Application juniors (report to Prashant) ─────────────
        $appJuniors = [
            [
                'name'        => 'Sayli Dinesh Upganlawar',
                'email'       => 'sayali@altumcredo.com',
                'employee_id' => '11363',
                'states'      => ['ST-KA', 'ST-TN'],
            ],
            [
                'name'        => 'Omkar Jadhav',
                'email'       => 'omkar.jadhav@altumcredo.com',
                'employee_id' => '13151',
                'states'      => ['ST-AP', 'ST-TG'],
            ],
            [
                'name'        => 'Kalyani Sunil Ganjare',
                'email'       => 'kalyani.g@altumcredo.com',
                'employee_id' => '12615',
                'states'      => ['ST-MH', 'ST-RJ'],
            ],
        ];
        foreach ($appJuniors as $j) {
            $user = $this->upsert([
                'name'                  => $j['name'],
                'email'                 => $j['email'],
                'password'              => 'junior@123',
                'role'                  => 'resolver',
                'resolver_level'        => 'junior',
                'assigned_support_type' => 'application',
                'department'            => 'IT',
                'employee_id'           => $j['employee_id'],
                'reports_to'            => $prashant->id,
                'branch_id'             => $puneCorp?->id,
                'region_id'             => $regions['ST-MH'] ?? null,
            ]);
            $this->syncStates($user, $j['states'], $regions);
        }

        // ─── Infra juniors (report to Vishal Savale) ──────────────
        $infraJuniors = [
            [
                'name'        => 'Vishal Girase',
                'email'       => 'it.trainee@altumcredo.com',
                'employee_id' => '50135',
                'states'      => ['ST-MH', 'ST-RJ', 'ST-KA'],
            ],
            [
                'name'        => 'Kumar Phulchand Saroj',
                'email'       => 'kumar@altumcredo.com',
                'employee_id' => '11233',
                'states'      => ['ST-AP', 'ST-TG', 'ST-TN'],
            ],
        ];
        foreach ($infraJuniors as $j) {
            $user = $this->upsert([
                'name'                  => $j['name'],
                'email'                 => $j['email'],
                'password'              => 'junior@123',
                'role'                  => 'resolver',
                'resolver_level'        => 'junior',
                'assigned_support_type' => 'infrastructure',
                'department'            => 'IT',
                'employee_id'           => $j['employee_id'],
                'reports_to'            => $vishalSavale->id,
                'branch_id'             => $puneCorp?->id,
                'region_id'             => $regions['ST-MH'] ?? null,
            ]);
            $this->syncStates($user, $j['states'], $regions);
        }

        // ─── Management users (auto-critical, auto-flagged on create) ─
        $management = [
            [
                'name'        => 'Hari Shankar Reddy',
                'email'       => 'hari@altumcredo.com',
                'department'  => 'Sales',
                'employee_id' => '10659',
                'phone'       => '9951439000',
                'branch_id'   => $hydRO?->id,
                'region_code' => 'ST-TG',
            ],
            [
                'name'        => 'Pankaj Maduskar',
                'email'       => 'pankaj@altumcredo.com',
                'department'  => 'CEO Office',
                'employee_id' => '14228',
                'phone'       => '8655149712',
            ],
            [
                'name'        => 'Sandeep Upadhyay',
                'email'       => 'sandeep.upadhyay@altumcredo.com',
                'department'  => 'Sales',
                'employee_id' => '12984',
                'phone'       => '3434343434',
            ],
            [
                'name'        => 'Sanjay Chhabinath Tiwari',
                'email'       => 'sanjay@altumcredo.com',
                'department'  => 'CEO Office',
                'employee_id' => '10001',
                'phone'       => '1212122222',
            ],
            [
                'name'        => 'Sarveish Kharangate',
                'email'       => 'sarveish@altumcredo.com',
                'department'  => 'CEO Office',
                'employee_id' => '13385',
                'phone'       => '1122334455',
            ],
            [
                'name'        => 'Vikrant Vishwas Bhagwat',
                'email'       => 'vikrant@altumcredo.com',
                'department'  => 'CEO Office',
                'employee_id' => '10010',
                'phone'       => '1110001001',
            ],
            [
                'name'        => 'Vivek Jain',
                'email'       => 'vivek@altumcredo.com',
                'department'  => 'CEO Office',
                'employee_id' => '10233',
                'phone'       => '3333344444',
            ],
            [
                'name'        => 'Ravindra P Nankar',
                'email'       => 'ravindra.n@altumcredo.com',
                'department'  => 'CEO Office',
                'employee_id' => '12831',
                'phone'       => '1231231234',
            ],
            [
                'name'        => 'Ujjwal Kumar Verma',
                'email'       => 'ujjwal@altumcredo.com',
                'department'  => 'CEO Office',
                'employee_id' => '12671',
                'phone'       => '1234512345',
            ],
        ];

        foreach ($management as $m) {
            $regionCode = $m['region_code'] ?? 'ST-MH';
            $branchId   = $m['branch_id']   ?? $puneCorp?->id;
            $this->upsert([
                'name'           => $m['name'],
                'email'          => $m['email'],
                'password'       => 'mgmt@123',
                'role'           => 'management',
                'department'     => $m['department'],
                'employee_id'    => $m['employee_id'],
                'phone'          => $m['phone'],
                'branch_id'      => $branchId,
                'region_id'      => $regions[$regionCode] ?? null,
            ]);
        }

        // ─── Developer (sandbox-only role; sees Asset Mgmt + Dialer scaffolds) ───
        $this->upsert([
            'name'        => 'Developer Sandbox',
            'email'       => 'dev@altumcredo.com',
            'password'    => 'dev@123',
            'role'        => 'developer',
            'department'  => 'IT — R&D',
            'employee_id' => 'DEV-0001',
            'branch_id'   => $puneCorp?->id,
            'region_id'   => $regions['ST-MH'] ?? null,
        ]);
    }

    private function upsert(array $data): User
    {
        $password = $data['password'] ?? 'password';
        unset($data['password']);

        return User::updateOrCreate(
            ['email' => $data['email']],
            $data + [
                'password'          => Hash::make($password),
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );
    }

    /** @param array<int,string> $codes Region codes like ['ST-MH','ST-RJ'] */
    private function syncStates(User $user, array $codes, $regions): void
    {
        $ids = collect($codes)->map(fn ($c) => $regions[$c] ?? null)->filter()->values()->all();
        $user->assignedRegions()->sync($ids);
        // Keep legacy assigned_region_id in sync with the first state for fallback paths.
        if (!empty($ids)) {
            $user->forceFill(['assigned_region_id' => $ids[0]])->save();
        }
    }
}
