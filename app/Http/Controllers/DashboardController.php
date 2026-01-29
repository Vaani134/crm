<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Category;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Today's sales
        $todaySales = Sale::today()->selectRaw('COUNT(*) as count, COALESCE(SUM(grand_total), 0) as total')->first();
        
        // This week's sales
        $weekSales = Sale::thisWeek()->selectRaw('COUNT(*) as count, COALESCE(SUM(grand_total), 0) as total')->first();
        
        // This month's sales
        $monthSales = Sale::thisMonth()->selectRaw('COUNT(*) as count, COALESCE(SUM(grand_total), 0) as total')->first();
        
        // This year's sales
        $yearSales = Sale::whereYear('created_at', date('Y'))->selectRaw('COUNT(*) as count, COALESCE(SUM(grand_total), 0) as total')->first();
        
        // Product statistics
        $totalProducts = Product::count();
        $lowStock = Product::lowStock()->count();
        $outOfStock = Product::where('stock_qty', 0)->count();
        $inStock = Product::where('stock_qty', '>', 0)->count();
        
        // Get top selling products (this month)
        $topProducts = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->select(
                'products.name',
                'products.barcode_number',
                'products.stock_qty',
                DB::raw('SUM(sale_items.qty) as total_sold'),
                DB::raw('SUM(sale_items.line_total) as total_revenue')
            )
            ->whereMonth('sales.created_at', date('m'))
            ->whereYear('sales.created_at', date('Y'))
            ->groupBy('products.id', 'products.name', 'products.barcode_number', 'products.stock_qty')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();
        
        // Category statistics (with error handling)
        $totalCategories = 0;
        
        try {
            if (\Schema::hasTable('categories')) {
                $totalCategories = Category::active()->count();
            }
        } catch (\Exception $e) {
            // Categories table doesn't exist yet, use defaults
            $totalCategories = 0;
        }

        return view('dashboard', compact(
            'todaySales', 'weekSales', 'monthSales', 'yearSales', 
            'totalProducts', 'lowStock', 'outOfStock', 'inStock',
            'topProducts', 'totalCategories'
        ));
    }

}