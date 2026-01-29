<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use App\Models\Sale;
use App\Services\SimplePDFService;

class PHPMailerService
{
    private $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        $this->configureMailer();
    }

    private function configureMailer()
    {
        try {
            // Server settings
            $this->mail->isSMTP();
            $this->mail->Host = env('MAIL_HOST', 'smtp.gmail.com');
            $this->mail->SMTPAuth = true;
            $this->mail->Username = env('MAIL_USERNAME');
            $this->mail->Password = env('MAIL_PASSWORD');
            $this->mail->SMTPSecure = env('MAIL_ENCRYPTION', PHPMailer::ENCRYPTION_STARTTLS);
            $this->mail->Port = env('MAIL_PORT', 587);

            // Default sender
            $this->mail->setFrom(
                env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                env('MAIL_FROM_NAME', env('APP_NAME', 'Laravel'))
            );

            // Enable HTML
            $this->mail->isHTML(true);

        } catch (Exception $e) {
            throw new \Exception("Mailer configuration failed: {$this->mail->ErrorInfo}");
        }
    }

    public function sendReceiptEmail(Sale $sale, string $recipientEmail): bool
    {
        try {
            // Load sale relationships
            $sale->load(['saleItems.product', 'admin']);

            // Recipients
            $this->mail->addAddress($recipientEmail);

            // Content
            $this->mail->Subject = 'Receipt - ' . $sale->receipt_no . ' - Inventory & Sales';
            $this->mail->Body = $this->generateReceiptHTML($sale);
            $this->mail->AltBody = $this->generateReceiptText($sale);

            // Add PDF attachment
            $pdfService = new SimplePDFService();
            $tempFilePath = $pdfService->generateReceiptPDF($sale);
            $filename = $pdfService->getReceiptFilename($sale);
            
            $this->mail->addAttachment($tempFilePath, $filename);

            $result = $this->mail->send();
            
            // Clean up
            $pdfService->cleanupTempFile($tempFilePath);
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            
            return $result;

        } catch (Exception $e) {
            throw new \Exception("Email sending failed: {$this->mail->ErrorInfo}");
        }
    }

    private function generateReceiptHTML(Sale $sale): string
    {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Receipt - ' . $sale->receipt_no . '</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #f8f9fa;
                }
                .receipt-container {
                    background: white;
                    padding: 30px;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                .header {
                    text-align: center;
                    border-bottom: 2px solid #007bff;
                    padding-bottom: 20px;
                    margin-bottom: 30px;
                }
                .header h1 {
                    color: #007bff;
                    margin: 0;
                    font-size: 28px;
                }
                .receipt-info {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 20px;
                    flex-wrap: wrap;
                }
                .customer-info {
                    background: #f8f9fa;
                    padding: 15px;
                    border-radius: 5px;
                    margin-bottom: 20px;
                }
                .items-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                .items-table th,
                .items-table td {
                    padding: 12px;
                    text-align: left;
                    border-bottom: 1px solid #ddd;
                }
                .items-table th {
                    background-color: #007bff;
                    color: white;
                }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .totals {
                    border-top: 2px solid #007bff;
                    padding-top: 15px;
                }
                .grand-total {
                    font-size: 18px;
                    font-weight: bold;
                    color: #007bff;
                    border-top: 1px solid #ddd;
                    padding-top: 10px;
                    margin-top: 10px;
                }
                .footer {
                    text-align: center;
                    margin-top: 30px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                    color: #666;
                }
            </style>
        </head>
        <body>
            <div class="receipt-container">
                <div class="header">
                    <h1>🏪 Inventory & Sales</h1>
                    <p>Point of Sale System</p>
                    <p>Thank you for your purchase!</p>
                </div>
                
                <div class="receipt-info">
                    <div><strong>Receipt #:</strong> ' . $sale->receipt_no . '</div>
                    <div><strong>Date:</strong> ' . $sale->created_at->format('M d, Y H:i') . '</div>
                </div>';

        // Customer information
        if ($sale->customer_name || $sale->customer_phone || $sale->customer_email) {
            $html .= '<div class="customer-info">
                        <h3>📋 Customer Information</h3>';
            
            if ($sale->customer_name) {
                $html .= '<p><strong>Name:</strong> ' . $sale->customer_name . '</p>';
            }
            if ($sale->customer_phone) {
                $html .= '<p><strong>Phone:</strong> ' . $sale->customer_phone . '</p>';
            }
            if ($sale->customer_email) {
                $html .= '<p><strong>Email:</strong> ' . $sale->customer_email . '</p>';
            }
            
            $html .= '</div>';
        }

        $html .= '
                <div class="receipt-info">
                    <div><strong>Cashier:</strong> ' . $sale->admin->full_name . '</div>
                    <div><strong>Items:</strong> ' . $sale->total_items . '</div>
                </div>
                
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Price</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>';

        foreach ($sale->saleItems as $item) {
            $html .= '<tr>
                        <td>
                            <strong>' . $item->product->name . '</strong><br>
                            <small>' . $item->product->barcode_number . '</small>
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
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span><strong>Subtotal:</strong></span>
                        <span>$' . number_format($sale->subtotal, 2) . '</span>
                    </div>';

        if ($sale->tax_percent > 0) {
            $html .= '<div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span>Tax (' . number_format($sale->tax_percent, 2) . '%):</span>
                        <span>$' . number_format($sale->tax_amount, 2) . '</span>
                      </div>';
        }

        $html .= '
                    <div class="grand-total" style="display: flex; justify-content: space-between;">
                        <span><strong>Grand Total:</strong></span>
                        <span><strong>$' . number_format($sale->grand_total, 2) . '</strong></span>
                    </div>
                </div>
                
                <div class="footer">
                    <p><strong>Thank you for your business!</strong></p>
                    <p>' . now()->format('Y') . ' Inventory & Sales System</p>
                    <p style="font-size: 12px; color: #999;">
                        This is an electronic receipt. Please keep it for your records.
                    </p>
                </div>
            </div>
        </body>
        </html>';

        return $html;
    }

    private function generateReceiptText(Sale $sale): string
    {
        $text = "INVENTORY & SALES\n";
        $text .= "Point of Sale System\n";
        $text .= "========================\n\n";
        
        $text .= "Receipt #: " . $sale->receipt_no . "\n";
        $text .= "Date: " . $sale->created_at->format('M d, Y H:i') . "\n";
        
        if ($sale->customer_name || $sale->customer_phone || $sale->customer_email) {
            $text .= "\nCustomer Information:\n";
            if ($sale->customer_name) $text .= "Name: " . $sale->customer_name . "\n";
            if ($sale->customer_phone) $text .= "Phone: " . $sale->customer_phone . "\n";
            if ($sale->customer_email) $text .= "Email: " . $sale->customer_email . "\n";
        }
        
        $text .= "\nCashier: " . $sale->admin->full_name . "\n";
        $text .= "Items: " . $sale->total_items . "\n\n";
        
        $text .= "ITEMS:\n";
        $text .= "------------------------\n";
        
        foreach ($sale->saleItems as $item) {
            $text .= $item->product->name . "\n";
            $text .= "  Qty: " . $item->qty . " x $" . number_format($item->unit_price, 2);
            $text .= " = $" . number_format($item->line_total, 2) . "\n";
        }
        
        $text .= "\n------------------------\n";
        $text .= "Subtotal: $" . number_format($sale->subtotal, 2) . "\n";
        
        if ($sale->tax_percent > 0) {
            $text .= "Tax (" . number_format($sale->tax_percent, 2) . "%): $" . number_format($sale->tax_amount, 2) . "\n";
        }
        
        $text .= "GRAND TOTAL: $" . number_format($sale->grand_total, 2) . "\n\n";
        $text .= "Thank you for your business!\n";
        $text .= now()->format('Y') . " Inventory & Sales System";
        
        return $text;
    }
}