@extends('layouts.app')

@section('title', 'Edit Product - Inventory & Sales')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-edit"></i> Edit Product</h2>
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
                <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    @if($categories->count() > 0)
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category *</label>
                        <select class="form-select @error('category_id') is-invalid @enderror" 
                                id="category_id" name="category_id" required>
                            <option value="">Select a category...</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" 
                                    {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @else
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Categories Not Set Up</h6>
                        <p>Product categories are not configured yet.</p>
                        <a href="/setup-categories.php" class="btn btn-sm btn-warning" target="_blank">
                            <i class="fas fa-cog"></i> Set Up Categories
                        </a>
                    </div>
                    @endif
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Product Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $product->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="barcode_number" class="form-label">SKU/Barcode *</label>
                            <input type="text" class="form-control @error('barcode_number') is-invalid @enderror" 
                                   id="barcode_number" name="barcode_number" 
                                   value="{{ old('barcode_number', $product->barcode_number) }}" required>
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
                                   id="price" name="price" value="{{ old('price', $product->price) }}" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="stock_qty" class="form-label">Stock Quantity *</label>
                            <input type="number" min="0" 
                                   class="form-control @error('stock_qty') is-invalid @enderror" 
                                   id="stock_qty" name="stock_qty" 
                                   value="{{ old('stock_qty', $product->stock_qty) }}" required>
                            @error('stock_qty')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Product Image</label>
                        @if($product->image_path)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $product->image_path) }}" 
                                 class="img-thumbnail" style="max-width: 200px;" alt="Current image">
                            <div class="form-text">Current image</div>
                        </div>
                        @endif
                        <input type="file" class="form-control @error('image') is-invalid @enderror" 
                               id="image" name="image" accept="image/*">
                        <div class="form-text">
                            Supported formats: JPG, PNG, GIF (Max: 2MB)
                            @if($product->image_path) - Leave empty to keep current image @endif
                        </div>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-info-circle"></i> Product Info</h5>
            </div>
            <div class="card-body">
                <p><strong>Created:</strong> {{ $product->created_at->format('M d, Y H:i') }}</p>
                <p><strong>Last Updated:</strong> {{ $product->updated_at->format('M d, Y H:i') }}</p>
                @if($product->isLowStock())
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Low Stock Alert!
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection