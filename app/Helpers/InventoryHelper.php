<?php

namespace App\Helpers;

class InventoryHelper
{
    public static function formatCurrency($amount)
    {
        return '$' . number_format($amount, 2);
    }

    public static function generateReceiptNo()
    {
        return 'POS-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    }

    public static function sanitizeInput($data)
    {
        return htmlspecialchars(strip_tags(trim($data)));
    }
}