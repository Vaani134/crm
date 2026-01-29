@forelse($products as $product)
<div class="col-md-6 col-lg-4 mb-4">
    <div class="card h-100 {{ $product->isLowStock() ? 'border-warning' : '' }}">
        @if($product->image_path)
        <img src="{{ asset('storage/' . $product->image_path) }}" class="card-img-top" 
             style="height: 200px; object-fit: cover;" alt="{{ $product->name }}">
        @else
        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
             style="height: 200px;">
            <i class="fas fa-image fa-3x text-muted"></i>
        </div>
        @endif
        
        <div class="card-body d-flex flex-column">
            <h5 class="card-title">{{ $product->name }}</h5>
            @if($product->category)
            <div class="mb-2">
                <span class="badge" style="background-color: {{ $product->category->color }}; color: white;">
                    <i class="{{ $product->category->icon }}"></i> {{ $product->category->name }}
                </span>
            </div>
            @endif
            <p class="card-text">
                <strong>SKU:</strong> {{ $product->barcode_number }}<br>
                <strong>Price:</strong> ${{ number_format($product->price, 2) }}<br>
                <strong>Stock:</strong> 
                <span class="badge {{ $product->isLowStock() ? 'bg-warning text-dark' : 'bg-success' }}">
                    {{ $product->stock_qty }} units
                </span>
            </p>
            
            @if($product->isLowStock())
            <div class="alert alert-warning py-2">
                <i class="fas fa-exclamation-triangle"></i> Low Stock!
            </div>
            @endif
            
            <div class="mt-auto">
                <div class="btn-group w-100" role="group">
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="{{ route('products.refill.single', $product) }}" 
                       class="btn {{ $product->isLowStock() ? 'btn-warning' : 'btn-outline-success' }} btn-sm"
                       title="{{ $product->isLowStock() ? 'Priority Refill Needed' : 'Add Stock' }}">
                        <i class="fas fa-plus-circle"></i> 
                        @if($product->stock_qty == 0)
                            Restock
                        @elseif($product->isLowStock())
                            Refill
                        @else
                            Stock+
                        @endif
                    </a>
                    <form action="{{ route('products.destroy', $product) }}" method="POST" 
                          class="d-inline" onsubmit="return confirm('Are you sure?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@empty
<div class="col-12">
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle"></i> No products found.
        <a href="{{ route('products.create') }}" class="alert-link">Add your first product</a>
    </div>
</div>
@endforelse