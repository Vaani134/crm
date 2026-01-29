<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    // Automatically generate slug when creating/updating
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
            // Set default sort_order if not provided
            if (is_null($category->sort_order)) {
                $category->sort_order = 0;
            }
        });
        
        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    // Relationships
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Accessors
    public function getProductCountAttribute()
    {
        return $this->products()->count();
    }

    public function getLowStockProductCountAttribute()
    {
        return $this->products()->where('stock_qty', '<=', 5)->count();
    }

    public function getTotalStockValueAttribute()
    {
        return $this->products()->selectRaw('SUM(price * stock_qty) as total')->value('total') ?? 0;
    }

    // Helper methods
    public function hasProducts()
    {
        return $this->products()->exists();
    }

    public static function getDefaultCategory()
    {
        return self::firstOrCreate(
            ['slug' => 'general'],
            [
                'name' => 'General',
                'description' => 'Default category for products',
                'color' => '#6c757d',
                'icon' => 'fas fa-box',
                'is_active' => true,
                'sort_order' => 0
            ]
        );
    }
}