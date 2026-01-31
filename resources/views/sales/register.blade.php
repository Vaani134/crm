@extends('layouts.app')

@section('title', 'Sales Register - Inventory & Sales')

@section('content')
<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-cash-register"></i> Sales Register</h2>
        <p class="text-muted">Point of Sale System</p>
    </div>
</div>

<div class="row">
    <!-- Product Selection -->
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-search"></i> Product Search</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="product-search" 
                           placeholder="Search products by name">
                </div>
                
                <div id="products-grid" class="row">
                    @foreach($products as $product)
                    <div class="col-md-6 col-lg-4 mb-3 product-item">
                        <div class="card h-100 product-card {{ $product->isLowStock() ? 'border-warning' : '' }}" style="cursor: pointer;">
                            @if($product->image_path)
                            <img src="{{ asset('storage/' . $product->image_path) }}" class="card-img-top" 
                                 style="height: 120px; object-fit: cover;" alt="{{ $product->name }}">
                            @else
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 120px;">
                                <i class="fas fa-image text-muted"></i>
                            </div>
                            @endif
                            
                            <div class="card-body p-2">
                                <h6 class="card-title mb-1">{{ Str::limit($product->name, 25) }}</h6>
                                
                                <!-- Product Description (2 lines max) -->
                                @if($product->description)
                                <div class="mb-1" style="font-size: 0.8rem; line-height: 1.3;">
                                    <p class="mb-1 text-muted" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        {{ $product->description }}
                                    </p>
                                    @if(strlen($product->description) > 100)
                                    <a href="#" class="text-primary" style="font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#productModal{{ $product->id }}" onclick="event.stopPropagation();">
                                        more...
                                    </a>
                                    @endif
                                </div>
                                @endif
                                
                                <p class="card-text small mb-1">
                                    <strong>${{ number_format($product->price, 2) }}</strong><br>
                                    Stock: <span class="badge {{ $product->isLowStock() ? 'bg-warning text-dark' : 'bg-success' }}">{{ $product->stock_qty }}</span>
                                </p>
                                
                                @if($product->isLowStock())
                                <div class="alert alert-warning py-1 px-2 mb-1" style="font-size: 0.75rem;">
                                    <i class="fas fa-exclamation-triangle"></i> Low Stock!
                                </div>
                                @endif
                                
                                <button type="button" class="btn btn-sm btn-primary w-100" 
                                        onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price }}, {{ $product->stock_qty }})">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
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
                                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" 
                                            onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price }}, {{ $product->stock_qty }})">
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    <!-- Shopping Cart -->
    <div class="col-md-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-shopping-cart"></i> Shopping Cart</h5>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearCart()">
                    <i class="fas fa-trash"></i> Clear
                </button>
            </div>
            <div class="card-body">
                <!-- Customer Information -->
                <div class="mb-3">
                    <h6 class="mb-2"><i class="fas fa-user"></i> Customer Information (Optional)</h6>
                    <div class="row">
                        <div class="col-12 mb-2">
                            <label for="customer-name" class="form-label">Name</label>
                            <input type="text" class="form-control form-control-sm" id="customer-name" 
                                   placeholder="Customer name">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="customer-phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control form-control-sm" id="customer-phone" 
                                   placeholder="Phone number">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="customer-email" class="form-label">Email</label>
                            <input type="email" class="form-control form-control-sm" id="customer-email" 
                                   placeholder="Email address">
                        </div>
                    </div>
                </div>
                
                <div id="cart-items">
                    <div class="text-center text-muted py-4" id="empty-cart">
                        <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                        <p>Cart is empty</p>
                    </div>
                </div>
                
                <!-- Cart Summary -->
                <div id="cart-summary" style="display: none;">
                    <hr>
                    <div class="mb-3">
                        <label for="tax-percent" class="form-label">Tax (%)</label>
                        <input type="number" class="form-control" id="tax-percent" 
                               value="0" min="0" max="100" step="0.01" onchange="updateTotals()">
                    </div>
                    
                    <div class="row mb-2">
                        <div class="col">Subtotal:</div>
                        <div class="col text-end" id="subtotal">$0.00</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col">Tax:</div>
                        <div class="col text-end" id="tax-amount">$0.00</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col"><strong>Total:</strong></div>
                        <div class="col text-end"><strong id="grand-total">$0.00</strong></div>
                    </div>
                    
                    <form action="{{ route('sales.checkout') }}" method="POST" id="checkout-form" onsubmit="debugFormSubmission()">
                        @csrf
                        <input type="hidden" name="cart_data" id="cart-data">
                        <input type="hidden" name="tax_percent" id="tax-percent-hidden">
                        <input type="hidden" name="customer_name" id="customer-name-hidden">
                        <input type="hidden" name="customer_phone" id="customer-phone-hidden">
                        <input type="hidden" name="customer_email" id="customer-email-hidden">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-credit-card"></i> Checkout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let cart = [];

function addToCart(productId, productName, price, stockQty) {
    // Check if product already in cart
    const existingItem = cart.find(item => item.product_id === productId);
    
    if (existingItem) {
        if (existingItem.qty < stockQty) {
            existingItem.qty++;
            existingItem.line_total = existingItem.qty * existingItem.unit_price;
        } else {
            alert('Not enough stock available!');
            return;
        }
    } else {
        cart.push({
            product_id: productId,
            name: productName,
            qty: 1,
            unit_price: price,
            line_total: price,
            max_stock: stockQty
        });
    }
    
    updateCartDisplay();
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.product_id !== productId);
    updateCartDisplay();
}

function updateQuantity(productId, newQty) {
    const item = cart.find(item => item.product_id === productId);
    if (item) {
        if (newQty <= 0) {
            removeFromCart(productId);
        } else if (newQty <= item.max_stock) {
            item.qty = newQty;
            item.line_total = item.qty * item.unit_price;
            updateCartDisplay();
        } else {
            alert('Not enough stock available!');
        }
    }
}

function clearCart() {
    cart = [];
    updateCartDisplay();
}

function updateCartDisplay() {
    const cartItemsDiv = document.getElementById('cart-items');
    const emptyCart = document.getElementById('empty-cart');
    const cartSummary = document.getElementById('cart-summary');
    
    if (cart.length === 0) {
        cartItemsDiv.innerHTML = '<div class="text-center text-muted py-4" id="empty-cart"><i class="fas fa-shopping-cart fa-2x mb-2"></i><p>Cart is empty</p></div>';
        cartSummary.style.display = 'none';
        return;
    }
    
    let html = '';
    cart.forEach(item => {
        html += `
            <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                <div class="flex-grow-1">
                    <h6 class="mb-1">${item.name}</h6>
                    <small class="text-muted">$${item.unit_price.toFixed(2)} each</small>
                </div>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-secondary" 
                            onclick="updateQuantity(${item.product_id}, ${item.qty - 1})">-</button>
                    <span class="mx-2">${item.qty}</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" 
                            onclick="updateQuantity(${item.product_id}, ${item.qty + 1})">+</button>
                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" 
                            onclick="removeFromCart(${item.product_id})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="text-end ms-2">
                    <strong>$${item.line_total.toFixed(2)}</strong>
                </div>
            </div>
        `;
    });
    
    cartItemsDiv.innerHTML = html;
    cartSummary.style.display = 'block';
    updateTotals();
}

function updateTotals() {
    const subtotal = cart.reduce((sum, item) => sum + item.line_total, 0);
    const taxPercent = parseFloat(document.getElementById('tax-percent').value) || 0;
    const taxAmount = (subtotal * taxPercent) / 100;
    const grandTotal = subtotal + taxAmount;
    
    document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('tax-amount').textContent = '$' + taxAmount.toFixed(2);
    document.getElementById('grand-total').textContent = '$' + grandTotal.toFixed(2);
    
    // Update hidden form fields
    document.getElementById('cart-data').value = JSON.stringify(cart);
    document.getElementById('tax-percent-hidden').value = taxPercent;
    document.getElementById('customer-name-hidden').value = document.getElementById('customer-name').value;
    document.getElementById('customer-phone-hidden').value = document.getElementById('customer-phone').value;
    document.getElementById('customer-email-hidden').value = document.getElementById('customer-email').value;
}

// Product search functionality
document.getElementById('product-search').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const productItems = document.querySelectorAll('.product-item');
    
    productItems.forEach(item => {
        const productName = item.querySelector('.card-title').textContent.toLowerCase();
        if (productName.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Customer information change handlers
document.getElementById('customer-name').addEventListener('input', function() {
    document.getElementById('customer-name-hidden').value = this.value;
});

document.getElementById('customer-phone').addEventListener('input', function() {
    document.getElementById('customer-phone-hidden').value = this.value;
});

document.getElementById('customer-email').addEventListener('input', function() {
    document.getElementById('customer-email-hidden').value = this.value;
});

// Debug function for form submission
function debugFormSubmission() {
    const customerName = document.getElementById('customer-name').value;
    const customerPhone = document.getElementById('customer-phone').value;
    const customerEmail = document.getElementById('customer-email').value;
    console.log('Customer Name:', customerName);
    console.log('Customer Phone:', customerPhone);
    console.log('Customer Email:', customerEmail);
    return true; // Allow form submission
}

// Initialize
updateCartDisplay();
</script>
@endsection