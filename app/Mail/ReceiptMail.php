<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\Sale;
use App\Services\SimplePDFService;

class ReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public $sale;
    private $pdfService;
    private $tempFilePath;

    /**
     * Create a new message instance.
     */
    public function __construct(Sale $sale)
    {
        $this->sale = $sale;
        $this->pdfService = new SimplePDFService();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Receipt - ' . $this->sale->receipt_no . ' - Inventory & Sales',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.receipt',
            with: [
                'sale' => $this->sale,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // Generate PDF file
        $this->tempFilePath = $this->pdfService->generateReceiptPDF($this->sale);
        $filename = $this->pdfService->getReceiptFilename($this->sale);
        
        return [
            Attachment::fromPath($this->tempFilePath)
                ->as($filename)
                ->withMime('application/pdf')
        ];
    }
    
    /**
     * Clean up temporary files after sending
     */
    public function __destruct()
    {
        if ($this->tempFilePath) {
            $this->pdfService->cleanupTempFile($this->tempFilePath);
        }
    }
}