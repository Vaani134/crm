<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class AuditLogService
{
    public static function log(
        string $action,
        string $module,
        string $description,
        array $oldValues = null,
        array $newValues = null,
        int $adminId = null
    ) {
        $adminId = $adminId ?? Auth::guard('admin')->id();
        
        if (!$adminId) {
            return; // Don't log if no user is authenticated
        }

        // Check if audit_logs table exists before trying to log
        try {
            if (!\Schema::hasTable('audit_logs')) {
                return; // Silently fail if table doesn't exist
            }
        } catch (\Exception $e) {
            return; // Silently fail on any database error
        }

        try {
            $request = request();
            
            AuditLog::create([
                'admin_id' => $adminId,
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        } catch (\Exception $e) {
            // Silently fail if logging fails - don't break the main functionality
            return;
        }
    }

    // Specific logging methods for common actions
    public static function logLogin(int $adminId, string $username)
    {
        self::log(
            'login',
            'auth',
            "User '{$username}' logged in successfully",
            null,
            null,
            $adminId
        );
    }

    public static function logLogout(int $adminId, string $username)
    {
        self::log(
            'logout',
            'auth',
            "User '{$username}' logged out",
            null,
            null,
            $adminId
        );
    }

    public static function logProductCreate(array $productData)
    {
        self::log(
            'create_product',
            'products',
            "Created product '{$productData['name']}' (SKU: {$productData['barcode_number']})",
            null,
            $productData
        );
    }

    public static function logProductUpdate(array $oldData, array $newData)
    {
        self::log(
            'update_product',
            'products',
            "Updated product '{$newData['name']}' (SKU: {$newData['barcode_number']})",
            $oldData,
            $newData
        );
    }

    public static function logProductDelete(array $productData)
    {
        self::log(
            'delete_product',
            'products',
            "Deleted product '{$productData['name']}' (SKU: {$productData['barcode_number']})",
            $productData,
            null
        );
    }

    public static function logStockRefill(string $productName, int $oldStock, int $newStock, int $addedQty)
    {
        self::log(
            'refill_stock',
            'products',
            "Refilled stock for '{$productName}': {$oldStock} → {$newStock} (+{$addedQty})",
            ['stock_qty' => $oldStock],
            ['stock_qty' => $newStock, 'added_qty' => $addedQty]
        );
    }

    public static function logSaleCreate(array $saleData)
    {
        self::log(
            'create_sale',
            'sales',
            "Created sale {$saleData['receipt_no']} - Total: \${$saleData['grand_total']} ({$saleData['total_items']} items)",
            null,
            $saleData
        );
    }

    public static function logUserCreate(array $userData)
    {
        // Remove password from logged data
        $logData = $userData;
        unset($logData['password']);
        
        self::log(
            'create_user',
            'users',
            "Created user '{$userData['username']}' ({$userData['full_name']}) with role '{$userData['role']}'",
            null,
            $logData
        );
    }

    public static function logUserDelete(array $userData)
    {
        // Remove password from logged data
        $logData = $userData;
        unset($logData['password']);
        
        self::log(
            'delete_user',
            'users',
            "Deleted user '{$userData['username']}' ({$userData['full_name']})",
            $logData,
            null
        );
    }

    public static function logUserUpdate(array $oldData, array $newData)
    {
        // Remove password from logged data
        $oldLogData = $oldData;
        $newLogData = $newData;
        unset($oldLogData['password']);
        unset($newLogData['password']);
        
        self::log(
            'update_user',
            'users',
            "Updated user '{$newData['username']}' ({$newData['full_name']})",
            $oldLogData,
            $newLogData
        );
    }

    public static function logCustom(array $data)
    {
        $action = $data['action'] ?? 'custom_action';
        $module = $data['module'] ?? 'system';
        $description = $data['description'] ?? 'Custom action performed';
        $oldValues = $data['old_values'] ?? null;
        $newValues = $data['new_values'] ?? $data;
        
        self::log($action, $module, $description, $oldValues, $newValues);
    }
}