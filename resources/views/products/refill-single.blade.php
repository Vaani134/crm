@extends('layouts.app')

@section('title', 'Refill Stock - ' . $product->name)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-plus-circle"></i> Refill Stock</h2>
            <div>
                <a href="{{ route('products.index') }}" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
                <a href="{{ route('products.refill') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-list"></i> Bulk Refill
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-box"></i> Refill Stock for: {{ $product->name }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('products.update.stock') }}" method="POST">
                    @csrf
                    
                    <!-- Hidden product ID -->
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    
                    <div class="mb-4">
                        <h6>Product Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> {{ $product->name }}</p>
                                <p><strong>SKU:</strong> {{ $product->barcode_number }}</p>
                                <p><strong>Price:</strong> ${{ number_format($product->price, 2) }}</p>
                            </div>
                            <div class="col-md-6">
                                @if($product->category)
                                <p><strong>Category:</strong> 
                                    <span class="badge" style="background-color: {{ $product->category->color }}; color: white;">
                                        <i class="{{ $product->category->icon }}"></i> {{ $product->category->name }}
                                    </span>
                                </p>
                                @endif
                                <p><strong>Current Stock:</strong> 
                                    <span class="badge {{ $product->isLowStock() ? 'bg-warning text-dark' : 'bg-success' }}">
                                        {{ $product->stock_qty }} units
                                    </span>
                                </p>
                                @if($product->isLowStock())
                                <div class="alert alert-warning py-2">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    @if($product->stock_qty == 0)
                                        Out of Stock!
                                    @else
                                        Low Stock Alert!
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity to Add *</label>
                        <input type="number" min="1" 
                               class="form-control @error('quantity') is-invalid @enderror" 
                               id="quantity" name="quantity" value="{{ old('quantity', $product->isLowStock() ? 10 : 5) }}" 
                               required onchange="updateNewStock()">
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Enter the number of units you want to add to the current stock.
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="alert alert-info">
                            <strong>Stock Calculation:</strong><br>
                            Current Stock: <span id="current-stock">{{ $product->stock_qty }}</span> units<br>
                            Adding: <span id="adding-qty">{{ old('quantity', $product->isLowStock() ? 10 : 5) }}</span> units<br>
                            <strong>New Total: <span id="new-total">{{ $product->stock_qty + (old('quantity', $product->isLowStock() ? 10 : 5)) }}</span> units</strong>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('products.index') }}" class="btn btn-secondary me-2">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-plus"></i> Add Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        @if($product->image_path)
        <div class="card mb-3">
            <div class="card-header">
                <h6><i class="fas fa-image"></i> Product Image</h6>
            </div>
            <div class="card-body text-center">
                <img src="{{ asset('storage/' . $product->image_path) }}" 
                     class="img-fluid rounded" 
                     style="max-height: 200px;" 
                     alt="{{ $product->name }}">
            </div>
        </div>
        @endif
        
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-lightbulb"></i> Quick Tips</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i> 
                        <small>Add stock based on your sales forecast</small>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i> 
                        <small>Consider storage space limitations</small>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i> 
                        <small>Keep track of expiration dates if applicable</small>
                    </li>
                    @if($product->isLowStock())
                    <li class="mb-2">
                        <i class="fas fa-exclamation-triangle text-warning"></i> 
                        <small><strong>Priority item:</strong> This product needs immediate restocking</small>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6><i class="fas fa-cog"></i> Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit"></i> Edit Product
                    </a>
                    <a href="{{ route('products.refill') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-list"></i> Bulk Refill Page
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function updateNewStock() {
    const currentStock = {{ $product->stock_qty }};
    const quantityInput = document.getElementById('quantity');
    const addingQty = parseInt(quantityInput.value) || 0;
    const newTotal = currentStock + addingQty;
    
    document.getElementById('current-stock').textContent = currentStock;
    document.getElementById('adding-qty').textContent = addingQty;
    document.getElementById('new-total').textContent = newTotal;
}

// Set default quantity based on stock level
document.addEventListener('DOMContentLoaded', function() {
    const isLowStock = {{ $product->isLowStock() ? 'true' : 'false' }};
    const quantityInput = document.getElementById('quantity');
    
    if (isLowStock && !quantityInput.value) {
        quantityInput.value = 10; // Suggest higher quantity for low stock items
        updateNewStock();
    }
});
</script>
@endsection