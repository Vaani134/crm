@extends('layouts.app')

@section('title', 'Audit Log Details - Inventory & Sales')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-clipboard-list"></i> Audit Log Details</h2>
            <a href="{{ route('audit.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Logs
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-info-circle"></i> Log Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Date & Time:</strong></div>
                    <div class="col-sm-9">
                        {{ $auditLog->created_at->format('F d, Y \a\t H:i:s') }}
                        <small class="text-muted">({{ $auditLog->created_at->diffForHumans() }})</small>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>User:</strong></div>
                    <div class="col-sm-9">
                        {{ $auditLog->admin->full_name }} ({{ $auditLog->admin->username }})
                        <span class="badge bg-{{ $auditLog->admin->role === 'admin' ? 'danger' : 'primary' }} ms-2">
                            {{ ucfirst($auditLog->admin->role) }}
                        </span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Action:</strong></div>
                    <div class="col-sm-9">
                        <span class="badge bg-{{ $auditLog->badge_color }}">
                            {{ $auditLog->formatted_action }}
                        </span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Module:</strong></div>
                    <div class="col-sm-9">
                        <span class="badge bg-light text-dark">
                            {{ ucfirst($auditLog->module) }}
                        </span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Description:</strong></div>
                    <div class="col-sm-9">{{ $auditLog->description }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>IP Address:</strong></div>
                    <div class="col-sm-9">{{ $auditLog->ip_address ?? 'N/A' }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>User Agent:</strong></div>
                    <div class="col-sm-9">
                        <small class="text-muted">{{ $auditLog->user_agent ?? 'N/A' }}</small>
                    </div>
                </div>
            </div>
        </div>
        
        @if($auditLog->old_values || $auditLog->new_values)
        <div class="card mt-4">
            <div class="card-header">
                <h5><i class="fas fa-exchange-alt"></i> Data Changes</h5>
            </div>
            <div class="card-body">
                @if($auditLog->old_values)
                <div class="mb-4">
                    <h6 class="text-danger"><i class="fas fa-minus-circle"></i> Previous Values</h6>
                    <div class="bg-light p-3 rounded">
                        <pre class="mb-0"><code>{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</code></pre>
                    </div>
                </div>
                @endif
                
                @if($auditLog->new_values)
                <div class="mb-3">
                    <h6 class="text-success"><i class="fas fa-plus-circle"></i> New Values</h6>
                    <div class="bg-light p-3 rounded">
                        <pre class="mb-0"><code>{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</code></pre>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-bar"></i> Quick Stats</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>User's Total Actions Today:</strong>
                    <span class="badge bg-primary">
                        {{ $auditLog->admin->auditLogs()->whereDate('created_at', today())->count() }}
                    </span>
                </div>
                
                <div class="mb-3">
                    <strong>User's Total Actions This Week:</strong>
                    <span class="badge bg-info">
                        {{ $auditLog->admin->auditLogs()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count() }}
                    </span>
                </div>
                
                <div class="mb-3">
                    <strong>Similar Actions Today:</strong>
                    <span class="badge bg-secondary">
                        {{ App\Models\AuditLog::where('action', $auditLog->action)->whereDate('created_at', today())->count() }}
                    </span>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5><i class="fas fa-clock"></i> Recent Activity</h5>
            </div>
            <div class="card-body">
                @php
                    $recentLogs = $auditLog->admin->auditLogs()
                        ->where('id', '!=', $auditLog->id)
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
                @endphp
                
                @forelse($recentLogs as $recentLog)
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <div>
                        <small class="text-muted">{{ $recentLog->created_at->format('M d, H:i') }}</small><br>
                        <span class="badge bg-{{ $recentLog->badge_color }} badge-sm">
                            {{ $recentLog->formatted_action }}
                        </span>
                    </div>
                    <a href="{{ route('audit.show', $recentLog) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye"></i>
                    </a>
                </div>
                @empty
                <p class="text-muted">No other recent activity</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection