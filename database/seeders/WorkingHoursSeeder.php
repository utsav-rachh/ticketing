<?php
namespace Database\Seeders;

use App\Models\WorkingHour;
use Illuminate\Database\Seeder;

class WorkingHoursSeeder extends Seeder
{
    public function run(): void
    {
        // Mon-Sat 09:00-18:00, Sunday off.
        for ($d = 0; $d <= 6; $d++) {
            WorkingHour::updateOrCreate(
                ['day_of_week' => $d],
                [
                    'is_working_day' => $d !== 0,
                    'start_time'     => '09:00:00',
                    'end_time'       => '18:00:00',
                ]
            );
        }
    }
}
