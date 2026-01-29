<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->search) {
            $query->search($request->search);
        }

        if ($request->category_id) {
            $query->byCategory($request->category_id);
        }

        if ($request->low_stock == '1') {
            $query->lowStock();
        }

        $products = $query->orderBy('name')->get();
        
        // Handle missing categories table
        $categories = collect();
        try {
            if (\Schema::hasTable('categories')) {
                $categories = Category::active()->ordered()->get();
            }
        } catch (\Exception $e) {
            $categories = collect();
        }

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = collect();
        try {
            if (\Schema::hasTable('categories')) {
                $categories = Category::active()->ordered()->get();
            }
        } catch (\Exception $e) {
            $categories = collect();
        }
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|min:3|max:120',
            'barcode_number' => 'required|string|unique:products,barcode_number',
            'price' => 'required|numeric|min:0',
            'stock_qty' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ];
        
        // Only require category if categories table exists
        if (\Schema::hasTable('categories')) {
            $rules['category_id'] = 'required|exists:categories,id';
        }
        
        $request->validate($rules);

        $data = $request->only(['category_id', 'name', 'barcode_number', 'price', 'stock_qty']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        // Log product creation
        AuditLogService::logProductCreate($product->toArray());

        return redirect()->route('products.index')->with('success', 'Product added successfully!');
    }

    public function edit(Product $product)
    {
        $categories = collect();
        try {
            if (\Schema::hasTable('categories')) {
                $categories = Category::active()->ordered()->get();
            }
        } catch (\Exception $e) {
            $categories = collect();
        }
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $rules = [
            'name' => 'required|string|min:3|max:120',
            'barcode_number' => 'required|string|unique:products,barcode_number,' . $product->id,
            'price' => 'required|numeric|min:0',
            'stock_qty' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ];
        
        // Only require category if categories table exists
        if (\Schema::hasTable('categories')) {
            $rules['category_id'] = 'required|exists:categories,id';
        }
        
        $request->validate($rules);

        $oldData = $product->toArray();
        $data = $request->only(['category_id', 'name', 'barcode_number', 'price', 'stock_qty']);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        // Log product update
        AuditLogService::logProductUpdate($oldData, $product->fresh()->toArray());

        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        // Check if product has sales history
        if ($product->saleItems()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete product with sales history']);
        }

        $productData = $product->toArray();

        // Delete image
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        // Log product deletion
        AuditLogService::logProductDelete($productData);

        return redirect()->route('products.index')->with('success', 'Product deleted successfully!');
    }

    public function refillStock()
    {
        // Get products ordered by stock level (low stock first), then by name
        $products = Product::with('category')
            ->orderByRaw('CASE WHEN stock_qty <= 5 THEN 0 ELSE 1 END')
            ->orderBy('stock_qty', 'asc')
            ->orderBy('name')
            ->get();
            
        $categories = collect();
        try {
            if (\Schema::hasTable('categories')) {
                $categories = Category::active()->ordered()->get();
            }
        } catch (\Exception $e) {
            $categories = collect();
        }
        return view('products.refill', compact('products', 'categories'));
    }

    public function refillSingle(Product $product)
    {
        // Load the product with category relationship
        $product->load('category');
        
        $categories = collect();
        try {
            if (\Schema::hasTable('categories')) {
                $categories = Category::active()->ordered()->get();
            }
        } catch (\Exception $e) {
            $categories = collect();
        }
        
        return view('products.refill-single', compact('product', 'categories'));
    }

    public function searchAjax(Request $request)
    {
        $query = Product::with('category');

        if ($request->search) {
            $query->search($request->search);
        }

        if ($request->category_id) {
            $query->byCategory($request->category_id);
        }

        if ($request->low_stock == '1') {
            $query->lowStock();
        }

        $products = $query->orderBy('name')->get();
        
        // Return the products grid HTML
        return view('products.partials.products-grid', compact('products'))->render();
    }

    public function updateStock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::findOrFail($request->product_id);
        $oldStock = $product->stock_qty;
        $addedQty = $request->quantity;
        
        $product->increment('stock_qty', $addedQty);
        $newStock = $product->fresh()->stock_qty;

        // Log stock refill
        AuditLogService::logStockRefill($product->name, $oldStock, $newStock, $addedQty);

        // Check if this came from single product refill page
        $referer = $request->headers->get('referer');
        if ($referer && str_contains($referer, '/refill')) {
            // If from single product page, redirect back to products index with success message
            return redirect()->route('products.index')->with('success', 
                "Stock updated successfully! {$product->name} now has {$newStock} units (+{$addedQty})");
        }

        // Default behavior for bulk refill page
        return back()->with('success', 'Stock updated successfully!');
    }
}