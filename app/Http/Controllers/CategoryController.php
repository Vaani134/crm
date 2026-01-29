<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Services\AuditLogService;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount(['products'])
            ->ordered()
            ->get();
            
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string|max:500',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'required|string|max:50',
            'is_active' => 'boolean'
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->name);
        $data['is_active'] = $request->has('is_active');

        $category = Category::create($data);

        // Log category creation
        AuditLogService::log(
            'create_category',
            'categories',
            "Created category '{$category->name}'",
            null,
            $category->toArray()
        );

        return redirect()->route('categories.index')->with('success', 'Category created successfully!');
    }

    public function show(Category $category)
    {
        $category->load(['products' => function($query) {
            $query->orderBy('name');
        }]);
        
        $stats = [
            'total_products' => $category->products->count(),
            'low_stock_products' => $category->products->where('stock_qty', '<=', 5)->count(),
            'total_stock_value' => $category->products->sum(function($product) {
                return $product->price * $product->stock_qty;
            }),
            'out_of_stock' => $category->products->where('stock_qty', 0)->count()
        ];

        return view('categories.show', compact('category', 'stats'));
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'required|string|max:50',
            'is_active' => 'boolean'
        ]);

        $oldData = $category->toArray();
        
        $data = $request->all();
        $data['slug'] = Str::slug($request->name);
        $data['is_active'] = $request->has('is_active');

        $category->update($data);

        // Log category update
        AuditLogService::log(
            'update_category',
            'categories',
            "Updated category '{$category->name}'",
            $oldData,
            $category->fresh()->toArray()
        );

        return redirect()->route('categories.index')->with('success', 'Category updated successfully!');
    }

    public function destroy(Category $category)
    {
        // Check if category has products
        if ($category->hasProducts()) {
            return back()->withErrors(['error' => 'Cannot delete category that contains products. Please move or delete the products first.']);
        }

        $categoryData = $category->toArray();
        $category->delete();

        // Log category deletion
        AuditLogService::log(
            'delete_category',
            'categories',
            "Deleted category '{$categoryData['name']}'",
            $categoryData,
            null
        );

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully!');
    }

    public function toggleStatus(Category $category)
    {
        $oldStatus = $category->is_active;
        $category->update(['is_active' => !$category->is_active]);
        
        $action = $category->is_active ? 'activated' : 'deactivated';
        
        // Log status change
        AuditLogService::log(
            'toggle_category_status',
            'categories',
            "Category '{$category->name}' {$action}",
            ['is_active' => $oldStatus],
            ['is_active' => $category->is_active]
        );

        return back()->with('success', "Category {$action} successfully!");
    }
}