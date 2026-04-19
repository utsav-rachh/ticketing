<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('subcategories')->orderBy('support_type')->orderBy('sort_order')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.edit', ['category' => new Category()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $category = Category::create($data + ['sort_order' => (int) (Category::max('sort_order') + 1)]);

        // Guarantee "Others" exists for this new category
        Subcategory::updateOrCreate(
            ['category_id' => $category->id, 'name' => 'Others'],
            ['default_priority' => 'medium', 'is_active' => true, 'sort_order' => 9999,
             'description' => 'Not in the list — describe your issue below.']
        );

        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $category->update($this->validated($request));
        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        $category->update(['is_active' => false]);
        return back()->with('success', 'Category deactivated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'support_type' => 'required|in:application,infrastructure,admin',
            'name'         => 'required|string|max:200',
            'description'  => 'nullable|string',
            'is_active'    => 'nullable|boolean',
            'sort_order'   => 'nullable|integer',
        ]);
    }
}
