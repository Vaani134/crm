@extends('layouts.app')

@section('title', 'Audit Logs - Inventory & Sales')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-clipboard-list"></i> Audit Logs</h2>
        </div>
        
        @if(!$user->isAdmin())
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> You can only view your own activity logs.
        </div>
        @endif
    </div>
</div>

<!-- System Activity Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-shield-alt"></i> System Activity</h5>
                    @if($user->isAdmin())
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-download"></i> Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('audit.export', array_merge(request()->query(), ['type' => 'system', 'format' => 'csv'])) }}">
                                <i class="fas fa-file-csv"></i> CSV
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('audit.export', array_merge(request()->query(), ['type' => 'system', 'format' => 'json'])) }}">
                                <i class="fas fa-file-code"></i> JSON
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('audit.export', array_merge(request()->query(), ['type' => 'system', 'format' => 'pdf'])) }}">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a></li>
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <!-- System Filters -->
                <form method="GET" action="{{ route('audit.index') }}" class="row g-3 mb-4">
                    @if($user->isAdmin())
                    <div class="col-md-3">
                        <label for="system_user_id" class="form-label">User</label>
                        <select name="system_user_id" class="form-select">
                            <option value="">All Users</option>
                            @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('system_user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->full_name }} ({{ $u->username }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    
                    <div class="col-md-2">
                        <label for="system_module" class="form-label">Module</label>
                        <select name="system_module" class="form-select">
                            <option value="">All Modules</option>
                            @foreach($systemModules as $module)
                            <option value="{{ $module }}" {{ request('system_module') === $module ? 'selected' : '' }}>
                                {{ ucfirst($module) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="system_action" class="form-label">Action</label>
                        <select name="system_action" class="form-select">
                            <option value="">All Actions</option>
                            @foreach($systemActions as $action)
                            <option value="{{ $action }}" {{ request('system_action') === $action ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $action)) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="system_days" class="form-label">Time Period</label>
                        <select name="system_days" class="form-select">
                            <option value="7" {{ request('system_days', 30) == 7 ? 'selected' : '' }}>Last 7 days</option>
                            <option value="30" {{ request('system_days', 30) == 30 ? 'selected' : '' }}>Last 30 days</option>
                            <option value="90" {{ request('system_days', 30) == 90 ? 'selected' : '' }}>Last 90 days</option>
                            <option value="365" {{ request('system_days', 30) == 365 ? 'selected' : '' }}>Last year</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('audit.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>

                @if($systemLogs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Date/Time</th>
                                @if($user->isAdmin())
                                <th>User</th>
                                @endif
                                <th>Action</th>
                                <th>Module</th>
                                <th>Description</th>
                                <th>IP Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($systemLogs as $log)
                            <tr>
                                <td>
                                    <strong>{{ $log->created_at->format('M d, Y') }}</strong><br>
                                    <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                </td>
                                @if($user->isAdmin())
                                <td>
                                    <strong>{{ $log->admin->full_name }}</strong><br>
                                    <small class="text-muted">{{ $log->admin->username }}</small>
                                </td>
                                @endif
                                <td>
                                    <span class="badge bg-{{ $log->badge_color }}">
                                        {{ $log->formatted_action }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ ucfirst($log->module) }}
                                    </span>
                                </td>
                                <td>
                                    <div style="max-width: 300px;">
                                        {{ Str::limit($log->description, 80) }}
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $log->ip_address }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('audit.show', $log) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- System Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $systemLogs->appends(request()->query())->links() }}
                </div>
                
                <!-- System Summary -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6>System Activity Summary</h6>
                                <p class="mb-1"><strong>Total Records:</strong> {{ $systemLogs->total() }}</p>
                                <p class="mb-1"><strong>Showing:</strong> {{ $systemLogs->firstItem() ?? 0 }} - {{ $systemLogs->lastItem() ?? 0 }}</p>
                                <p class="mb-0"><strong>Time Range:</strong> Last {{ request('system_days', 30) }} days</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                @else
                <div class="text-center py-5">
                    <i class="fas fa-shield-alt fa-3x text-muted mb-3"></i>
                    <h5>No system activity logs found</h5>
                    <p class="text-muted">
                        No system activity logs match your current filters.
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Business Activity Card -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-chart-line"></i> Business Activity</h5>
                    @if($user->isAdmin())
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-download"></i> Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('audit.export', array_merge(request()->query(), ['type' => 'business', 'format' => 'csv'])) }}">
                                <i class="fas fa-file-csv"></i> CSV
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('audit.export', array_merge(request()->query(), ['type' => 'business', 'format' => 'json'])) }}">
                                <i class="fas fa-file-code"></i> JSON
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('audit.export', array_merge(request()->query(), ['type' => 'business', 'format' => 'pdf'])) }}">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a></li>
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <!-- Business Filters -->
                <form method="GET" action="{{ route('audit.index') }}" class="row g-3 mb-4">
                    @if($user->isAdmin())
                    <div class="col-md-3">
                        <label for="business_user_id" class="form-label">User</label>
                        <select name="business_user_id" class="form-select">
                            <option value="">All Users</option>
                            @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('business_user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->full_name }} ({{ $u->username }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    
                    <div class="col-md-2">
                        <label for="business_module" class="form-label">Module</label>
                        <select name="business_module" class="form-select">
                            <option value="">All Modules</option>
                            @foreach($businessModules as $module)
                            <option value="{{ $module }}" {{ request('business_module') === $module ? 'selected' : '' }}>
                                {{ ucfirst($module) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="business_action" class="form-label">Action</label>
                        <select name="business_action" class="form-select">
                            <option value="">All Actions</option>
                            @foreach($businessActions as $action)
                            <option value="{{ $action }}" {{ request('business_action') === $action ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $action)) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="business_days" class="form-label">Time Period</label>
                        <select name="business_days" class="form-select">
                            <option value="7" {{ request('business_days', 30) == 7 ? 'selected' : '' }}>Last 7 days</option>
                            <option value="30" {{ request('business_days', 30) == 30 ? 'selected' : '' }}>Last 30 days</option>
                            <option value="90" {{ request('business_days', 30) == 90 ? 'selected' : '' }}>Last 90 days</option>
                            <option value="365" {{ request('business_days', 30) == 365 ? 'selected' : '' }}>Last year</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('audit.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>

                @if($businessLogs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Date/Time</th>
                                @if($user->isAdmin())
                                <th>User</th>
                                @endif
                                <th>Action</th>
                                <th>Module</th>
                                <th>Description</th>
                                <th>IP Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($businessLogs as $log)
                            <tr>
                                <td>
                                    <strong>{{ $log->created_at->format('M d, Y') }}</strong><br>
                                    <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                </td>
                                @if($user->isAdmin())
                                <td>
                                    <strong>{{ $log->admin->full_name }}</strong><br>
                                    <small class="text-muted">{{ $log->admin->username }}</small>
                                </td>
                                @endif
                                <td>
                                    <span class="badge bg-{{ $log->badge_color }}">
                                        {{ $log->formatted_action }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ ucfirst($log->module) }}
                                    </span>
                                </td>
                                <td>
                                    <div style="max-width: 300px;">
                                        {{ Str::limit($log->description, 80) }}
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $log->ip_address }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('audit.show', $log) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Business Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $businessLogs->appends(request()->query())->links() }}
                </div>
                
                <!-- Business Summary -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6>Business Activity Summary</h6>
                                <p class="mb-1"><strong>Total Records:</strong> {{ $businessLogs->total() }}</p>
                                <p class="mb-1"><strong>Showing:</strong> {{ $businessLogs->firstItem() ?? 0 }} - {{ $businessLogs->lastItem() ?? 0 }}</p>
                                <p class="mb-0"><strong>Time Range:</strong> Last {{ request('business_days', 30) }} days</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                @else
                <div class="text-center py-5">
                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                    <h5>No business activity logs found</h5>
                    <p class="text-muted">
                        No business activity logs match your current filters.
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection