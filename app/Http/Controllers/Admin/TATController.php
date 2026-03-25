<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TatConfiguration;
use Illuminate\Http\Request;

class TATController extends Controller
{
    public function index()
    {
        $configs = TatConfiguration::orderByRaw("FIELD(priority,'critical','high','medium','low')")->get();
        return view('admin.tat.index', compact('configs'));
    }

    public function update(Request $request, TatConfiguration $config)
    {
        $data = $request->validate([
            'tat_hours'             => 'required|numeric|min:0.5',
            'warning_threshold_pct' => 'required|integer|min:1|max:99',
            'escalation_to_role'    => 'required|string',
        ]);
        $config->update($data);
        return back()->with('success', 'TAT configuration updated.');
    }
}
