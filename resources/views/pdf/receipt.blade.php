<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - {{ $sale->receipt_no }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            margin: 0;
            color: #000;
        }
        .header p {
            margin: 5px 0;
            font-size: 14px;
        }
        .receipt-info {
            margin-bottom: 15px;
        }
        .receipt-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .receipt-info td {
            padding: 3px 0;
            vertical-align: top;
        }
        .customer-info {
            background: #f8f8f8;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
        }
        .customer-info h3 {
            margin: 0 0 8px 0;
            font-size: 14px;
            color: #000;
        }
        .customer-info p {
            margin: 3px 0;
            font-size: 11px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-table th,
        .items-table td {
            padding: 8px 5px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }
        .items-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            border-bottom: 2px solid #000;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals {
            border-top: 2px solid #000;
            padding-top: 10px;
            margin-top: 10px;
        }
        .totals table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals td {
            padding: 3px 0;
            font-size: 12px;
        }
        .grand-total {
            font-size: 16px;
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 8px;
            margin-top: 8px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>INVENTORY & SALES</h1>
        <p>Point of Sale System</p>
        <p><strong>RECEIPT</strong></p>
    </div>
    
    <!-- Receipt Info -->
    <div class="receipt-info">
        <table>
            <tr>
                <td style="width: 50%;"><strong>Receipt #:</strong> {{ $sale->receipt_no }}</td>
                <td style="width: 50%; text-align: right;"><strong>Date:</strong> {{ $sale->created_at->format('M d, Y H:i') }}</td>
            </tr>
        </table>
    </div>
    
    <!-- Customer Information -->
    @if($sale->customer_name || $sale->customer_phone || $sale->customer_email)
    <div class="customer-info">
        <h3>CUSTOMER INFORMATION</h3>
        @if($sale->customer_name)
            <p><strong>Name:</strong> {{ $sale->customer_name }}</p>
        @endif
        @if($sale->customer_phone)
            <p><strong>Phone:</strong> {{ $sale->customer_phone }}</p>
        @endif
        @if($sale->customer_email)
            <p><strong>Email:</strong> {{ $sale->customer_email }}</p>
        @endif
    </div>
    @endif
    
    <div class="receipt-info">
        <table>
            <tr>
                <td style="width: 50%;"><strong>Cashier:</strong> {{ $sale->admin->full_name }}</td>
                <td style="width: 50%; text-align: right;"><strong>Total Items:</strong> {{ $sale->total_items }}</td>
            </tr>
        </table>
    </div>
    
    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 45%;">Item</th>
                <th style="width: 15%;" class="text-center">Qty</th>
                <th style="width: 20%;" class="text-right">Price</th>
                <th style="width: 20%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->saleItems as $item)
            <tr>
                <td>
                    <strong>{{ $item->product->name }}</strong><br>
                    <small style="color: #666;">SKU: {{ $item->product->barcode_number }}</small>
                </td>
                <td class="text-center">{{ $item->qty }}</td>
                <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">${{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Totals -->
    <div class="totals">
        <table>
            <tr>
                <td style="width: 70%;"><strong>Subtotal:</strong></td>
                <td style="width: 30%;" class="text-right">${{ number_format($sale->subtotal, 2) }}</td>
            </tr>
            
            @if($sale->tax_percent > 0)
            <tr>
                <td>Tax ({{ number_format($sale->tax_percent, 2) }}%):</td>
                <td class="text-right">${{ number_format($sale->tax_amount, 2) }}</td>
            </tr>
            @endif
            
            <tr class="grand-total">
                <td><strong>GRAND TOTAL:</strong></td>
                <td class="text-right"><strong>${{ number_format($sale->grand_total, 2) }}</strong></td>
            </tr>
        </table>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <p><strong>Thank you for your business!</strong></p>
        <p>{{ now()->format('Y') }} Inventory & Sales System</p>
        <p>This is a computer-generated receipt.</p>
    </div>
</body>
</html>