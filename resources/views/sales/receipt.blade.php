@extends('layouts.app')

@section('title', 'Receipt - Inventory & Sales')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body" id="receipt-content">
                <!-- Receipt Header -->
                <div class="text-center mb-4">
                    <h3><i class="fas fa-store"></i> Inventory & Sales</h3>
                    <p class="mb-1">Point of Sale System</p>
                    <hr>
                </div>
                
                <!-- Receipt Info -->
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Receipt #:</strong> {{ $sale->receipt_no }}
                    </div>
                    <div class="col-6 text-end">
                        <strong>Date:</strong> {{ $sale->created_at->format('M d, Y H:i') }}
                    </div>
                </div>
                
                @if($sale->customer_name || $sale->customer_phone || $sale->customer_email)
                <div class="row mb-3">
                    <div class="col-12">
                        <strong>Customer Information:</strong><br>
                        @if($sale->customer_name)
                            <span><i class="fas fa-user"></i> {{ $sale->customer_name }}</span><br>
                        @endif
                        @if($sale->customer_phone)
                            <span><i class="fas fa-phone"></i> {{ $sale->customer_phone }}</span><br>
                        @endif
                        @if($sale->customer_email)
                            <span><i class="fas fa-envelope"></i> {{ $sale->customer_email }}</span>
                        @endif
                    </div>
                </div>
                @endif
                
                <div class="row mb-4">
                    <div class="col-6">
                        <strong>Cashier:</strong> {{ $sale->admin->full_name }}
                    </div>
                    <div class="col-6 text-end">
                        <strong>Items:</strong> {{ $sale->total_items }}
                    </div>
                </div>
                
                <hr>
                
                <!-- Items -->
                <div class="mb-4">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->saleItems as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->product->name }}</strong><br>
                                    <small class="text-muted">{{ $item->product->barcode_number }}</small>
                                </td>
                                <td class="text-center">{{ $item->qty }}</td>
                                <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">${{ number_format($item->line_total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <hr>
                
                <!-- Totals -->
                <div class="row mb-2">
                    <div class="col-6"><strong>Subtotal:</strong></div>
                    <div class="col-6 text-end">${{ number_format($sale->subtotal, 2) }}</div>
                </div>
                
                @if($sale->tax_percent > 0)
                <div class="row mb-2">
                    <div class="col-6">Tax ({{ number_format($sale->tax_percent, 2) }}%):</div>
                    <div class="col-6 text-end">${{ number_format($sale->tax_amount, 2) }}</div>
                </div>
                @endif
                
                <div class="row mb-4">
                    <div class="col-6"><h5><strong>Grand Total:</strong></h5></div>
                    <div class="col-6 text-end"><h5><strong>${{ number_format($sale->grand_total, 2) }}</strong></h5></div>
                </div>
                
                <hr>
                
                <!-- Footer -->
                <div class="text-center">
                    <p class="mb-1">Thank you for your business!</p>
                    <small class="text-muted">{{ now()->format('Y') }} Inventory & Sales System</small>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="card-footer text-center">
                <button type="button" class="btn btn-primary me-2" onclick="printReceipt()">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
                <a href="{{ route('sales.download-pdf', $sale->id) }}" class="btn btn-success me-2">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </a>
                @if($sale->customer_email)
                <button type="button" class="btn btn-info me-2" onclick="sendReceiptEmail('{{ $sale->customer_email }}')">
                    <i class="fas fa-envelope"></i> Email to Customer
                </button>
                @else
                <button type="button" class="btn btn-outline-info me-2" onclick="showEmailModal()">
                    <i class="fas fa-envelope"></i> Send Email
                </button>
                @endif
                <a href="{{ route('sales.register') }}" class="btn btn-secondary me-2">
                    <i class="fas fa-plus"></i> New Sale
                </a>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Email Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emailModalLabel">
                    <i class="fas fa-envelope"></i> Send Receipt via Email
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="emailForm">
                    @csrf
                    <div class="mb-3">
                        <label for="emailAddress" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="emailAddress" 
                               placeholder="Enter customer email address" required>
                        <div class="form-text">Receipt will be sent to this email address</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-info" onclick="sendEmailFromModal()">
                    <i class="fas fa-paper-plane"></i> Send Email
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function printReceipt() {
    const receiptContent = document.getElementById('receipt-content').innerHTML;
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Receipt - {{ $sale->receipt_no }}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .text-center { text-align: center; }
                .text-end { text-align: right; }
                .mb-1 { margin-bottom: 0.25rem; }
                .mb-2 { margin-bottom: 0.5rem; }
                .mb-3 { margin-bottom: 1rem; }
                .mb-4 { margin-bottom: 1.5rem; }
                table { width: 100%; border-collapse: collapse; }
                th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
                .text-muted { color: #6c757d; }
                hr { border: 1px solid #ddd; }
                h3, h5 { margin: 0; }
                .row { display: flex; justify-content: space-between; }
                @media print {
                    body { margin: 0; }
                }
            </style>
        </head>
        <body>
            ${receiptContent}
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.print();
}

function showEmailModal() {
    const modal = new bootstrap.Modal(document.getElementById('emailModal'));
    modal.show();
}

function sendReceiptEmail(email) {
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    button.disabled = true;
    
    // Send email
    fetch('{{ route("sales.send-email", $sale->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            email: email
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showAlert('success', data.message);
        } else {
            // Show error message
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Failed to send email. Please try again.');
    })
    .finally(() => {
        // Restore button state
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function sendEmailFromModal() {
    const email = document.getElementById('emailAddress').value;
    if (!email) {
        showAlert('error', 'Please enter an email address');
        return;
    }
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('emailModal'));
    modal.hide();
    
    // Send email
    sendReceiptEmail(email);
}

function showAlert(type, message) {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at top of page
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Pre-fill email if customer email exists
@if($sale->customer_email)
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('emailAddress').value = '{{ $sale->customer_email }}';
});
@endif
</script>
@endsection