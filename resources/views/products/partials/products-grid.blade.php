@forelse($products as $product)
<div class="col-md-4 col-lg-3 col-xl-2 mb-3">
    <div class="card h-100 {{ $product->isLowStock() ? 'border-warning' : '' }}" style="font-size: 0.9rem;">
        @if($product->image_path)
        <img src="{{ asset('storage/' . $product->image_path) }}" class="card-img-top" 
             style="height: 120px; object-fit: cover;" alt="{{ $product->name }}">
        @else
        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
             style="height: 120px;">
            <i class="fas fa-image fa-2x text-muted"></i>
        </div>
        @endif
        
        <div class="card-body d-flex flex-column p-2">
            <h6 class="card-title mb-1" style="font-size: 0.95rem;">{{ Str::limit($product->name, 20) }}</h6>
            @if($product->category)
            <div class="mb-1">
                <span class="badge" style="background-color: {{ $product->category->color }}; color: white; font-size: 0.75rem;">
                    <i class="{{ $product->category->icon }}"></i> {{ Str::limit($product->category->name, 10) }}
                </span>
            </div>
            @endif
            
            <!-- Product Description (2 lines max) -->
            @if($product->description)
            <div class="mb-1" style="font-size: 0.8rem; line-height: 1.3;">
                <p class="mb-1 text-muted" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    {{ $product->description }}
                </p>
                @if(strlen($product->description) > 100)
                <a href="#" class="text-primary" style="font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#productModal{{ $product->id }}">
                    more...
                </a>
                @endif
            </div>
            @endif
            
            <p class="card-text mb-1" style="font-size: 0.8rem;">
                <strong>SKU:</strong> {{ Str::limit($product->barcode_number, 12) }}<br>
                <strong>Price:</strong> ${{ number_format($product->price, 2) }}<br>
                <strong>Stock:</strong> 
                <span class="badge {{ $product->isLowStock() ? 'bg-warning text-dark' : 'bg-success' }}" style="font-size: 0.75rem;">
                    {{ $product->stock_qty }}
                </span>
            </p>
            
            @if($product->isLowStock())
            <div class="alert alert-warning py-1 px-2 mb-1" style="font-size: 0.75rem;">
                <i class="fas fa-exclamation-triangle"></i> Low Stock!
            </div>
            @endif
            
            <div class="mt-auto">
                <div class="btn-group w-100" role="group" style="gap: 2px;">
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="{{ route('products.refill.single', $product) }}" 
                       class="btn {{ $product->isLowStock() ? 'btn-warning' : 'btn-outline-success' }} btn-sm"
                       style="font-size: 0.75rem; padding: 0.25rem 0.5rem;"
                       title="{{ $product->isLowStock() ? 'Priority Refill Needed' : 'Add Stock' }}">
                        <i class="fas fa-plus-circle"></i>
                    </a>
                    <form action="{{ route('products.destroy', $product) }}" method="POST" 
                          class="d-inline" onsubmit="return confirm('Are you sure?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Product Details Modal -->
    @if($product->description)
    <div class="modal fade" id="productModal{{ $product->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $product->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($product->image_path)
                    <img src="{{ asset('storage/' . $product->image_path) }}" class="img-fluid mb-3" alt="{{ $product->name }}">
                    @endif
                    
                    <h6>Description</h6>
                    <p>{{ $product->description }}</p>
                    
                    @if($product->brand_name)
                    <p><strong>Brand:</strong> {{ $product->brand_name }}</p>
                    @endif
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-6">
                            <p><strong>SKU:</strong> {{ $product->barcode_number }}</p>
                            <p><strong>Price:</strong> ${{ number_format($product->price, 2) }}</p>
                            <p><strong>Stock:</strong> {{ $product->stock_qty }} units</p>
                        </div>
                        <div class="col-6">
                            @if($product->category)
                            <p><strong>Category:</strong> {{ $product->category->name }}</p>
                            @endif
                            @if($product->manufacturing_date)
                            <p><strong>Mfg Date:</strong> {{ $product->manufacturing_date->format('M d, Y') }}</p>
                            @endif
                            @if($product->expiry_date)
                            <p><strong>Expiry:</strong> {{ $product->expiry_date->format('M d, Y') }}</p>
                            @endif
                        </div>
                    </div>
                    
                    @if($product->warranty_months || $product->guarantee_months || $product->tax_percentage || $product->discount_percentage)
                    <hr>
                    <div class="row">
                        @if($product->warranty_months)
                        <div class="col-6">
                            <p><strong>Warranty:</strong> {{ $product->warranty_months }} months</p>
                        </div>
                        @endif
                        @if($product->guarantee_months)
                        <div class="col-6">
                            <p><strong>Guarantee:</strong> {{ $product->guarantee_months }} months</p>
                        </div>
                        @endif
                        @if($product->tax_percentage)
                        <div class="col-6">
                            <p><strong>Tax:</strong> {{ $product->tax_percentage }}%</p>
                        </div>
                        @endif
                        @if($product->discount_percentage)
                        <div class="col-6">
                            <p><strong>Discount:</strong> {{ $product->discount_percentage }}%</p>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">Edit Product</a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@empty
<div class="col-12">
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle"></i> No products found.
        <a href="{{ route('products.create') }}" class="alert-link">Add your first product</a>
    </div>
</div>
@endforelse