@extends('layouts.app')

@section('title', 'Add Product - Inventory & Sales')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-plus"></i> Add Product</h2>
            <a href="{{ route('products.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    @if($categories->count() > 0)
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category *</label>
                        <select class="form-select @error('category_id') is-invalid @enderror" 
                                id="category_id" name="category_id" required>
                            <option value="">Select a category...</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <a href="{{ route('categories.create') }}" target="_blank">Create new category</a>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Categories Not Set Up</h6>
                        <p>Product categories are not configured yet. You can still create products, but consider setting up categories first for better organization.</p>
                        <a href="/setup-categories.php" class="btn btn-sm btn-warning" target="_blank">
                            <i class="fas fa-cog"></i> Set Up Categories
                        </a>
                    </div>
                    @endif
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Product Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="barcode_number" class="form-label">SKU/Barcode *</label>
                            <input type="text" class="form-control @error('barcode_number') is-invalid @enderror" 
                                   id="barcode_number" name="barcode_number" value="{{ old('barcode_number') }}" required>
                            @error('barcode_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price ($) *</label>
                            <input type="number" step="0.01" min="0" 
                                   class="form-control @error('price') is-invalid @enderror" 
                                   id="price" name="price" value="{{ old('price') }}" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="stock_qty" class="form-label">Stock Quantity *</label>
                            <input type="number" min="0" 
                                   class="form-control @error('stock_qty') is-invalid @enderror" 
                                   id="stock_qty" name="stock_qty" value="{{ old('stock_qty') }}" required>
                            @error('stock_qty')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Product Image</label>
                        <input type="file" class="form-control @error('image') is-invalid @enderror" 
                               id="image" name="image" accept="image/*">
                        <div class="form-text">Supported formats: JPG, PNG, GIF (Max: 2MB)</div>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Product Details Section -->
                    <hr class="my-4">
                    <h5 class="mb-3"><i class="fas fa-details"></i> Product Details</h5>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Product Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4" 
                                  placeholder="Enter complete product details...">{{ old('description') }}</textarea>
                        <div class="form-text">Include features, specifications, and any other relevant information</div>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="brand_name" class="form-label">Brand Name</label>
                            <input type="text" class="form-control @error('brand_name') is-invalid @enderror" 
                                   id="brand_name" name="brand_name" value="{{ old('brand_name') }}" 
                                   placeholder="e.g., Samsung, Apple, Sony">
                            @error('brand_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="manufacturing_date" class="form-label">Manufacturing Date</label>
                            <input type="date" class="form-control @error('manufacturing_date') is-invalid @enderror" 
                                   id="manufacturing_date" name="manufacturing_date" value="{{ old('manufacturing_date') }}">
                            @error('manufacturing_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="expiry_date" class="form-label">Expiry Date</label>
                            <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                   id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}">
                            <div class="form-text">Leave empty if product doesn't expire</div>
                            @error('expiry_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="warranty_months" class="form-label">Warranty Period (Months)</label>
                            <input type="number" min="0" class="form-control @error('warranty_months') is-invalid @enderror" 
                                   id="warranty_months" name="warranty_months" value="{{ old('warranty_months') }}" 
                                   placeholder="e.g., 12">
                            @error('warranty_months')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="guarantee_months" class="form-label">Guarantee Period (Months)</label>
                            <input type="number" min="0" class="form-control @error('guarantee_months') is-invalid @enderror" 
                                   id="guarantee_months" name="guarantee_months" value="{{ old('guarantee_months') }}" 
                                   placeholder="e.g., 6">
                            @error('guarantee_months')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="tax_percentage" class="form-label">Tax (%) Applied</label>
                            <input type="number" step="0.01" min="0" max="100" 
                                   class="form-control @error('tax_percentage') is-invalid @enderror" 
                                   id="tax_percentage" name="tax_percentage" value="{{ old('tax_percentage', 0) }}" 
                                   placeholder="e.g., 18">
                            @error('tax_percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="discount_percentage" class="form-label">Discount (%) Applied</label>
                        <input type="number" step="0.01" min="0" max="100" 
                               class="form-control @error('discount_percentage') is-invalid @enderror" 
                               id="discount_percentage" name="discount_percentage" value="{{ old('discount_percentage', 0) }}" 
                               placeholder="e.g., 10">
                        @error('discount_percentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Stock-wise Expiry Details:</strong> These can be managed from the product edit page after creation.
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Add Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-info-circle"></i> Guidelines</h5>
            </div>
            <div class="card-body">
                <h6>Basic Information</h6>
                <ul class="list-unstyled small">
                    <li><i class="fas fa-check text-success"></i> Product name: 3-120 characters</li>
                    <li><i class="fas fa-check text-success"></i> SKU must be unique</li>
                    <li><i class="fas fa-check text-success"></i> Price must be ≥ 0</li>
                    <li><i class="fas fa-check text-success"></i> Stock quantity must be ≥ 0</li>
                </ul>
                
                <hr>
                
                <h6>Product Details</h6>
                <ul class="list-unstyled small">
                    <li><i class="fas fa-check text-success"></i> Description: Complete product info</li>
                    <li><i class="fas fa-check text-success"></i> Brand: Manufacturer name</li>
                    <li><i class="fas fa-check text-success"></i> Dates: Manufacturing & Expiry</li>
                    <li><i class="fas fa-check text-success"></i> Warranty: In months</li>
                    <li><i class="fas fa-check text-success"></i> Guarantee: In months</li>
                    <li><i class="fas fa-check text-success"></i> Tax: Percentage (0-100)</li>
                    <li><i class="fas fa-check text-success"></i> Discount: Percentage (0-100)</li>
                </ul>
                
                <hr>
                
                <h6>Tips</h6>
                <ul class="list-unstyled small">
                    <li><i class="fas fa-lightbulb text-warning"></i> Image is optional</li>
                    <li><i class="fas fa-lightbulb text-warning"></i> Expiry date is optional</li>
                    <li><i class="fas fa-lightbulb text-warning"></i> All dates are optional</li>
                    <li><i class="fas fa-lightbulb text-warning"></i> Tax & discount default to 0%</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection