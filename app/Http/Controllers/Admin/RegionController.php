<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index()
    {
        $regions = Region::withCount('branches')->orderBy('name')->get();
        return view('admin.regions.index', compact('regions'));
    }

    public function create() { return view('admin.regions.edit', ['region' => new Region()]); }

    public function store(Request $request)
    {
        Region::create($this->validated($request));
        return redirect()->route('admin.regions.index')->with('success', 'Region created.');
    }

    public function edit(Region $region) { return view('admin.regions.edit', compact('region')); }

    public function update(Request $request, Region $region)
    {
        $region->update($this->validated($request, $region->id));
        return redirect()->route('admin.regions.index')->with('success', 'Region updated.');
    }

    public function destroy(Region $region)
    {
        $region->update(['is_active' => false]);
        return back()->with('success', 'Region deactivated.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name'      => 'required|string|max:150',
            'code'      => 'required|string|max:20|unique:regions,code' . ($id ? ",{$id}" : ''),
            'is_active' => 'nullable|boolean',
        ]);
    }
}
