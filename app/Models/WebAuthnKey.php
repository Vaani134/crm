<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WebAuthnKey extends Model
{
    protected $table = 'webauthn_keys';
    
    protected $fillable = [
        'authenticatable_type',
        'authenticatable_id',
        'name',
        'credential_id',
        'public_key',
        'aaguid',
        'counter',
        'last_used_at',
    ];

    protected $casts = [
        'counter' => 'integer',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the authenticatable entity that owns the WebAuthn key.
     */
    public function authenticatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Update the counter and last used timestamp.
     */
    public function updateCounter(int $counter): void
    {
        $this->update([
            'counter' => $counter,
            'last_used_at' => now(),
        ]);
    }
}