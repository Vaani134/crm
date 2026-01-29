<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Category;
use App\Models\SaleItem;
use App\Services\AuditLogService;
use App\Mail\ReceiptMail;
use App\Services\PHPMailerService;
use App\Services\PDFService;
use App\Services\SimplePDFService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class SalesController extends Controller
{
    public function register()
    {
        $products = Product::with('category')->where('stock_qty', '>', 0)->orderBy('name')->get();
        
        $categories = collect();
        try {
            if (\Schema::hasTable('categories')) {
                $categories = Category::active()->ordered()->get();
            }
        } catch (\Exception $e) {
            $categories = collect();
        }
        
        return view('sales.register', compact('products', 'categories'));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'cart_data' => 'required|json',
            'tax_percent' => 'required|numeric|min:0|max:100',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:255'
        ]);

        $cart = json_decode($request->cart_data, true);
        $taxPercent = $request->tax_percent;
        $customerName = trim($request->customer_name) ?: null;
        $customerPhone = trim($request->customer_phone) ?: null;
        $customerEmail = trim($request->customer_email) ?: null;

        if (empty($cart)) {
            return back()->withErrors(['error' => 'Cart is empty']);
        }

        DB::beginTransaction();
        try {
            // Calculate totals
            $subtotal = 0;
            $totalItems = 0;

            foreach ($cart as $item) {
                $subtotal += $item['line_total'];
                $totalItems += $item['qty'];
            }

            $taxAmount = ($subtotal * $taxPercent) / 100;
            $grandTotal = $subtotal + $taxAmount;
            $receiptNo = Sale::generateReceiptNo();

            // Create sale
            $sale = Sale::create([
                'receipt_no' => $receiptNo,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'customer_email' => $customerEmail,
                'total_items' => $totalItems,
                'subtotal' => $subtotal,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'created_by' => Auth::guard('admin')->id()
            ]);

            // Create sale items and update stock
            foreach ($cart as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['line_total']
                ]);

                // Update stock
                Product::where('id', $item['product_id'])->decrement('stock_qty', $item['qty']);
            }

            DB::commit();
            
            // Log sale creation
            AuditLogService::logSaleCreate($sale->toArray());
            
            return redirect()->route('sales.receipt', $sale->id);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Transaction failed: ' . $e->getMessage()]);
        }
    }

    public function receipt(Sale $sale)
    {
        $sale->load(['saleItems.product', 'admin']);
        return view('sales.receipt', compact('sale'));
    }

    public function history(Request $request)
    {
        $query = Sale::with(['admin']);

        // Apply date filters based on request
        switch ($request->filter) {
            case 'today':
                $query->today();
                break;
            case 'week':
                $query->thisWeek();
                break;
            case 'month':
                $query->thisMonth();
                break;
        }

        // Apply employee filter if provided
        if ($request->filled('employee_id')) {
            $query->where('created_by', $request->employee_id);
        }

        $sales = $query->orderBy('created_at', 'desc')->get();

        // Get list of employees for the filter dropdown
        $employees = \App\Models\Admin::orderBy('full_name')->get();

        // If this is an AJAX request, return JSON data
        if ($request->ajax()) {
            try {
                return response()->json([
                    'success' => true,
                    'sales' => $sales->map(function($sale) {
                        return [
                            'id' => $sale->id,
                            'receipt_no' => $sale->receipt_no,
                            'customer_name' => $sale->customer_name,
                            'customer_phone' => $sale->customer_phone,
                            'customer_email' => $sale->customer_email,
                            'created_at' => $sale->created_at,
                            'formatted_date' => $sale->created_at->format('M d, Y'),
                            'formatted_time' => $sale->created_at->format('H:i'),
                            'total_items' => $sale->total_items,
                            'grand_total' => (float) $sale->grand_total,
                            'tax_percent' => (float) $sale->tax_percent,
                            'admin' => [
                                'full_name' => $sale->admin->full_name,
                                'role' => ucfirst($sale->admin->role)
                            ],
                            'receipt_url' => route('sales.receipt', $sale)
                        ];
                    }),
                    'summary' => [
                        'total_transactions' => $sales->count(),
                        'total_items' => $sales->sum('total_items'),
                        'total_revenue' => (float) $sales->sum('grand_total'),
                        'filter' => $request->filter,
                        'employee_id' => $request->employee_id,
                        'period_text' => $this->getPeriodText($request->filter)
                    ]
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to load sales data: ' . $e->getMessage()
                ], 500);
            }
        }

        return view('sales.history', compact('sales', 'employees'));
    }

    private function getPeriodText($filter)
    {
        switch ($filter) {
            case 'today':
                return [
                    'title' => 'Today',
                    'range' => now()->format('M d, Y')
                ];
            case 'week':
                return [
                    'title' => 'This Week',
                    'range' => now()->startOfWeek()->format('M d') . ' - ' . now()->endOfWeek()->format('M d, Y')
                ];
            case 'month':
                return [
                    'title' => 'This Month',
                    'range' => now()->format('F Y')
                ];
            default:
                return [
                    'title' => '',
                    'range' => ''
                ];
        }
    }

    public function getProductData(Request $request)
    {
        $search = $request->get('search', '');
        $categoryId = $request->get('category_id', '');
        
        $products = Product::with('category')
            ->where('stock_qty', '>', 0)
            ->when($search, function($query, $search) {
                return $query->search($search);
            })
            ->when($categoryId, function($query, $categoryId) {
                return $query->byCategory($categoryId);
            })
            ->orderBy('name')
            ->get();

        return response()->json($products);
    }

    public function sendReceiptEmail(Request $request, Sale $sale)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        try {
            // Load sale relationships
            $sale->load(['saleItems.product', 'admin']);
            
            // Choose email method based on configuration
            $usePhpMailer = env('USE_PHPMAILER', false);
            
            if ($usePhpMailer) {
                // Use PHPMailer
                $phpMailerService = new PHPMailerService();
                $result = $phpMailerService->sendReceiptEmail($sale, $request->email);
                
                if (!$result) {
                    throw new \Exception('PHPMailer failed to send email');
                }
            } else {
                // Use Laravel Mail
                Mail::to($request->email)->send(new ReceiptMail($sale));
            }
            
            // Log the email sending
            AuditLogService::logCustom([
                'action' => 'receipt_email_sent',
                'description' => "Receipt {$sale->receipt_no} emailed to {$request->email} using " . ($usePhpMailer ? 'PHPMailer' : 'Laravel Mail'),
                'sale_id' => $sale->id,
                'email' => $request->email,
                'method' => $usePhpMailer ? 'phpmailer' : 'laravel_mail'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Receipt sent successfully to ' . $request->email
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sendReceiptEmailPHPMailer(Request $request, Sale $sale)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        try {
            // Use PHPMailer service
            $phpMailerService = new PHPMailerService();
            $result = $phpMailerService->sendReceiptEmail($sale, $request->email);
            
            if (!$result) {
                throw new \Exception('Failed to send email via PHPMailer');
            }
            
            // Log the email sending
            AuditLogService::logCustom([
                'action' => 'receipt_email_sent',
                'description' => "Receipt {$sale->receipt_no} emailed to {$request->email} via PHPMailer",
                'sale_id' => $sale->id,
                'email' => $request->email,
                'method' => 'phpmailer'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Receipt sent successfully to ' . $request->email . ' via PHPMailer'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'PHPMailer error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadReceiptPDF(Sale $sale)
    {
        try {
            // Try to use PDFService first, fallback to SimplePDFService
            try {
                $pdfService = new PDFService();
                $pdfContent = $pdfService->generateReceiptPDFContent($sale);
            } catch (\Exception $e) {
                // Fallback to SimplePDFService if dompdf is not available
                $pdfService = new SimplePDFService();
                $pdfContent = $pdfService->generateReceiptPDFContent($sale);
            }
            
            $filename = $pdfService->getReceiptFilename($sale);
            
            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
                
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to generate PDF: ' . $e->getMessage()]);
        }
    }

    // Test methods for PDF generation
    public function testPDFGeneration(Request $request)
    {
        try {
            // Get the most recent sale for testing
            $sale = Sale::with(['saleItems.product', 'admin'])->latest()->first();
            
            if (!$sale) {
                return response()->json([
                    'success' => false,
                    'error' => 'No sales found. Please create a sale first.'
                ]);
            }

            // Test PDF generation
            $pdfService = new SimplePDFService();
            $tempFilePath = $pdfService->generateReceiptPDF($sale);
            
            // Get file information
            $fileInfo = [
                'filename' => basename($tempFilePath),
                'size' => filesize($tempFilePath),
                'extension' => pathinfo($tempFilePath, PATHINFO_EXTENSION),
                'mime_type' => mime_content_type($tempFilePath)
            ];
            
            // Clean up temp file
            $pdfService->cleanupTempFile($tempFilePath);
            
            return response()->json([
                'success' => true,
                'message' => 'PDF generated successfully',
                'filename' => $fileInfo['filename'],
                'size' => $fileInfo['size'],
                'extension' => $fileInfo['extension'],
                'mime_type' => $fileInfo['mime_type']
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function testEmailPDF(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        try {
            // Get the most recent sale for testing
            $sale = Sale::with(['saleItems.product', 'admin'])->latest()->first();
            
            if (!$sale) {
                return response()->json([
                    'success' => false,
                    'error' => 'No sales found. Please create a sale first.'
                ]);
            }

            // Test email with PDF attachment using PHPMailer
            $phpMailerService = new PHPMailerService();
            $result = $phpMailerService->sendReceiptEmail($sale, $request->email);
            
            if (!$result) {
                throw new \Exception('Failed to send email');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully',
                'attachment_name' => 'Receipt_' . $sale->receipt_no . '.pdf',
                'attachment_size' => 'Generated dynamically'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getRecentSales(Request $request)
    {
        try {
            $sales = Sale::with(['admin'])
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($sale) {
                    return [
                        'id' => $sale->id,
                        'receipt_no' => $sale->receipt_no,
                        'grand_total' => number_format($sale->grand_total, 2),
                        'created_at' => $sale->created_at->format('M d, Y H:i'),
                        'customer_name' => $sale->customer_name
                    ];
                });

            return response()->json([
                'success' => true,
                'sales' => $sales
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}