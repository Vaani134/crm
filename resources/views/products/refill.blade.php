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
                    
                    <div class="mb-3">
                        <label for="product_id" class="form-label">Product *</label>
                        <select class="form-select @error('product_id') is-invalid @enderror" 
                                id="product_id" name="product_id" required onchange="updateProductInfo()">
                            <option value="">Select a product...</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}" 
                                    data-name="{{ $product->name }}"
                                    data-sku="{{ $product->barcode_number }}"
                                    data-price="{{ $product->price }}"
                                    data-stock="{{ $product->stock_qty }}"
                                    data-low-stock="{{ $product->isLowStock() ? 'true' : 'false' }}"
                                    style="{{ $product->isLowStock() ? 'background-color: #fff3cd; font-weight: bold;' : '' }}"
                                    {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                @if($product->stock_qty == 0)
                                    🔴 {{ $product->name }} ({{ $product->barcode_number }}) - OUT OF STOCK
                                @elseif($product->isLowStock())
                                    ⚠️ {{ $product->name }} ({{ $product->barcode_number }}) - LOW: {{ $product->stock_qty }} units
                                @else
                                    {{ $product->name }} ({{ $product->barcode_number }}) - Stock: {{ $product->stock_qty }} units
                                @endif
                            </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
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
function updateProductInfo() {
    const select = document.getElementById('product_id');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        document.getElementById('info-name').textContent = selectedOption.dataset.name;
        document.getElementById('info-sku').textContent = selectedOption.dataset.sku;
        document.getElementById('info-price').textContent = selectedOption.dataset.price;
        document.getElementById('info-stock').textContent = selectedOption.dataset.stock;
        
        document.getElementById('product-info').style.display = 'block';
        document.getElementById('no-selection').style.display = 'none';
        
        // Show/hide low stock alert
        if (selectedOption.dataset.lowStock === 'true') {
            document.getElementById('low-stock-alert').style.display = 'block';
        } else {
            document.getElementById('low-stock-alert').style.display = 'none';
        }
    } else {
        document.getElementById('product-info').style.display = 'none';
        document.getElementById('no-selection').style.display = 'block';
    }
}
</script>
@endsection