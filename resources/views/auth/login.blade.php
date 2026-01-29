<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            position: relative;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #333;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .logo p {
            color: #666;
            font-size: 1.1em;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
            color: white;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(111, 66, 193, 0.3);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .loading {
            display: none;
        }

        .loading.show {
            display: inline-flex;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e1e5e9;
        }

        .divider span {
            background: white;
            padding: 0 15px;
            color: #666;
            font-size: 14px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }
            
            .logo h1 {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>🔐</h1>
            <h1>CRM Login</h1>
            <p>Secure Inventory Management</p>
        </div>

        <div id="error-message" class="error-message" style="display: none;"></div>
        <div id="success-message" class="success-message" style="display: none;"></div>

        @if ($errors->any())
            <div class="error-message">
                {{ $errors->first() }}
            </div>
        @endif

        <form id="login-form" method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-primary" id="password-login">
                <span class="loading" id="password-loading">
                    <span class="spinner"></span>
                </span>
                <span id="password-text">🔑 Sign In with Password</span>
            </button>
        </form>

        <div class="divider">
            <span>or</span>
        </div>

        <button type="button" class="btn btn-secondary" id="webauthn-login">
            <span class="loading" id="webauthn-loading">
                <span class="spinner"></span>
            </span>
            <span id="webauthn-text">🔐 Sign In with Passkey</span>
        </button>
    </div>

    <script>
        // Check WebAuthn support
        const webAuthnSupported = window.PublicKeyCredential && 
                                 typeof window.PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable === 'function' &&
                                 window.navigator && window.navigator.credentials;

        // Check if we're in a secure context
        const isSecureContext = window.isSecureContext || window.location.protocol === 'https:' || window.location.hostname === 'localhost';

        if (!webAuthnSupported || !isSecureContext) {
            document.getElementById('webauthn-login').style.display = 'none';
            document.querySelector('.divider').style.display = 'none';
            
            // Show a subtle message about WebAuthn availability
            if (!isSecureContext) {
                console.log('WebAuthn requires HTTPS or localhost for security reasons');
            }
        }

        // Password form submission
        document.getElementById('login-form').addEventListener('submit', function(e) {
            setPasswordLoading(true);
        });

        // WebAuthn login
        document.getElementById('webauthn-login').addEventListener('click', async function() {
            const username = document.getElementById('username').value.trim();
            
            if (!username) {
                showError('Please enter your username first');
                return;
            }

            // Double-check WebAuthn support
            if (!navigator.credentials || !navigator.credentials.get) {
                showError('WebAuthn is not supported in this browser or context');
                return;
            }
            
            setWebAuthnLoading(true);
            
            try {
                // Get authentication options
                const optionsResponse = await fetch('/webauthn/login/options', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ username: username })
                });
                
                if (!optionsResponse.ok) {
                    throw new Error('Failed to get authentication options');
                }
                
                const options = await optionsResponse.json();
                
                // Convert base64url strings to ArrayBuffers
                options.challenge = base64urlToArrayBuffer(options.challenge);
                
                if (options.allowCredentials) {
                    options.allowCredentials = options.allowCredentials.map(cred => ({
                        ...cred,
                        id: base64urlToArrayBuffer(cred.id)
                    }));
                }
                
                // Get credential
                const assertion = await navigator.credentials.get({
                    publicKey: options
                });
                
                // Convert ArrayBuffers to base64url for transmission
                const assertionData = {
                    id: assertion.id,
                    rawId: arrayBufferToBase64url(assertion.rawId),
                    response: {
                        clientDataJSON: arrayBufferToBase64url(assertion.response.clientDataJSON),
                        authenticatorData: arrayBufferToBase64url(assertion.response.authenticatorData),
                        signature: arrayBufferToBase64url(assertion.response.signature),
                        userHandle: assertion.response.userHandle ? arrayBufferToBase64url(assertion.response.userHandle) : null
                    },
                    type: assertion.type
                };
                
                // Send to server
                const loginResponse = await fetch('/webauthn/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(assertionData)
                });
                
                const result = await loginResponse.json();
                
                if (result.success) {
                    showSuccess('Authentication successful! Redirecting...');
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1000);
                } else {
                    throw new Error(result.error || 'Authentication failed');
                }
                
            } catch (error) {
                console.error('WebAuthn login error:', error);
                let errorMessage = 'Passkey authentication failed: ' + error.message;
                
                // Provide more specific error messages
                if (error.name === 'NotSupportedError') {
                    errorMessage = 'WebAuthn is not supported on this device or browser.';
                } else if (error.name === 'SecurityError') {
                    errorMessage = 'Security error: This feature requires HTTPS or localhost.';
                } else if (error.name === 'NotAllowedError') {
                    errorMessage = 'Authentication was cancelled or not allowed.';
                } else if (error.message.includes('undefined')) {
                    errorMessage = 'WebAuthn is not available in this browser context.';
                }
                
                showError(errorMessage);
            } finally {
                setWebAuthnLoading(false);
            }
        });

        // Utility functions
        function setPasswordLoading(loading) {
            const loadingEl = document.getElementById('password-loading');
            const textEl = document.getElementById('password-text');
            
            if (loading) {
                loadingEl.classList.add('show');
                textEl.style.display = 'none';
                document.getElementById('password-login').disabled = true;
            } else {
                loadingEl.classList.remove('show');
                textEl.style.display = 'inline';
                document.getElementById('password-login').disabled = false;
            }
        }

        function setWebAuthnLoading(loading) {
            const loadingEl = document.getElementById('webauthn-loading');
            const textEl = document.getElementById('webauthn-text');
            
            if (loading) {
                loadingEl.classList.add('show');
                textEl.style.display = 'none';
                document.getElementById('webauthn-login').disabled = true;
            } else {
                loadingEl.classList.remove('show');
                textEl.style.display = 'inline';
                document.getElementById('webauthn-login').disabled = false;
            }
        }

        function showError(message) {
            const errorMessage = document.getElementById('error-message');
            const successMessage = document.getElementById('success-message');
            errorMessage.textContent = message;
            errorMessage.style.display = 'block';
            successMessage.style.display = 'none';
        }

        function showSuccess(message) {
            const errorMessage = document.getElementById('error-message');
            const successMessage = document.getElementById('success-message');
            successMessage.textContent = message;
            successMessage.style.display = 'block';
            errorMessage.style.display = 'none';
        }

        function hideMessages() {
            document.getElementById('error-message').style.display = 'none';
            document.getElementById('success-message').style.display = 'none';
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
    </script>
</body>
</html>