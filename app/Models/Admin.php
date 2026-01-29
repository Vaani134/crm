<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Admin extends Authenticatable
{
    protected $fillable = [
        'username',
        'password',
        'full_name',
        'email',
        'role'
    ];

    protected $hidden = [
        'password',
    ];

    // Removed the setPasswordAttribute mutator to avoid double hashing

    public function sales()
    {
        return $this->hasMany(Sale::class, 'created_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Get all WebAuthn keys for this admin.
     */
    public function webAuthnKeys(): MorphMany
    {
        return $this->morphMany(WebAuthnKey::class, 'authenticatable');
    }

    /**
     * Get the user identifier for WebAuthn.
     */
    public function getWebAuthnUserId(): string
    {
        return (string) $this->id;
    }

    /**
     * Get the user name for WebAuthn.
     */
    public function getWebAuthnUserName(): string
    {
        return $this->username;
    }

    /**
     * Get the user display name for WebAuthn.
     */
    public function getWebAuthnDisplayName(): string
    {
        return $this->full_name;
    }
}
