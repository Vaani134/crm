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
                        <div class="card h-100 product-card" style="cursor: pointer;" 
                             onclick="addToCart({{ $product->id }}, '{{ $product->name }}', {{ $product->price }}, {{ $product->stock_qty }})">
                            @if($product->image_path)
                            <img src="{{ asset('storage/' . $product->image_path) }}" class="card-img-top" 
                                 style="height: 120px; object-fit: cover;">
                            @else
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 120px;">
                                <i class="fas fa-image text-muted"></i>
                            </div>
                            @endif
                            
                            <div class="card-body p-2">
                                <h6 class="card-title mb-1">{{ Str::limit($product->name, 25) }}</h6>
                                <p class="card-text small mb-1">
                                    <strong>${{ number_format($product->price, 2) }}</strong><br>
                                    Stock: {{ $product->stock_qty }}
                                </p>
                            </div>
                        </div>
                    </div>
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