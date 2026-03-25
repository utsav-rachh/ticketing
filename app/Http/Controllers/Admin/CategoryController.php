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

    public function store(Request $request)
    {
        $data = $request->validate([
            'support_type' => 'required|in:application,infrastructure,admin',
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
        ]);
        Category::create($data + ['is_active' => true, 'sort_order' => Category::max('sort_order') + 1]);
        return back()->with('success', 'Category created.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);
        $category->update($data);
        return back()->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        $category->update(['is_active' => false]);
        return back()->with('success', 'Category deactivated.');
    }
}
