@extends('layouts.app')

@section('title', 'Sales History - Inventory & Sales')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-history"></i> Sales History</h2>
            <a href="{{ route('sales.register') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Sale
            </a>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="sales-filter" class="form-label">Time Period</label>
                        <select id="sales-filter" class="form-select">
                            <option value="">All Sales</option>
                            <option value="today" {{ request('filter') === 'today' ? 'selected' : '' }}>
                                Today's Sales
                            </option>
                            <option value="week" {{ request('filter') === 'week' ? 'selected' : '' }}>
                                This Week's Sales
                            </option>
                            <option value="month" {{ request('filter') === 'month' ? 'selected' : '' }}>
                                This Month's Sales
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="employee-filter" class="form-label">Employee</label>
                        <select id="employee-filter" class="form-select">
                            <option value="">All Employees</option>
                            @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->full_name }} ({{ ucfirst($employee->role) }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="button" id="clear-filter" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Clear Filters
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div id="filter-loading" class="text-center" style="display: none;">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <small class="text-muted ms-2">Filtering sales...</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sales Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div id="sales-content">
                    @include('sales.partials.sales-table', ['sales' => $sales])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterSelect = document.getElementById('sales-filter');
    const employeeSelect = document.getElementById('employee-filter');
    const clearButton = document.getElementById('clear-filter');
    const loadingIndicator = document.getElementById('filter-loading');
    const salesContent = document.getElementById('sales-content');
    
    // Filter change events
    filterSelect.addEventListener('change', function() {
        performFilter();
    });
    
    employeeSelect.addEventListener('change', function() {
        performFilter();
    });
    
    // Clear filter event
    clearButton.addEventListener('click', function() {
        filterSelect.value = '';
        employeeSelect.value = '';
        performFilter();
    });
    
    function performFilter() {
        loadingIndicator.style.display = 'block';
        
        // Build URL with filter parameters
        let url = '{{ route("sales.history") }}';
        const params = new URLSearchParams();
        
        if (filterSelect.value) {
            params.append('filter', filterSelect.value);
        }
        
        if (employeeSelect.value) {
            params.append('employee_id', employeeSelect.value);
        }
        
        if (params.toString()) {
            url += '?' + params.toString();
        }
        
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success === false) {
                throw new Error(data.error || 'Unknown error occurred');
            }
            
            // Update the content dynamically
            updateSalesContent(data.sales, data.summary);
            updateURL();
        })
        .catch(error => {
            console.error('Filter error:', error);
            showError('Failed to filter sales: ' + error.message);
        })
        .finally(() => {
            loadingIndicator.style.display = 'none';
        });
    }
    
    function updateSalesContent(sales, summary) {
        // Simple approach: create the HTML content
        let html = '';
        
        if (sales.length === 0) {
            html = getEmptyStateHTML(summary.filter);
        } else {
            html = getSalesTableHTML(sales, summary);
        }
        
        salesContent.innerHTML = html;
    }
    
    function getSalesTableHTML(sales, summary) {
        let html = '<div class="table-responsive"><table class="table table-hover">';
        html += '<thead class="table-dark"><tr>';
        html += '<th>Receipt #</th><th>Customer</th><th>Contact</th><th>Date & Time</th>';
        html += '<th>Items</th><th>Total</th><th>Cashier</th><th>Actions</th>';
        html += '</tr></thead><tbody>';
        
        sales.forEach(sale => {
            const customerName = sale.customer_name || '<span class="text-muted">Walk-in</span>';
            const phone = sale.customer_phone ? `<small><i class="fas fa-phone"></i> ${sale.customer_phone}</small>` : '';
            const email = sale.customer_email ? `<small><i class="fas fa-envelope"></i> ${sale.customer_email}</small>` : '';
            const contact = phone || email ? (phone + (phone && email ? '<br>' : '') + email) : '<span class="text-muted">-</span>';
            const taxInfo = sale.tax_percent > 0 ? `<br><small class="text-muted">+${sale.tax_percent.toFixed(2)}% tax</small>` : '';
            
            html += `<tr>
                <td><strong>${sale.receipt_no}</strong></td>
                <td>${customerName}</td>
                <td>${contact}</td>
                <td>${sale.formatted_date}<br><small class="text-muted">${sale.formatted_time}</small></td>
                <td><span class="badge bg-info">${sale.total_items} items</span></td>
                <td><strong>$${sale.grand_total.toFixed(2)}</strong>${taxInfo}</td>
                <td>${sale.admin.full_name}<br><small class="text-muted">${sale.admin.role}</small></td>
                <td><a href="${sale.receipt_url}" class="btn btn-sm btn-outline-primary"><i class="fas fa-receipt"></i> View</a></td>
            </tr>`;
        });
        
        html += '</tbody></table></div>';
        
        // Add summary
        html += '<div class="row mt-4"><div class="col-md-6"><div class="card bg-light"><div class="card-body">';
        html += `<h6>Summary`;
        
        // Add filter information to summary
        let filterInfo = [];
        if (summary.period_text && summary.period_text.title) {
            filterInfo.push(summary.period_text.title);
        }
        if (summary.employee_id) {
            const employeeSelect = document.getElementById('employee-filter');
            if (employeeSelect && employeeSelect.selectedIndex > 0) {
                const selectedEmployee = employeeSelect.options[employeeSelect.selectedIndex].text;
                filterInfo.push(`Employee: ${selectedEmployee.split(' (')[0]}`);
            }
        }
        
        if (filterInfo.length > 0) {
            html += ` <small class="text-muted">(${filterInfo.join(', ')})</small>`;
        }
        
        html += `</h6>`;
        html += `<p class="mb-1"><strong>Total Transactions:</strong> ${summary.total_transactions}</p>`;
        html += `<p class="mb-1"><strong>Total Items Sold:</strong> ${summary.total_items}</p>`;
        html += `<p class="mb-0"><strong>Total Revenue:</strong> $${summary.total_revenue.toFixed(2)}</p>`;
        
        if (summary.period_text.range) {
            html += `<hr class="my-2"><small class="text-muted">Period: ${summary.period_text.range}</small>`;
        }
        
        html += '</div></div></div></div>';
        
        return html;
    }
    
    function getEmptyStateHTML(filter) {
        let message = 'No sales have been recorded yet.';
        
        switch (filter) {
            case 'today':
                message = 'No sales have been made today yet.';
                break;
            case 'week':
                message = 'No sales have been made this week yet.';
                break;
            case 'month':
                message = 'No sales have been made this month yet.';
                break;
        }
        
        return `<div class="text-center py-5">
            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
            <h5>No sales found</h5>
            <p class="text-muted">${message}</p>
            <a href="{{ route('sales.register') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Make First Sale
            </a>
        </div>`;
    }
    
    function updateURL() {
        const url = new URL(window.location);
        
        // Clear existing parameters
        url.searchParams.delete('filter');
        url.searchParams.delete('employee_id');
        
        // Add current filter values
        if (filterSelect.value) {
            url.searchParams.set('filter', filterSelect.value);
        }
        
        if (employeeSelect.value) {
            url.searchParams.set('employee_id', employeeSelect.value);
        }
        
        window.history.pushState({}, '', url);
    }
    
    function showError(message) {
        alert(message);
    }
});
</script>
@endsection