<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TatConfiguration;
use Illuminate\Http\Request;

class TATController extends Controller
{
    /** Display order — only the first three are editable. */
    private const ORDER = ['open','in_progress','reopen','pending_info','hold','resolved','closed'];

    public function index()
    {
        $field = "FIELD(status,'" . implode("','", self::ORDER) . "')";
        $configs = TatConfiguration::whereNotNull('status')
            ->orderByRaw($field)
            ->get();
        return view('admin.tat.index', compact('configs'));
    }

    public function update(Request $request, TatConfiguration $config)
    {
        // Only the SLA-bearing statuses can be edited.
        if (!in_array($config->status, TatConfiguration::SLA_STATUSES, true)) {
            return back()->withErrors(['tat_hours' => 'TAT cannot be set for this status.']);
        }

        $data = $request->validate([
            'tat_hours'             => 'required|numeric|min:0.5',
            'warning_threshold_pct' => 'required|integer|min:1|max:99',
            'is_active'             => 'sometimes|boolean',
        ]);
        $config->update([
            'tat_hours'             => $data['tat_hours'],
            'warning_threshold_pct' => $data['warning_threshold_pct'],
            'is_active'             => (bool) ($data['is_active'] ?? false),
        ]);
        return back()->with('success', 'TAT configuration updated.');
    }
}
