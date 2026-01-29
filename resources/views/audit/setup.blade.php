@extends('layouts.app')

@section('title', 'Setup Audit Logs - Inventory & Sales')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h4><i class="fas fa-exclamation-triangle"></i> Audit Logs Setup Required</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle"></i> Database Table Missing</h5>
                    <p>The audit logs feature requires a database table that hasn't been created yet. Click the button below to create it automatically.</p>
                </div>
                
                <div id="result" style="display: none;"></div>
                
                <div class="text-center">
                    <button id="createTable" class="btn btn-primary btn-lg">
                        <i class="fas fa-database"></i> Create Audit Logs Table
                    </button>
                </div>
                
                <div class="mt-4">
                    <h6>What this will create:</h6>
                    <ul>
                        <li><strong>audit_logs table</strong> - Stores all user activity logs</li>
                        <li><strong>Indexes</strong> - For optimal performance</li>
                        <li><strong>Foreign keys</strong> - Links to user accounts</li>
                        <li><strong>Migration record</strong> - Tracks database changes</li>
                    </ul>
                </div>
                
                <div class="mt-4">
                    <h6>What gets logged:</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <ul>
                                <li>User login/logout</li>
                                <li>Product creation/updates</li>
                                <li>Stock refills</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul>
                                <li>Sales transactions</li>
                                <li>User management</li>
                                <li>System activities</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('createTable').addEventListener('click', function() {
    const button = this;
    const result = document.getElementById('result');
    
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating table...';
    
    fetch('/setup-audit-table', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        result.style.display = 'block';
        
        if (data.success) {
            result.innerHTML = `
                <div class="alert alert-success">
                    <h5><i class="fas fa-check-circle"></i> Success!</h5>
                    <p>${data.message}</p>
                    <button onclick="window.location.reload()" class="btn btn-success">
                        <i class="fas fa-refresh"></i> Continue to Audit Logs
                    </button>
                </div>
            `;
        } else {
            result.innerHTML = `
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-circle"></i> Error</h5>
                    <p>${data.message}</p>
                    <p class="mt-2"><strong>Manual Solution:</strong></p>
                    <p>You can create the table manually by running this SQL in your database:</p>
                    <pre class="bg-light p-2 small">CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(255) NOT NULL,
    module VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(255) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);</pre>
                </div>
            `;
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-database"></i> Create Audit Logs Table';
        }
    })
    .catch(error => {
        result.style.display = 'block';
        result.innerHTML = `
            <div class="alert alert-danger">
                <h5><i class="fas fa-exclamation-circle"></i> Network Error</h5>
                <p>Failed to create table: ${error.message}</p>
            </div>
        `;
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-database"></i> Create Audit Logs Table';
    });
});
</script>
@endsection