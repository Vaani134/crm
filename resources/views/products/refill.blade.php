@extends('layouts.app')

@section('title', 'Refill Stock - Inventory & Sales')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-plus-circle"></i> Refill Stock</h2>
            <a href="{{ route('products.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-box"></i> Select Product to Refill</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('products.update.stock') }}" method="POST">
                    @csrf
                    
                    <!-- Combined Search + Dropdown -->
                    <div class="mb-3">
                        <label for="product_search" class="form-label">
                            <i class="fas fa-search"></i> Select Product *
                        </label>
                        <div class="position-relative">
                            <input type="text" 
                                   class="form-control @error('product_id') is-invalid @enderror" 
                                   id="product_search" 
                                   placeholder="Search by name, SKU, or barcode...">
                            <div id="product_dropdown" class="position-absolute w-100 bg-white border rounded mt-1" 
                                 style="display: none; max-height: 300px; overflow-y: auto; z-index: 1000; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            </div>
                        </div>
                        <small class="text-muted">Type to search or click to see all products</small>
                        @error('product_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Hidden input to store selected product ID -->
                    <input type="hidden" id="product_id" name="product_id" value="{{ old('product_id') }}" required>
                    
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity to Add *</label>
                        <input type="number" min="1" 
                               class="form-control @error('quantity') is-invalid @enderror" 
                               id="quantity" name="quantity" value="{{ old('quantity') }}" required>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-info-circle"></i> Product Details</h5>
            </div>
            <div class="card-body">
                <div id="product-info" style="display: none;">
                    <p><strong>Name:</strong> <span id="info-name">-</span></p>
                    <p><strong>SKU:</strong> <span id="info-sku">-</span></p>
                    <p><strong>Price:</strong> $<span id="info-price">-</span></p>
                    <p><strong>Current Stock:</strong> <span id="info-stock">-</span> units</p>
                    <div id="low-stock-alert" class="alert alert-warning" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i> Low Stock Item!
                    </div>
                </div>
                <div id="no-selection" class="text-muted">
                    <i class="fas fa-arrow-up"></i> Select a product to see details
                </div>
            </div>
        </div>
        
        <!-- Low Stock Products -->
        @php
            $lowStockProducts = $products->filter(fn($p) => $p->isLowStock());
            $outOfStockProducts = $products->filter(fn($p) => $p->stock_qty == 0);
        @endphp
        
        @if($lowStockProducts->count() > 0)
        <div class="card mt-3">
            <div class="card-header bg-warning">
                <h6 class="mb-0">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Priority Restocking ({{ $lowStockProducts->count() }} items)
                </h6>
            </div>
            <div class="card-body">
                @if($outOfStockProducts->count() > 0)
                <div class="mb-3">
                    <strong class="text-danger">Out of Stock ({{ $outOfStockProducts->count() }}):</strong>
                    @foreach($outOfStockProducts as $product)
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-danger">🔴 {{ Str::limit($product->name, 20) }}</small>
                        <span class="badge bg-danger">0</span>
                    </div>
                    @endforeach
                </div>
                @endif
                
                @php
                    $lowButNotOutProducts = $lowStockProducts->filter(fn($p) => $p->stock_qty > 0);
                @endphp
                
                @if($lowButNotOutProducts->count() > 0)
                <div>
                    <strong class="text-warning">Low Stock ({{ $lowButNotOutProducts->count() }}):</strong>
                    @foreach($lowButNotOutProducts as $product)
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-warning">⚠️ {{ Str::limit($product->name, 20) }}</small>
                        <span class="badge bg-warning text-dark">{{ $product->stock_qty }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
// Product data
const productsData = {!! json_encode($products->map(function($p) {
    return [
        'id' => $p->id,
        'name' => $p->name,
        'sku' => $p->barcode_number,
        'price' => $p->price,
        'stock' => $p->stock_qty,
        'isLowStock' => $p->isLowStock(),
        'search' => strtolower($p->name . ' ' . $p->barcode_number)
    ];
})->values()) !!};

const searchInput = document.getElementById('product_search');
const dropdown = document.getElementById('product_dropdown');
const productIdInput = document.getElementById('product_id');
let selectedProduct = null;

// Show dropdown on focus
searchInput.addEventListener('focus', function() {
    if (this.value === '') {
        renderDropdown(productsData);
    }
});

// Filter and show dropdown on input
searchInput.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase().trim();
    
    if (searchTerm === '') {
        renderDropdown(productsData);
    } else {
        const filtered = productsData.filter(product => 
            product.search.includes(searchTerm)
        );
        renderDropdown(filtered);
    }
});

// Render dropdown items
function renderDropdown(items) {
    dropdown.innerHTML = '';
    
    if (items.length === 0) {
        dropdown.innerHTML = '<div class="p-3 text-muted text-center">No products found</div>';
        dropdown.style.display = 'block';
        return;
    }
    
    items.forEach(product => {
        const item = document.createElement('div');
        item.className = 'p-2 border-bottom cursor-pointer hover-item';
        item.style.cursor = 'pointer';
        item.style.padding = '10px 12px';
        item.style.transition = 'background-color 0.2s';
        
        // Status indicator
        let statusIcon = '';
        let statusText = '';
        if (product.stock === 0) {
            statusIcon = '🔴';
            statusText = 'OUT OF STOCK';
        } else if (product.isLowStock) {
            statusIcon = '⚠️';
            statusText = `LOW: ${product.stock} units`;
        } else {
            statusIcon = '✓';
            statusText = `Stock: ${product.stock} units`;
        }
        
        item.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="fw-bold">${product.name}</div>
                    <small class="text-muted">SKU: ${product.sku}</small>
                </div>
                <div class="text-end">
                    <div class="small">${statusIcon} ${statusText}</div>
                    <div class="text-primary fw-bold">$${parseFloat(product.price).toFixed(2)}</div>
                </div>
            </div>
        `;
        
        // Hover effect
        item.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8f9fa';
        });
        item.addEventListener('mouseleave', function() {
            this.style.backgroundColor = 'transparent';
        });
        
        // Click to select
        item.addEventListener('click', function() {
            selectProduct(product);
        });
        
        dropdown.appendChild(item);
    });
    
    dropdown.style.display = 'block';
}

// Select product
function selectProduct(product) {
    selectedProduct = product;
    productIdInput.value = product.id;
    searchInput.value = `${product.name} (${product.sku})`;
    dropdown.style.display = 'none';
    updateProductInfo();
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (e.target !== searchInput && e.target !== dropdown) {
        dropdown.style.display = 'none';
    }
});

// Allow keyboard navigation
searchInput.addEventListener('keydown', function(e) {
    const items = dropdown.querySelectorAll('.hover-item');
    
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (items.length > 0) {
            items[0].focus();
        }
    } else if (e.key === 'Escape') {
        dropdown.style.display = 'none';
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (items.length > 0) {
            items[0].click();
        }
    }
});

function updateProductInfo() {
    if (selectedProduct) {
        document.getElementById('info-name').textContent = selectedProduct.name;
        document.getElementById('info-sku').textContent = selectedProduct.sku;
        document.getElementById('info-price').textContent = selectedProduct.price;
        document.getElementById('info-stock').textContent = selectedProduct.stock;
        
        document.getElementById('product-info').style.display = 'block';
        document.getElementById('no-selection').style.display = 'none';
        
        // Show/hide low stock alert
        if (selectedProduct.isLowStock) {
            document.getElementById('low-stock-alert').style.display = 'block';
        } else {
            document.getElementById('low-stock-alert').style.display = 'none';
        }
    } else {
        document.getElementById('product-info').style.display = 'none';
        document.getElementById('no-selection').style.display = 'block';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // If there's a pre-selected product (from validation error), show it
    if (productIdInput.value) {
        const product = productsData.find(p => p.id == productIdInput.value);
        if (product) {
            selectProduct(product);
        }
    }
});
</script>
@endsection