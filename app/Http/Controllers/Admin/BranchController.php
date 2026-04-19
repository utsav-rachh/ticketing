<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Region;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::with('region')->orderBy('name')->paginate(25);
        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {
        $regions = Region::active()->orderBy('name')->get();
        return view('admin.branches.edit', ['branch' => new Branch(), 'regions' => $regions]);
    }

    public function store(Request $request)
    {
        Branch::create($this->validated($request));
        return redirect()->route('admin.branches.index')->with('success', 'Branch created.');
    }

    public function edit(Branch $branch)
    {
        $regions = Region::active()->orderBy('name')->get();
        return view('admin.branches.edit', compact('branch','regions'));
    }

    public function update(Request $request, Branch $branch)
    {
        $branch->update($this->validated($request, $branch->id));
        return redirect()->route('admin.branches.index')->with('success', 'Branch updated.');
    }

    public function destroy(Branch $branch)
    {
        $branch->update(['is_active' => false]);
        return back()->with('success', 'Branch deactivated.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name'      => 'required|string|max:150',
            'code'      => 'required|string|max:20|unique:branches,code' . ($id ? ",{$id}" : ''),
            'address'   => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);
    }
}
