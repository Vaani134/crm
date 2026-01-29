<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\WebAuthnKey;
use Illuminate\Support\Str;

class WebAuthnService
{
    /**
     * Generate registration options for WebAuthn.
     */
    public function generateRegistrationOptions(Admin $user): array
    {
        $challenge = $this->generateChallenge();
        
        // Store challenge in session for verification
        session(['webauthn_challenge' => $challenge]);
        
        // Get the current domain from the request
        $rpId = $this->getRelyingPartyId();
        
        return [
            'challenge' => base64url_encode($challenge),
            'rp' => [
                'name' => config('webauthn.relying_party.name'),
                'id' => $rpId,
            ],
            'user' => [
                'id' => base64url_encode($user->getWebAuthnUserId()),
                'name' => $user->getWebAuthnUserName(),
                'displayName' => $user->getWebAuthnDisplayName(),
            ],
            'pubKeyCredParams' => config('webauthn.public_key_credential_parameters'),
            'timeout' => config('webauthn.timeout'),
            'attestation' => config('webauthn.attestation_conveyance'),
            'authenticatorSelection' => [
                'authenticatorAttachment' => 'platform',
                'userVerification' => config('webauthn.user_verification'),
                'requireResidentKey' => false,
            ],
            'excludeCredentials' => $this->getExistingCredentials($user),
        ];
    }

    /**
     * Generate authentication options for WebAuthn.
     */
    public function generateAuthenticationOptions(?Admin $user = null): array
    {
        $challenge = $this->generateChallenge();
        
        // Store challenge in session for verification
        session(['webauthn_challenge' => $challenge]);
        
        // Get the current domain from the request
        $rpId = $this->getRelyingPartyId();
        
        $options = [
            'challenge' => base64url_encode($challenge),
            'timeout' => config('webauthn.timeout'),
            'userVerification' => config('webauthn.user_verification'),
            'rpId' => $rpId,
        ];

        if ($user) {
            $options['allowCredentials'] = $this->getExistingCredentials($user);
        }

        return $options;
    }

    /**
     * Verify and store a new WebAuthn credential.
     */
    public function verifyAndStoreCredential(Admin $user, array $credential, ?string $deviceName = null): WebAuthnKey
    {
        // Basic verification (in a real implementation, you'd use a proper WebAuthn library)
        $credentialId = base64url_decode($credential['id']);
        $publicKey = $this->extractPublicKey($credential);
        
        // Verify the challenge
        $storedChallenge = session('webauthn_challenge');
        if (!$storedChallenge) {
            throw new \Exception('No challenge found in session');
        }
        
        // Clear the challenge
        session()->forget('webauthn_challenge');
        
        // Store the credential
        return WebAuthnKey::create([
            'authenticatable_type' => get_class($user),
            'authenticatable_id' => $user->id,
            'name' => $deviceName ?: 'WebAuthn Key',
            'credential_id' => base64url_encode($credentialId),
            'public_key' => $publicKey,
            'aaguid' => $credential['response']['aaguid'] ?? null,
            'counter' => 0,
        ]);
    }

    /**
     * Verify a WebAuthn authentication assertion.
     */
    public function verifyAssertion(array $assertion): ?Admin
    {
        $credentialId = $assertion['id'];
        
        // Find the credential
        $webAuthnKey = WebAuthnKey::where('credential_id', $credentialId)->first();
        
        if (!$webAuthnKey) {
            return null;
        }
        
        // Verify the challenge
        $storedChallenge = session('webauthn_challenge');
        if (!$storedChallenge) {
            throw new \Exception('No challenge found in session');
        }
        
        // Clear the challenge
        session()->forget('webauthn_challenge');
        
        // Update counter and last used
        $webAuthnKey->updateCounter($webAuthnKey->counter + 1);
        
        return $webAuthnKey->authenticatable;
    }

    /**
     * Generate a cryptographic challenge.
     */
    private function generateChallenge(): string
    {
        return random_bytes(config('webauthn.challenge_length', 32));
    }

    /**
     * Get existing credentials for a user.
     */
    private function getExistingCredentials(Admin $user): array
    {
        return $user->webAuthnKeys->map(function ($key) {
            return [
                'id' => $key->credential_id,
                'type' => 'public-key',
                'transports' => ['internal', 'hybrid'],
            ];
        })->toArray();
    }

    /**
     * Extract public key from credential (simplified).
     */
    private function extractPublicKey(array $credential): string
    {
        // This is a simplified version - in production, use a proper WebAuthn library
        return base64_encode(json_encode($credential['response']['publicKey'] ?? []));
    }

    /**
     * Get the relying party ID based on the current request.
     */
    private function getRelyingPartyId(): string
    {
        // First try the configured RP ID
        $configuredRpId = config('webauthn.relying_party.id');
        
        if ($configuredRpId && $configuredRpId !== 'localhost') {
            return $configuredRpId;
        }
        
        // Fallback to current request host
        if (request()) {
            $host = request()->getHost();
            
            // Handle localhost variations
            if (in_array($host, ['localhost', '127.0.0.1', '::1'])) {
                return 'localhost';
            }
            
            return $host;
        }
        
        // Final fallback
        return 'localhost';
    }
}

// Helper function for base64url encoding/decoding
if (!function_exists('base64url_encode')) {
    function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (!function_exists('base64url_decode')) {
    function base64url_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}