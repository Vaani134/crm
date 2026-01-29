@extends('layouts.app')

@section('title', 'Products - Inventory & Sales')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-box"></i> Products</h2>
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Product
            </a>
        </div>
    </div>
</div>

<!-- Search and Filter -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form id="searchForm" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="searchInput" name="search" 
                               placeholder="Search by product name or SKU..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="category_id" id="categorySelect" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="lowStockCheck" name="low_stock" value="1" 
                                   {{ request('low_stock') ? 'checked' : '' }}>
                            <label class="form-check-label">
                                Low stock only
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="button" id="searchBtn" class="btn btn-outline-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <button type="button" id="clearBtn" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Loading Indicator -->
<div id="loadingIndicator" class="row mb-4" style="display: none;">
    <div class="col-12">
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Searching products...</p>
        </div>
    </div>
</div>

<!-- Products Grid -->
<div class="row" id="productsContainer">
    @include('products.partials.products-grid', ['products' => $products])
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let searchTimeout;
    
    // AJAX search function
    function performSearch() {
        const formData = {
            search: $('#searchInput').val(),
            category_id: $('#categorySelect').val(),
            low_stock: $('#lowStockCheck').is(':checked') ? '1' : ''
        };
        
        // Show loading indicator
        $('#loadingIndicator').show();
        $('#productsContainer').hide();
        
        $.ajax({
            url: '{{ route("products.search.ajax") }}',
            method: 'GET',
            data: formData,
            success: function(response) {
                $('#productsContainer').html(response).show();
                $('#loadingIndicator').hide();
                
                // Update URL without page reload
                const url = new URL(window.location);
                Object.keys(formData).forEach(key => {
                    if (formData[key]) {
                        url.searchParams.set(key, formData[key]);
                    } else {
                        url.searchParams.delete(key);
                    }
                });
                window.history.pushState({}, '', url);
            },
            error: function(xhr, status, error) {
                console.error('Search error:', error);
                $('#loadingIndicator').hide();
                $('#productsContainer').show();
                
                // Show error message
                $('#productsContainer').html(`
                    <div class="col-12">
                        <div class="alert alert-danger text-center">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Search failed. Please try again.
                        </div>
                    </div>
                `);
            }
        });
    }
    
    // Real-time search on input (with debounce)
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            performSearch();
        }, 500); // 500ms delay
    });
    
    // Instant search on category change
    $('#categorySelect').on('change', function() {
        clearTimeout(searchTimeout);
        performSearch();
    });
    
    // Instant search on low stock checkbox change
    $('#lowStockCheck').on('change', function() {
        clearTimeout(searchTimeout);
        performSearch();
    });
    
    // Manual search button
    $('#searchBtn').on('click', function(e) {
        e.preventDefault();
        clearTimeout(searchTimeout);
        performSearch();
    });
    
    // Clear search
    $('#clearBtn').on('click', function(e) {
        e.preventDefault();
        $('#searchInput').val('');
        $('#categorySelect').val('');
        $('#lowStockCheck').prop('checked', false);
        
        // Clear URL parameters
        const url = new URL(window.location);
        url.search = '';
        window.history.pushState({}, '', url);
        
        performSearch();
    });
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', function(event) {
        const urlParams = new URLSearchParams(window.location.search);
        $('#searchInput').val(urlParams.get('search') || '');
        $('#categorySelect').val(urlParams.get('category_id') || '');
        $('#lowStockCheck').prop('checked', urlParams.get('low_stock') === '1');
        performSearch();
    });
});
</script>
@endsection