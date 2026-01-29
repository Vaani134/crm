<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt - {{ $sale->receipt_no }}</title>
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
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .receipt-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .receipt-info div {
            margin-bottom: 10px;
        }
        .customer-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .customer-info h3 {
            margin: 0 0 10px 0;
            color: #007bff;
            font-size: 16px;
        }
        .customer-info p {
            margin: 5px 0;
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
            font-weight: bold;
        }
        .items-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals {
            border-top: 2px solid #007bff;
            padding-top: 15px;
        }
        .totals .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
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
        .icon {
            color: #007bff;
            margin-right: 5px;
        }
        @media (max-width: 600px) {
            .receipt-info {
                flex-direction: column;
            }
            .items-table {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="header">
            <h1>🏪 Inventory & Sales</h1>
            <p>Point of Sale System</p>
            <p>Thank you for your purchase!</p>
        </div>
        
        <!-- Receipt Info -->
        <div class="receipt-info">
            <div>
                <strong>Receipt #:</strong> {{ $sale->receipt_no }}
            </div>
            <div>
                <strong>Date:</strong> {{ $sale->created_at->format('M d, Y H:i') }}
            </div>
        </div>
        
        <!-- Customer Information -->
        @if($sale->customer_name || $sale->customer_phone || $sale->customer_email)
        <div class="customer-info">
            <h3>📋 Customer Information</h3>
            @if($sale->customer_name)
                <p><span class="icon">👤</span><strong>Name:</strong> {{ $sale->customer_name }}</p>
            @endif
            @if($sale->customer_phone)
                <p><span class="icon">📞</span><strong>Phone:</strong> {{ $sale->customer_phone }}</p>
            @endif
            @if($sale->customer_email)
                <p><span class="icon">📧</span><strong>Email:</strong> {{ $sale->customer_email }}</p>
            @endif
        </div>
        @endif
        
        <div class="receipt-info">
            <div>
                <strong>Cashier:</strong> {{ $sale->admin->full_name }}
            </div>
            <div>
                <strong>Items:</strong> {{ $sale->total_items }}
            </div>
        </div>
        
        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->saleItems as $item)
                <tr>
                    <td>
                        <strong>{{ $item->product->name }}</strong><br>
                        <small style="color: #666;">{{ $item->product->barcode_number }}</small>
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
            <div class="row">
                <span><strong>Subtotal:</strong></span>
                <span>${{ number_format($sale->subtotal, 2) }}</span>
            </div>
            
            @if($sale->tax_percent > 0)
            <div class="row">
                <span>Tax ({{ number_format($sale->tax_percent, 2) }}%):</span>
                <span>${{ number_format($sale->tax_amount, 2) }}</span>
            </div>
            @endif
            
            <div class="row grand-total">
                <span><strong>Grand Total:</strong></span>
                <span><strong>${{ number_format($sale->grand_total, 2) }}</strong></span>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p><strong>Thank you for your business!</strong></p>
            <p>{{ now()->format('Y') }} Inventory & Sales System</p>
            <p style="font-size: 12px; color: #999;">
                This is an electronic receipt. Please keep it for your records.
            </p>
        </div>
    </div>
</body>
</html>