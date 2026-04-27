<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Ticket;
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

    /**
     * Delete (soft) a category. Refuses if any past ticket references it
     * (or any of its subcategories) — past data must remain intact.
     */
    public function destroy(Category $category)
    {
        $ticketCount = Ticket::withTrashed()->where('category_id', $category->id)->count();
        $subIds = Subcategory::where('category_id', $category->id)->pluck('id');
        $subTicketCount = $subIds->isNotEmpty()
            ? Ticket::withTrashed()->whereIn('subcategory_id', $subIds)->count()
            : 0;

        $totalRefs = $ticketCount + $subTicketCount;
        if ($totalRefs > 0) {
            return back()->withErrors([
                'delete' => "Cannot delete \"{$category->name}\" — it is referenced by {$totalRefs} existing ticket(s). "
                          . "Mark it as Inactive instead so it disappears from new ticket dropdowns while keeping past data intact.",
            ]);
        }

        // Soft-delete subcategories alongside the category to keep things tidy.
        Subcategory::where('category_id', $category->id)->delete();
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', "Category \"{$category->name}\" deleted.");
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
