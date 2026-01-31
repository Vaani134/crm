<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'barcode_number',
        'name',
        'description',
        'brand_name',
        'price',
        'stock_qty',
        'image_path',
        'manufacturing_date',
        'expiry_date',
        'warranty_months',
        'guarantee_months',
        'tax_percentage',
        'discount_percentage',
        'stock_expiry_details'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_qty' => 'integer',
        'tax_percentage' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'warranty_months' => 'integer',
        'guarantee_months' => 'integer',
        'stock_expiry_details' => 'array'
    ];

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function isLowStock()
    {
        return $this->stock_qty <= 5;
    }

    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon()
    {
        return $this->expiry_date && $this->expiry_date->diffInDays(now()) <= 30 && !$this->isExpired();
    }

    public function getImageUrlAttribute()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    public function scopeLowStock($query)
    {
        return $query->where('stock_qty', '<=', 5);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('barcode_number', 'like', "%{$search}%")
              ->orWhere('brand_name', 'like', "%{$search}%");
        });
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeWithCategory($query)
    {
        return $query->with('category');
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    public function scopeExpiringSoon($query)
    {
        return $query->whereBetween('expiry_date', [now(), now()->addDays(30)]);
    }
}
