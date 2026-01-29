@extends('layouts.app')

@section('title', 'Manage Users - Inventory & Sales')

@section('content')
<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-users"></i> Manage Users</h2>
        <p class="text-muted">Admin Only - Create and manage system users</p>
    </div>
</div>

<div class="row">
    <!-- Add User Form -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>
                    <i class="fas fa-{{ isset($admin) ? 'user-edit' : 'user-plus' }}"></i> 
                    {{ isset($admin) ? 'Edit User' : 'Add New User' }}
                </h5>
                @if(isset($admin))
                    <div class="mt-2">
                        <a href="{{ route('admin.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-times"></i> Cancel Edit
                        </a>
                    </div>
                @endif
            </div>
            <div class="card-body">
                <form action="{{ isset($admin) ? route('admin.update', $admin) : route('admin.store') }}" method="POST">
                    @csrf
                    @if(isset($admin))
                        @method('PUT')
                    @endif
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username *</label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" 
                               id="username" name="username" value="{{ old('username', isset($admin) ? $admin->username : '') }}" required>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password {{ isset($admin) ? '' : '*' }}</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" {{ isset($admin) ? '' : 'required' }}>
                        <div class="form-text">
                            {{ isset($admin) ? 'Leave blank to keep current password' : 'Minimum 6 characters' }}
                        </div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                               id="full_name" name="full_name" value="{{ old('full_name', isset($admin) ? $admin->full_name : '') }}" required>
                        @error('full_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email', isset($admin) ? $admin->email : '') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role *</label>
                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                            <option value="">Select role...</option>
                            <option value="admin" {{ old('role', isset($admin) ? $admin->role : '') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="employee" {{ old('role', isset($admin) ? $admin->role : '') === 'employee' ? 'selected' : '' }}>Employee</option>
                        </select>
                        <div class="form-text">
                            <strong>Admin:</strong> Full access including user management<br>
                            <strong>Employee:</strong> Sales and inventory access only
                        </div>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-{{ isset($admin) ? 'save' : 'user-plus' }}"></i> 
                        {{ isset($admin) ? 'Update User' : 'Create User' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Users List -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-list"></i> System Users</h5>
            </div>
            <div class="card-body">
                @if($admins->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($admins as $admin)
                            <tr>
                                <td>
                                    <strong>{{ $admin->full_name }}</strong><br>
                                    <small class="text-muted">
                                        {{ $admin->username }} | {{ $admin->email }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge {{ $admin->role === 'admin' ? 'bg-danger' : 'bg-primary' }}">
                                        {{ ucfirst($admin->role) }}
                                    </span>
                                </td>
                                <td>
                                    @if($admin->id !== auth('admin')->id())
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.edit', $admin) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.destroy', $admin) }}" method="POST" 
                                              class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                    @else
                                    <span class="text-muted small">Current User</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-users fa-2x text-muted mb-2"></i>
                    <p class="text-muted">No users found</p>
                </div>
                @endif
            </div>
        </div>
        
        <!-- User Statistics -->
        <div class="card mt-3">
            <div class="card-body">
                <h6><i class="fas fa-chart-bar"></i> User Statistics</h6>
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-danger">{{ $admins->where('role', 'admin')->count() }}</h4>
                        <small>Admins</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-primary">{{ $admins->where('role', 'employee')->count() }}</h4>
                        <small>Employees</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection