<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;

class SimplePDFService
{
    public function generateReceiptPDF(Sale $sale): string
    {
        // Load sale relationships
        $sale->load(['saleItems.product', 'admin']);
        
        // Generate PDF using Laravel DomPDF with custom HTML
        $html = $this->generatePDFHTML($sale);
        $pdf = Pdf::loadHTML($html);
        
        // Create a temporary file with PDF extension
        $filename = 'receipt_' . $sale->receipt_no . '_' . time() . '.pdf';
        $tempPath = storage_path('app/temp/' . $filename);
        
        // Ensure temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        
        // Save as actual PDF file
        $pdf->save($tempPath);
        
        return $tempPath;
    }
    
    public function generateReceiptPDFContent(Sale $sale): string
    {
        // Load sale relationships
        $sale->load(['saleItems.product', 'admin']);
        
        // Generate PDF content using Laravel DomPDF
        $html = $this->generatePDFHTML($sale);
        $pdf = Pdf::loadHTML($html);
        
        return $pdf->output();
    }
    
    private function generatePDFHTML(Sale $sale): string
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - ' . $sale->receipt_no . '</title>
    <style>
        @page { margin: 20mm; }
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; color: #000; margin: 0; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { font-size: 24px; margin: 0; }
        .receipt-info { margin-bottom: 15px; }
        .customer-info { background: #f8f8f8; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .items-table th, .items-table td { padding: 8px 5px; text-align: left; border-bottom: 1px solid #ddd; }
        .items-table th { background-color: #f0f0f0; font-weight: bold; border-bottom: 2px solid #000; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals { border-top: 2px solid #000; padding-top: 10px; margin-top: 10px; }
        .grand-total { font-size: 16px; font-weight: bold; border-top: 1px solid #000; padding-top: 8px; margin-top: 8px; }
        .footer { text-align: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>INVENTORY & SALES</h1>
        <p>Point of Sale System</p>
        <p><strong>RECEIPT</strong></p>
    </div>
    
    <div class="receipt-info">
        <table style="width: 100%;">
            <tr>
                <td><strong>Receipt #:</strong> ' . $sale->receipt_no . '</td>
                <td style="text-align: right;"><strong>Date:</strong> ' . $sale->created_at->format('M d, Y H:i') . '</td>
            </tr>
        </table>
    </div>';

        // Customer information
        if ($sale->customer_name || $sale->customer_phone || $sale->customer_email) {
            $html .= '<div class="customer-info">
                        <h3 style="margin: 0 0 8px 0;">CUSTOMER INFORMATION</h3>';
            
            if ($sale->customer_name) {
                $html .= '<p style="margin: 3px 0;"><strong>Name:</strong> ' . $sale->customer_name . '</p>';
            }
            if ($sale->customer_phone) {
                $html .= '<p style="margin: 3px 0;"><strong>Phone:</strong> ' . $sale->customer_phone . '</p>';
            }
            if ($sale->customer_email) {
                $html .= '<p style="margin: 3px 0;"><strong>Email:</strong> ' . $sale->customer_email . '</p>';
            }
            
            $html .= '</div>';
        }

        $html .= '
    <div class="receipt-info">
        <table style="width: 100%;">
            <tr>
                <td><strong>Cashier:</strong> ' . $sale->admin->full_name . '</td>
                <td style="text-align: right;"><strong>Total Items:</strong> ' . $sale->total_items . '</td>
            </tr>
        </table>
    </div>
    
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 45%;">Item</th>
                <th style="width: 15%;" class="text-center">Qty</th>
                <th style="width: 20%;" class="text-right">Price</th>
                <th style="width: 20%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($sale->saleItems as $item) {
            $html .= '<tr>
                        <td>
                            <strong>' . $item->product->name . '</strong><br>
                            <small>SKU: ' . $item->product->barcode_number . '</small>
                        </td>
                        <td class="text-center">' . $item->qty . '</td>
                        <td class="text-right">$' . number_format($item->unit_price, 2) . '</td>
                        <td class="text-right">$' . number_format($item->line_total, 2) . '</td>
                      </tr>';
        }

        $html .= '
        </tbody>
    </table>
    
    <div class="totals">
        <table style="width: 100%;">
            <tr>
                <td style="width: 70%;"><strong>Subtotal:</strong></td>
                <td style="width: 30%;" class="text-right">$' . number_format($sale->subtotal, 2) . '</td>
            </tr>';

        if ($sale->tax_percent > 0) {
            $html .= '<tr>
                        <td>Tax (' . number_format($sale->tax_percent, 2) . '%):</td>
                        <td class="text-right">$' . number_format($sale->tax_amount, 2) . '</td>
                      </tr>';
        }

        $html .= '
            <tr class="grand-total">
                <td><strong>GRAND TOTAL:</strong></td>
                <td class="text-right"><strong>$' . number_format($sale->grand_total, 2) . '</strong></td>
            </tr>
        </table>
    </div>
    
    <div class="footer">
        <p><strong>Thank you for your business!</strong></p>
        <p>' . now()->format('Y') . ' Inventory & Sales System</p>
        <p>This is a computer-generated receipt.</p>
    </div>
</body>
</html>';

        return $html;
    }
    
    public function cleanupTempFile(string $filePath): void
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    public function getReceiptFilename(Sale $sale): string
    {
        return 'Receipt_' . $sale->receipt_no . '.pdf';
    }
}