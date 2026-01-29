@extends('layouts.app')

@section('title', 'Dashboard - Inventory & Sales')

@section('content')
<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
        <p class="text-muted">Welcome back, {{ auth('admin')->user()->full_name }}!</p>
    </div>
</div>

<div class="row mb-4">
    <!-- Today's Sales -->
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Today's Sales</h6>
                        <h4>${{ number_format($todaySales->total, 2) }}</h4>
                        <small>{{ $todaySales->count }} transactions</small>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calendar-day fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- This Week's Sales -->
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">This Week</h6>
                        <h4>${{ number_format($weekSales->total, 2) }}</h4>
                        <small>{{ $weekSales->count }} transactions</small>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calendar-week fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- This Month's Sales -->
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">This Month</h6>
                        <h4>${{ number_format($monthSales->total, 2) }}</h4>
                        <small>{{ $monthSales->count }} transactions</small>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calendar-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Yearly Sales -->
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">This Year</h6>
                        <h4>${{ number_format($yearSales->total, 2) }}</h4>
                        <small>{{ $yearSales->count }} transactions</small>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calendar fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <!--<div class="card-header"> 
                <h6><i class="fas fa-bolt"></i> Quick Actions</h6> 
            </div> -->
            <div class="card-body py-2">
                <div class="row">
                    <div class="col-md-2 col-6 mb-2">
                        <a href="{{ route('sales.register') }}" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-cash-register"></i> New Sale
                        </a>
                    </div>
                    <div class="col-md-2 col-6 mb-2">
                        <a href="{{ route('products.create') }}" class="btn btn-success btn-sm w-100">
                            <i class="fas fa-plus"></i> Add Product
                        </a>
                    </div>
                    <div class="col-md-2 col-6 mb-2">
                        <a href="{{ route('products.index') }}" class="btn btn-info btn-sm w-100">
                            <i class="fas fa-box"></i> View Products
                        </a>
                    </div>
                    <div class="col-md-2 col-6 mb-2">
                        <a href="{{ route('sales.history') }}" class="btn btn-secondary btn-sm w-100">
                            <i class="fas fa-history"></i> Sales History
                        </a>
                    </div>
                    @if($totalCategories > 0)
                    <div class="col-md-2 col-6 mb-2">
                        <a href="{{ route('categories.index') }}" class="btn btn-warning btn-sm w-100">
                            <i class="fas fa-tags"></i> Categories
                        </a>
                    </div>
                    @else
                    <div class="col-md-2 col-6 mb-2">
                        <a href="/setup-categories.php" class="btn btn-outline-warning btn-sm w-100" target="_blank">
                            <i class="fas fa-cog"></i> Setup Categories
                        </a>
                    </div>
                    @endif
                    <div class="col-md-2 col-6 mb-2">
                        <a href="{{ route('analysis.index') }}" class="btn btn-dark btn-sm w-100">
                            <i class="fas fa-chart-line"></i> Analysis
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row">
    <!-- Product Overview -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-boxes"></i> Product Overview</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-box fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h4 class="mb-0">{{ $totalProducts }}</h4>
                                <small class="text-muted">Total Products</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                            <div>
                                <h4 class="mb-0">{{ $inStock }}</h4>
                                <small class="text-muted">In Stock</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                            </div>
                            <div>
                                <h4 class="mb-0">{{ $lowStock }}</h4>
                                <small class="text-muted">Low Stock</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-times-circle fa-2x text-danger"></i>
                            </div>
                            <div>
                                <h4 class="mb-0">{{ $outOfStock }}</h4>
                                <small class="text-muted">Out of Stock</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if($totalCategories > 0)
                <hr>
                <div class="text-center">
                    <span class="badge bg-info">{{ $totalCategories }} Categories Available</span>
                </div>
                @endif
                
                @if($lowStock > 0)
                <div class="alert alert-warning mt-3 mb-0">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>{{ $lowStock }}</strong> products need restocking!
                    <a href="{{ route('products.index') }}?low_stock=1" class="alert-link">View them</a>
                </div>
                @endif
                
                @if($outOfStock > 0)
                <div class="alert alert-danger mt-2 mb-0">
                    <i class="fas fa-times-circle"></i>
                    <strong>{{ $outOfStock }}</strong> products are out of stock!
                    <a href="{{ route('products.refill') }}" class="alert-link">Restock now</a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Top Selling Products (This Month) -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-trophy"></i> Top Selling Products</h5>
                <small class="text-muted">This Month</small>
            </div>
            <div class="card-body">
                @if($topProducts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Sold</th>
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topProducts as $index => $product)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                            <div>
                                                <strong>{{ $product->name }}</strong>
                                                <br><small class="text-muted">{{ $product->barcode_number }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ $product->total_sold }}</span>
                                    </td>
                                    <td class="text-end">
                                        <strong>${{ number_format($product->total_revenue, 2) }}</strong>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="fas fa-chart-bar fa-2x text-muted mb-2"></i>
                        <p class="text-muted">No sales data for this month</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($totalCategories == 0)
<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fas fa-tags"></i>
            <strong>Categories not set up yet.</strong>
            Setting up categories will help you organize your products better and get detailed sales analytics.
            <a href="/setup-categories.php" class="alert-link" target="_blank">Set them up now</a>
        </div>
    </div>
</div>
@endif
@endsection