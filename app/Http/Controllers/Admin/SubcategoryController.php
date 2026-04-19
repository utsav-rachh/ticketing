<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    public function index(Request $request)
    {
        $categoryId   = $request->input('category_id');
        $subcategories = Subcategory::with('category')
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->orderBy('category_id')->orderBy('sort_order')->paginate(50);
        $categories = Category::orderBy('support_type')->orderBy('sort_order')->get();
        return view('admin.subcategories.index', compact('subcategories','categories','categoryId'));
    }

    public function create(Request $request)
    {
        $categories = Category::orderBy('support_type')->orderBy('sort_order')->get();
        $sub = new Subcategory(['category_id' => $request->input('category_id')]);
        return view('admin.subcategories.edit', ['subcategory' => $sub, 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        Subcategory::create($this->validated($request));
        return redirect()->route('admin.subcategories.index')->with('success', 'Issue type created.');
    }

    public function edit(Subcategory $subcategory)
    {
        $categories = Category::orderBy('support_type')->orderBy('sort_order')->get();
        return view('admin.subcategories.edit', compact('subcategory','categories'));
    }

    public function update(Request $request, Subcategory $subcategory)
    {
        $subcategory->update($this->validated($request));
        return redirect()->route('admin.subcategories.index')->with('success', 'Issue type updated.');
    }

    public function destroy(Subcategory $subcategory)
    {
        if (strcasecmp($subcategory->name, 'Others') === 0) {
            return back()->withErrors(['name' => 'The "Others" row is required on every category and cannot be deleted.']);
        }
        $subcategory->update(['is_active' => false]);
        return back()->with('success', 'Issue type deactivated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'category_id'      => 'required|exists:categories,id',
            'name'             => 'required|string|max:200',
            'description'      => 'nullable|string',
            'default_priority' => 'required|in:critical,high,medium,low',
            'is_active'        => 'nullable|boolean',
            'sort_order'       => 'nullable|integer',
        ]);
    }
}
