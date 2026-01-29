<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;

class PDFService
{

    public function generateReceiptPDF(Sale $sale): string
    {
        // Load sale relationships
        $sale->load(['saleItems.product', 'admin']);
        
        // Generate PDF using Laravel DomPDF
        $pdf = Pdf::loadView('pdf.receipt', compact('sale'));
        
        // Create a temporary file
        $filename = 'receipt_' . $sale->receipt_no . '_' . time() . '.pdf';
        $tempPath = storage_path('app/temp/' . $filename);
        
        // Ensure temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        
        // Save PDF to temporary file
        $pdf->save($tempPath);
        
        return $tempPath;
    }
    
    public function generateReceiptPDFContent(Sale $sale): string
    {
        // Load sale relationships
        $sale->load(['saleItems.product', 'admin']);
        
        // Generate PDF using Laravel DomPDF and return content
        $pdf = Pdf::loadView('pdf.receipt', compact('sale'));
        
        return $pdf->output();
    }
    
    public function generateReceiptHTML(Sale $sale): string
    {
        // Load sale relationships
        $sale->load(['saleItems.product', 'admin']);
        
        // Generate HTML content
        return View::make('pdf.receipt', compact('sale'))->render();
    }
    
    public function cleanupTempFile(string $filePath): void
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    public function generatePdfResponse(string $html, string $filename, string $orientation = 'portrait')
    {
        try {
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', $orientation);
            
            return $pdf->download($filename);
        } catch (\Exception $e) {
            throw new \Exception('Failed to generate PDF: ' . $e->getMessage());
        }
    }
}