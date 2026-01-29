@extends('layouts.app')

@section('title', 'Manage Passkeys')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-key"></i> Manage Your Passkeys</h4>
                    <p class="mb-0 text-muted">Passkeys provide secure, passwordless authentication using your device's biometrics or PIN.</p>
                </div>
                <div class="card-body">
                    <!-- Add New Passkey Section -->
                    <div class="mb-4">
                        <h5>Add New Passkey</h5>
                        <p class="text-muted">Register a new passkey for this device or security key.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="device-name">Device Name</label>
                                    <input type="text" id="device-name" class="form-control" placeholder="e.g., My iPhone, Work Laptop" value="">
                                </div>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button type="button" id="register-passkey" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Register New Passkey
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Existing Passkeys -->
                    <div>
                        <h5>Your Passkeys</h5>
                        @if($webAuthnKeys->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Device Name</th>
                                            <th>Created</th>
                                            <th>Last Used</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="passkeys-table">
                                        @foreach($webAuthnKeys as $key)
                                        <tr data-key-id="{{ $key->id }}">
                                            <td>
                                                <span class="key-name">{{ $key->name }}</span>
                                                <input type="text" class="form-control key-name-input d-none" value="{{ $key->name }}">
                                            </td>
                                            <td>{{ $key->created_at->format('M j, Y g:i A') }}</td>
                                            <td>
                                                @if($key->last_used_at)
                                                    {{ $key->last_used_at->format('M j, Y g:i A') }}
                                                @else
                                                    <span class="text-muted">Never</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary edit-key-name" data-key-id="{{ $key->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success save-key-name d-none" data-key-id="{{ $key->id }}">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary cancel-edit d-none" data-key-id="{{ $key->id }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-key" data-key-id="{{ $key->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                You don't have any passkeys registered yet. Register your first passkey above to enable passwordless login.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-shield-alt"></i> About Passkeys</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>What are Passkeys?</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Passwordless authentication</li>
                                <li><i class="fas fa-check text-success"></i> Uses device biometrics or PIN</li>
                                <li><i class="fas fa-check text-success"></i> Phishing resistant</li>
                                <li><i class="fas fa-check text-success"></i> No passwords to remember</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Supported Methods</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-fingerprint text-primary"></i> Fingerprint</li>
                                <li><i class="fas fa-eye text-primary"></i> Face recognition</li>
                                <li><i class="fas fa-lock text-primary"></i> Device PIN</li>
                                <li><i class="fas fa-mobile-alt text-primary"></i> Mobile device</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<div id="message-container"></div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Comprehensive WebAuthn support check
    function checkWebAuthnSupport() {
        // Check 1: Basic API availability
        if (!window.PublicKeyCredential) {
            return { supported: false, reason: 'PublicKeyCredential API not available' };
        }
        
        // Check 2: Navigator credentials
        if (!navigator.credentials) {
            return { supported: false, reason: 'navigator.credentials not available' };
        }
        
        // Check 3: Required methods
        if (!navigator.credentials.create || !navigator.credentials.get) {
            return { supported: false, reason: 'credentials.create or credentials.get not available' };
        }
        
        // Check 4: Secure context
        if (!window.isSecureContext) {
            return { supported: false, reason: 'Not a secure context (requires HTTPS or localhost)' };
        }
        
        return { supported: true, reason: 'All checks passed' };
    }

    const webAuthnCheck = checkWebAuthnSupport();
    console.log('WebAuthn Support Check:', webAuthnCheck);

    // Show/hide WebAuthn features based on support
    if (!webAuthnCheck.supported) {
        document.getElementById('register-passkey').disabled = true;
        document.getElementById('register-passkey').innerHTML = '<i class="fas fa-exclamation-triangle"></i> WebAuthn Not Available';
        document.getElementById('register-passkey').classList.remove('btn-primary');
        document.getElementById('register-passkey').classList.add('btn-secondary');
        
        // Show specific error message
        let errorMessage = `WebAuthn is not available: ${webAuthnCheck.reason}. `;
        errorMessage += '<a href="/webauthn-browser-test" class="alert-link" target="_blank">Run browser test</a> or ';
        errorMessage += '<a href="/webauthn/info" class="alert-link">learn more</a>.';
        
        showMessage(errorMessage, 'error');
        return;
    }

    // WebAuthn is supported, proceed with registration functionality
    document.getElementById('register-passkey').addEventListener('click', async function() {
        const deviceName = document.getElementById('device-name').value.trim() || 'WebAuthn Key';
        
        try {
            // Final check before proceeding
            if (!navigator.credentials || !navigator.credentials.create) {
                throw new Error('WebAuthn credentials API not available');
            }

            // Get registration options
            const optionsResponse = await fetch('/webauthn/register/options', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            if (!optionsResponse.ok) {
                const errorText = await optionsResponse.text();
                throw new Error(`Failed to get registration options: ${optionsResponse.status} ${errorText}`);
            }
            
            const options = await optionsResponse.json();
            console.log('Registration options:', options);
            
            // Convert base64url strings to ArrayBuffers
            options.challenge = base64urlToArrayBuffer(options.challenge);
            options.user.id = base64urlToArrayBuffer(options.user.id);
            
            // Convert excludeCredentials IDs to ArrayBuffers
            if (options.excludeCredentials && Array.isArray(options.excludeCredentials)) {
                options.excludeCredentials = options.excludeCredentials.map(cred => ({
                    ...cred,
                    id: base64urlToArrayBuffer(cred.id)
                }));
            }
            
            console.log('Calling navigator.credentials.create...');
            
            // Create credential
            const credential = await navigator.credentials.create({
                publicKey: options
            });
            
            console.log('Credential created:', credential);
            
            // Convert ArrayBuffers to base64url for transmission
            const credentialData = {
                id: credential.id,
                rawId: arrayBufferToBase64url(credential.rawId),
                response: {
                    clientDataJSON: arrayBufferToBase64url(credential.response.clientDataJSON),
                    attestationObject: arrayBufferToBase64url(credential.response.attestationObject)
                },
                type: credential.type,
                device_name: deviceName
            };
            
            console.log('Sending credential to server...');
            
            // Send to server
            const registerResponse = await fetch('/webauthn/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(credentialData)
            });
            
            const result = await registerResponse.json();
            console.log('Server response:', result);
            
            if (result.success) {
                showMessage('Passkey registered successfully!', 'success');
                // Reload page to show new key
                setTimeout(() => location.reload(), 1500);
            } else {
                throw new Error(result.error || 'Registration failed');
            }
            
        } catch (error) {
            console.error('Registration error:', error);
            let errorMessage = 'Failed to register passkey: ' + error.message;
            
            // Provide more specific error messages
            if (error.name === 'NotSupportedError') {
                errorMessage = 'WebAuthn is not supported on this device or browser.';
            } else if (error.name === 'SecurityError') {
                errorMessage = 'Security error: This feature requires HTTPS or localhost.';
            } else if (error.name === 'NotAllowedError') {
                errorMessage = 'Registration was cancelled or not allowed by the user.';
            } else if (error.name === 'InvalidStateError') {
                errorMessage = 'This authenticator is already registered for this account.';
            } else if (error.message.includes('undefined')) {
                errorMessage = 'WebAuthn is not available in this browser context.';
            }
            
            showMessage(errorMessage, 'error');
        }
    });
    
    // Edit key name
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-key-name')) {
            const keyId = e.target.closest('.edit-key-name').dataset.keyId;
            const row = document.querySelector(`tr[data-key-id="${keyId}"]`);
            
            row.querySelector('.key-name').classList.add('d-none');
            row.querySelector('.key-name-input').classList.remove('d-none');
            row.querySelector('.edit-key-name').classList.add('d-none');
            row.querySelector('.save-key-name').classList.remove('d-none');
            row.querySelector('.cancel-edit').classList.remove('d-none');
        }
    });
    
    // Save key name
    document.addEventListener('click', async function(e) {
        if (e.target.closest('.save-key-name')) {
            const keyId = e.target.closest('.save-key-name').dataset.keyId;
            const row = document.querySelector(`tr[data-key-id="${keyId}"]`);
            const newName = row.querySelector('.key-name-input').value.trim();
            
            if (!newName) {
                showMessage('Device name cannot be empty', 'error');
                return;
            }
            
            try {
                const response = await fetch(`/webauthn/keys/${keyId}/name`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ name: newName })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    row.querySelector('.key-name').textContent = newName;
                    cancelEdit(row);
                    showMessage('Device name updated successfully!', 'success');
                } else {
                    throw new Error(result.error || 'Update failed');
                }
            } catch (error) {
                showMessage('Failed to update device name: ' + error.message, 'error');
            }
        }
    });
    
    // Cancel edit
    document.addEventListener('click', function(e) {
        if (e.target.closest('.cancel-edit')) {
            const keyId = e.target.closest('.cancel-edit').dataset.keyId;
            const row = document.querySelector(`tr[data-key-id="${keyId}"]`);
            cancelEdit(row);
        }
    });
    
    // Delete key
    document.addEventListener('click', async function(e) {
        if (e.target.closest('.delete-key')) {
            if (!confirm('Are you sure you want to delete this passkey? You will no longer be able to use it for login.')) {
                return;
            }
            
            const keyId = e.target.closest('.delete-key').dataset.keyId;
            
            try {
                const response = await fetch(`/webauthn/keys/${keyId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    document.querySelector(`tr[data-key-id="${keyId}"]`).remove();
                    showMessage('Passkey deleted successfully!', 'success');
                } else {
                    throw new Error(result.error || 'Delete failed');
                }
            } catch (error) {
                showMessage('Failed to delete passkey: ' + error.message, 'error');
            }
        }
    });
    
    function cancelEdit(row) {
        row.querySelector('.key-name').classList.remove('d-none');
        row.querySelector('.key-name-input').classList.add('d-none');
        row.querySelector('.edit-key-name').classList.remove('d-none');
        row.querySelector('.save-key-name').classList.add('d-none');
        row.querySelector('.cancel-edit').classList.add('d-none');
    }
    
    function showMessage(message, type) {
        const container = document.getElementById('message-container');
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        
        container.innerHTML = `
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
    
    // Utility functions for base64url conversion
    function base64urlToArrayBuffer(base64url) {
        const base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
        const padded = base64.padEnd(base64.length + (4 - base64.length % 4) % 4, '=');
        const binary = atob(padded);
        const buffer = new ArrayBuffer(binary.length);
        const view = new Uint8Array(buffer);
        for (let i = 0; i < binary.length; i++) {
            view[i] = binary.charCodeAt(i);
        }
        return buffer;
    }
    
    function arrayBufferToBase64url(buffer) {
        const bytes = new Uint8Array(buffer);
        let binary = '';
        for (let i = 0; i < bytes.byteLength; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
    }
});
</script>
@endsection