@extends('layouts.app')

@section('title', 'Categories - Inventory & Sales')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-tags"></i> Product Categories</h2>
            <a href="{{ route('categories.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Category
            </a>
        </div>
    </div>
</div>

<!-- Categories Grid -->
<div class="row">
    @forelse($categories as $category)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 {{ !$category->is_active ? 'border-secondary' : '' }}">
            <div class="card-header d-flex justify-content-between align-items-center" 
                 style="background-color: {{ $category->color }}; color: white;">
                <div>
                    <i class="{{ $category->icon }}"></i>
                    <strong>{{ $category->name }}</strong>
                </div>
                <div>
                    @if($category->is_active)
                        <span class="badge bg-light text-dark">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </div>
            </div>
            
            <div class="card-body d-flex flex-column">
                @if($category->description)
                <p class="card-text text-muted">{{ $category->description }}</p>
                @endif
                
                <div class="row text-center mb-3">
                    <div class="col-6">
                        <h4 class="text-primary">{{ $category->products_count }}</h4>
                        <small>Products</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-info">{{ $category->sort_order }}</h4>
                        <small>Sort Order</small>
                    </div>
                </div>
                
                <div class="mt-auto">
                    <div class="btn-group w-100" role="group">
                        <a href="{{ route('categories.show', $category) }}" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('categories.toggle-status', $category) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-{{ $category->is_active ? 'warning' : 'success' }} btn-sm">
                                <i class="fas fa-{{ $category->is_active ? 'pause' : 'play' }}"></i>
                                {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </div>
                    
                    @if($category->products_count == 0)
                    <form action="{{ route('categories.destroy', $category) }}" method="POST" 
                          class="mt-2" onsubmit="return confirm('Are you sure you want to delete this category?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info text-center">
            <i class="fas fa-tags fa-3x mb-3"></i>
            <h5>No categories found</h5>
            <p>Create your first product category to organize your inventory.</p>
            <a href="{{ route('categories.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create First Category
            </a>
        </div>
    </div>
    @endforelse
</div>

@if($categories->count() > 0)
<!-- Summary -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5><i class="fas fa-chart-bar"></i> Category Summary</h5>
                <div class="row text-center">
                    <div class="col-md-3">
                        <h4 class="text-primary">{{ $categories->count() }}</h4>
                        <small>Total Categories</small>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-success">{{ $categories->where('is_active', true)->count() }}</h4>
                        <small>Active Categories</small>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-info">{{ $categories->sum('products_count') }}</h4>
                        <small>Total Products</small>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-warning">{{ $categories->where('products_count', 0)->count() }}</h4>
                        <small>Empty Categories</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection