<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'admin_id',
        'action',
        'module',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    // Scope to get logs for a specific user
    public function scopeForUser($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    // Scope to get logs by module
    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }

    // Scope to get logs by action
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    // Scope to get recent logs
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Get formatted action name
    public function getFormattedActionAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->action));
    }

    // Get badge color based on action type
    public function getBadgeColorAttribute()
    {
        return match($this->action) {
            'login' => 'success',
            'logout' => 'secondary',
            'create_product', 'create_sale', 'create_user', 'create_category' => 'primary',
            'update_product', 'update_stock', 'stock_refill', 'update_user', 'update_category' => 'info',
            'delete_product', 'delete_user', 'delete_category' => 'danger',
            'receipt_email_sent' => 'success',
            'toggle_category_status' => 'warning',
            default => 'dark'
        };
    }
}