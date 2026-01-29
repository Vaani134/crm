@if($sales->count() > 0)
<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-dark">
            <tr>
                <th>Receipt #</th>
                <th>Customer</th>
                <th>Contact</th>
                <th>Date & Time</th>
                <th>Items</th>
                <th>Total</th>
                <th>Cashier</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
            <tr>
                <td>
                    <strong>{{ $sale['receipt_no'] ?? $sale->receipt_no }}</strong>
                </td>
                <td>
                    @if(isset($sale['customer_name']) ? $sale['customer_name'] : $sale->customer_name)
                        {{ isset($sale['customer_name']) ? $sale['customer_name'] : $sale->customer_name }}
                    @else
                        <span class="text-muted">Walk-in</span>
                    @endif
                </td>
                <td>
                    @php
                        $phone = isset($sale['customer_phone']) ? $sale['customer_phone'] : $sale->customer_phone;
                        $email = isset($sale['customer_email']) ? $sale['customer_email'] : $sale->customer_email;
                    @endphp
                    @if($phone || $email)
                        @if($phone)
                            <small><i class="fas fa-phone"></i> {{ $phone }}</small><br>
                        @endif
                        @if($email)
                            <small><i class="fas fa-envelope"></i> {{ $email }}</small>
                        @endif
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>
                    @if(isset($sale['formatted_date']))
                        {{ $sale['formatted_date'] }}<br>
                        <small class="text-muted">{{ $sale['formatted_time'] }}</small>
                    @else
                        {{ $sale->created_at->format('M d, Y') }}<br>
                        <small class="text-muted">{{ $sale->created_at->format('H:i') }}</small>
                    @endif
                </td>
                <td>
                    <span class="badge bg-info">{{ isset($sale['total_items']) ? $sale['total_items'] : $sale->total_items }} items</span>
                </td>
                <td>
                    @php
                        $grandTotal = isset($sale['grand_total']) ? $sale['grand_total'] : $sale->grand_total;
                        $taxPercent = isset($sale['tax_percent']) ? $sale['tax_percent'] : $sale->tax_percent;
                    @endphp
                    <strong>${{ number_format($grandTotal, 2) }}</strong>
                    @if($taxPercent > 0)
                        <br><small class="text-muted">+{{ number_format($taxPercent, 2) }}% tax</small>
                    @endif
                </td>
                <td>
                    @if(isset($sale['admin']))
                        {{ $sale['admin']['full_name'] }}<br>
                        <small class="text-muted">{{ $sale['admin']['role'] }}</small>
                    @else
                        {{ $sale->admin->full_name }}<br>
                        <small class="text-muted">{{ ucfirst($sale->admin->role) }}</small>
                    @endif
                </td>
                <td>
                    @if(isset($sale['receipt_url']))
                        <a href="{{ $sale['receipt_url'] }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-receipt"></i> View
                        </a>
                    @else
                        <a href="{{ route('sales.receipt', $sale) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-receipt"></i> View
                        </a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Summary -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card bg-light">
            <div class="card-body">
                <h6 id="summary-title">
                    Summary
                    <small class="text-muted" id="summary-period"></small>
                </h6>
                @php
                    $totalTransactions = isset($sales[0]) && is_array($sales[0]) ? count($sales) : $sales->count();
                    $totalItems = isset($sales[0]) && is_array($sales[0]) ? 
                        collect($sales)->sum('total_items') : $sales->sum('total_items');
                    $totalRevenue = isset($sales[0]) && is_array($sales[0]) ? 
                        collect($sales)->sum('grand_total') : $sales->sum('grand_total');
                @endphp
                <p class="mb-1"><strong>Total Transactions:</strong> <span id="total-transactions">{{ $totalTransactions }}</span></p>
                <p class="mb-1"><strong>Total Items Sold:</strong> <span id="total-items">{{ $totalItems }}</span></p>
                <p class="mb-0"><strong>Total Revenue:</strong> $<span id="total-revenue">{{ number_format($totalRevenue, 2) }}</span></p>
                
                <div id="period-info" style="display: none;">
                    <hr class="my-2">
                    <small class="text-muted" id="period-range"></small>
                </div>
            </div>
        </div>
    </div>
</div>

@else
<div class="text-center py-5">
    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
    <h5>No sales found</h5>
    <p class="text-muted" id="no-sales-message">
        No sales have been recorded yet.
    </p>
    <a href="{{ route('sales.register') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Make First Sale
    </a>
</div>
@endif