<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WebAuthn Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the WebAuthn settings for your application.
    |
    */

    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Relying Party
    |--------------------------------------------------------------------------
    |
    | This is the information about your application that will be used
    | during the WebAuthn registration and authentication process.
    |
    */

    'relying_party' => [
        'name' => env('WEBAUTHN_NAME', config('app.name')),
        'id' => env('WEBAUTHN_ID', parse_url(config('app.url'), PHP_URL_HOST)),
    ],

    /*
    |--------------------------------------------------------------------------
    | Challenge Length
    |--------------------------------------------------------------------------
    |
    | This is the length of the challenge that will be generated for
    | WebAuthn registration and authentication.
    |
    */

    'challenge_length' => 32,

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | This is the timeout in milliseconds for WebAuthn operations.
    |
    */

    'timeout' => 60000,

    /*
    |--------------------------------------------------------------------------
    | User Verification
    |--------------------------------------------------------------------------
    |
    | This determines the user verification requirement for WebAuthn.
    | Options: 'required', 'preferred', 'discouraged'
    |
    */

    'user_verification' => 'preferred',

    /*
    |--------------------------------------------------------------------------
    | Attestation Conveyance
    |--------------------------------------------------------------------------
    |
    | This determines the attestation conveyance preference.
    | Options: 'none', 'indirect', 'direct'
    |
    */

    'attestation_conveyance' => 'none',

    /*
    |--------------------------------------------------------------------------
    | Public Key Credential Parameters
    |--------------------------------------------------------------------------
    |
    | This is the list of supported public key credential parameters.
    |
    */

    'public_key_credential_parameters' => [
        ['type' => 'public-key', 'alg' => -7],  // ES256
        ['type' => 'public-key', 'alg' => -257], // RS256
    ],
];