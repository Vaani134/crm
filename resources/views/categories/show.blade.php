@extends('layouts.app')

@section('title', $category->name . ' - Category Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="{{ $category->icon }}" style="color: {{ $category->color }};"></i> 
                {{ $category->name }}
                @if(!$category->is_active)
                    <span class="badge bg-secondary ms-2">Inactive</span>
                @endif
            </h2>
            <div>
                <a href="{{ route('categories.edit', $category) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Category
                </a>
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Categories
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Category Info -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header" style="background-color: {{ $category->color }}; color: white;">
                <h5><i class="{{ $category->icon }}"></i> Category Information</h5>
            </div>
            <div class="card-body">
                @if($category->description)
                <p class="lead">{{ $category->description }}</p>
                @else
                <p class="text-muted">No description provided for this category.</p>
                @endif
                
                <div class="row">
                    <div class="col-md-6">
                        <strong>Status:</strong> 
                        <span class="badge bg-{{ $category->is_active ? 'success' : 'secondary' }}">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="col-md-6">
                        <strong>Sort Order:</strong> {{ $category->sort_order }}
                    </div>
                </div>
                
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Created:</strong> {{ $category->created_at->format('M d, Y') }}
                    </div>
                    <div class="col-md-6">
                        <strong>Last Updated:</strong> {{ $category->updated_at->format('M d, Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-bar"></i> Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <h3 class="text-primary">{{ $stats['total_products'] }}</h3>
                        <small>Total Products</small>
                    </div>
                    <div class="col-6 mb-3">
                        <h3 class="text-warning">{{ $stats['low_stock_products'] }}</h3>
                        <small>Low Stock</small>
                    </div>
                    <div class="col-6">
                        <h3 class="text-success">${{ number_format($stats['total_stock_value'], 2) }}</h3>
                        <small>Stock Value</small>
                    </div>
                    <div class="col-6">
                        <h3 class="text-danger">{{ $stats['out_of_stock'] }}</h3>
                        <small>Out of Stock</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Products in this Category -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-box"></i> Products in this Category ({{ $category->products->count() }})</h5>
                <a href="{{ route('products.create') }}?category={{ $category->id }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Add Product
                </a>
            </div>
            <div class="card-body">
                @if($category->products->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Barcode</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($category->products as $product)
                            <tr>
                                <td>
                                    @if($product->image_path)
                                        <img src="{{ asset('storage/' . $product->image_path) }}" 
                                             alt="{{ $product->name }}" class="img-thumbnail" style="width: 50px; height: 50px;">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px; border-radius: 4px;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $product->name }}</strong>
                                </td>
                                <td>
                                    <code>{{ $product->barcode_number }}</code>
                                </td>
                                <td>
                                    <strong>${{ number_format($product->price, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $product->stock_qty <= 5 ? 'danger' : ($product->stock_qty <= 10 ? 'warning' : 'success') }}">
                                        {{ $product->stock_qty }}
                                    </span>
                                </td>
                                <td>
                                    @if($product->stock_qty > 0)
                                        <span class="badge bg-success">In Stock</span>
                                    @else
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($product->stock_qty <= 5)
                                        <a href="{{ route('products.refill') }}#product-{{ $product->id }}" class="btn btn-outline-warning">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h5>No products in this category</h5>
                    <p class="text-muted">Add your first product to this category to get started.</p>
                    <a href="{{ route('products.create') }}?category={{ $category->id }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add First Product
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection