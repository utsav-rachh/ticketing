<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkingHour;
use App\Services\WorkingHoursService;
use Illuminate\Http\Request;

class WorkingHourController extends Controller
{
    public function index()
    {
        $rows = WorkingHour::orderBy('day_of_week')->get()->keyBy('day_of_week');
        // Ensure 0..6 are all present in the view (defaults if missing).
        $hours = collect();
        for ($d = 0; $d <= 6; $d++) {
            $hours->push($rows->get($d) ?? new WorkingHour([
                'day_of_week'    => $d,
                'is_working_day' => $d !== 0,
                'start_time'     => '09:00:00',
                'end_time'       => '18:00:00',
            ]));
        }
        return view('admin.working-hours.index', ['hours' => $hours]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'days'                  => 'required|array',
            'days.*.day_of_week'    => 'required|integer|min:0|max:6',
            'days.*.is_working_day' => 'sometimes|boolean',
            'days.*.start_time'     => 'required|date_format:H:i',
            'days.*.end_time'       => 'required|date_format:H:i|after:days.*.start_time',
        ]);

        foreach ($data['days'] as $day) {
            WorkingHour::updateOrCreate(
                ['day_of_week' => $day['day_of_week']],
                [
                    'is_working_day' => (bool) ($day['is_working_day'] ?? false),
                    'start_time'     => $day['start_time'] . ':00',
                    'end_time'       => $day['end_time']   . ':00',
                ]
            );
        }
        WorkingHoursService::clearCache();
        return back()->with('success', 'Working hours updated.');
    }
}
