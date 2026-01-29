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

class AnalysisController extends Controller
{
    public function index(Request $request)
    {
        // Get selected years from request, default to current year
        $categoryYear = $request->get('category_year', date('Y'));
        $monthlyYear = $request->get('monthly_year', date('Y'));
        
        // Get available years for dropdown
        $availableYears = $this->getAvailableYears();

        // Chart data - independent year selection
        $categorySalesData = $this->getCategorySalesData($categoryYear);
        $timeSalesData = $this->getTimeSalesData($monthlyYear);
        $yearlySalesData = $this->getYearlySalesData();

        // Return JSON for AJAX requests
        if ($request->ajax()) {
            $response = [];
            
            // Check which chart data is requested
            if ($request->has('category_year')) {
                $response['categorySalesData'] = $categorySalesData;
                $response['categoryYear'] = $categoryYear;
            }
            
            if ($request->has('monthly_year')) {
                $response['timeSalesData'] = $timeSalesData;
                $response['monthlyYear'] = $monthlyYear;
            }
            
            if ($request->has('yearly_data')) {
                $response['yearlySalesData'] = $yearlySalesData;
            }
            
            return response()->json($response);
        }

        return view('analysis.index', compact(
            'categorySalesData', 'timeSalesData', 'yearlySalesData',
            'categoryYear', 'monthlyYear', 'availableYears'
        ));
    }

    private function getAvailableYears()
    {
        try {
            $years = Sale::selectRaw('YEAR(created_at) as year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();
            
            // If no sales data, include current year
            if (empty($years)) {
                $years = [date('Y')];
            }
            
            return $years;
        } catch (\Exception $e) {
            return [date('Y')];
        }
    }

    private function getCategorySalesData($year = null)
    {
        try {
            if (!\Schema::hasTable('categories')) {
                return ['labels' => [], 'data' => [], 'colors' => []];
            }

            $year = $year ?: date('Y');

            // Get category-wise sales for selected year
            $categorySales = DB::table('sale_items')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->select(
                    'categories.name',
                    'categories.color',
                    DB::raw('SUM(sale_items.line_total) as total_sales')
                )
                ->whereYear('sales.created_at', $year)
                ->groupBy('categories.id', 'categories.name', 'categories.color')
                ->orderBy('total_sales', 'desc')
                ->get();

            return [
                'labels' => $categorySales->pluck('name')->toArray(),
                'data' => $categorySales->pluck('total_sales')->map(fn($val) => (float)$val)->toArray(),
                'colors' => $categorySales->pluck('color')->toArray()
            ];

        } catch (\Exception $e) {
            return ['labels' => [], 'data' => [], 'colors' => []];
        }
    }

    private function getTimeSalesData($year = null)
    {
        $year = $year ?: date('Y');
        
        // Monthly sales for selected year
        $monthlySales = Sale::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(grand_total) as total')
            )
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->get();

        // Create array for all 12 months
        $monthlyData = array_fill(0, 12, 0);
        foreach ($monthlySales as $sale) {
            $monthlyData[$sale->month - 1] = (float)$sale->total;
        }

        return [
            'monthly' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'data' => $monthlyData
            ]
        ];
    }

    private function getYearlySalesData()
    {
        try {
            $currentYear = date('Y');
            $startYear = $currentYear - 9; // Last 10 years including current year
            
            // Get yearly sales data
            $yearlySales = Sale::select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('SUM(grand_total) as total'),
                    DB::raw('COUNT(*) as transactions')
                )
                ->whereYear('created_at', '>=', $startYear)
                ->whereYear('created_at', '<=', $currentYear)
                ->groupBy(DB::raw('YEAR(created_at)'))
                ->orderBy('year')
                ->get();

            // Create array for all 10 years (fill missing years with 0)
            $years = [];
            $salesData = [];
            $transactionData = [];
            
            for ($year = $startYear; $year <= $currentYear; $year++) {
                $years[] = $year;
                $yearData = $yearlySales->firstWhere('year', $year);
                $salesData[] = $yearData ? (float)$yearData->total : 0;
                $transactionData[] = $yearData ? (int)$yearData->transactions : 0;
            }

            return [
                'labels' => $years,
                'salesData' => $salesData,
                'transactionData' => $transactionData
            ];

        } catch (\Exception $e) {
            // Return empty data if there's an error
            $currentYear = date('Y');
            $startYear = $currentYear - 9;
            $years = range($startYear, $currentYear);
            
            return [
                'labels' => $years,
                'salesData' => array_fill(0, 10, 0),
                'transactionData' => array_fill(0, 10, 0)
            ];
        }
    }
}