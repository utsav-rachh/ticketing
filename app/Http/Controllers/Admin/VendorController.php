<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::orderBy('name')->paginate(25);
        return view('admin.vendors.index', compact('vendors'));
    }

    public function create() { return view('admin.vendors.edit', ['vendor' => new Vendor()]); }

    public function store(Request $request)
    {
        Vendor::create($this->validated($request));
        return redirect()->route('admin.vendors.index')->with('success', 'Vendor created.');
    }

    public function edit(Vendor $vendor) { return view('admin.vendors.edit', compact('vendor')); }

    public function update(Request $request, Vendor $vendor)
    {
        $vendor->update($this->validated($request));
        return redirect()->route('admin.vendors.index')->with('success', 'Vendor updated.');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->update(['is_active' => false]);
        return back()->with('success', 'Vendor deactivated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'           => 'required|string|max:200',
            'contact_person' => 'nullable|string|max:150',
            'phone'          => 'nullable|string|max:30',
            'email'          => 'nullable|email|max:150',
            'notes'          => 'nullable|string',
            'is_active'      => 'nullable|boolean',
        ]);
    }
}
