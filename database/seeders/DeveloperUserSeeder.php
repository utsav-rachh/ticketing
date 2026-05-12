<?php
namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * (Re)creates the developer sandbox account.
 *
 *     php artisan db:seed --class=DeveloperUserSeeder
 *
 * Email:    dev@altumcredo.com
 * Password: dev@123
 *
 * Safe to run repeatedly — it upserts on the email and always resets the
 * password to the known default. Requires the add_developer_role migration
 * (so the users.role enum accepts 'developer'); run `php artisan migrate` first
 * if you haven't.
 */
class DeveloperUserSeeder extends Seeder
{
    public function run(): void
    {
        $puneCorp = Branch::where('code', 'BR-MH-01')->first();
        $regionId = Region::where('code', 'ST-MH')->value('id');

        $user = User::updateOrCreate(
            ['email' => 'dev@altumcredo.com'],
            [
                'name'              => 'Developer Sandbox',
                'password'          => Hash::make('dev@123'),
                'role'              => 'developer',
                'department'        => 'IT — R&D',
                'employee_id'       => 'DEV-0001',
                'branch_id'         => $puneCorp?->id,
                'region_id'         => $regionId,
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );
        // Make sure a previously soft-deleted row comes back.
        if (method_exists($user, 'trashed') && $user->trashed()) {
            $user->restore();
        }

        $this->command?->info('Developer account ready: dev@altumcredo.com / dev@123');
    }
}
