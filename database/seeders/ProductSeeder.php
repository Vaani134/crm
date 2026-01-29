<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure categories exist first
        $this->call(CategorySeeder::class);
        
        // Get categories
        $electronics = Category::where('slug', 'electronics')->first();
        $accessories = Category::where('slug', 'accessories')->first();
        $audio = Category::where('slug', 'audio-video')->first();
        $computing = Category::where('slug', 'computing')->first();
        $mobile = Category::where('slug', 'mobile-devices')->first();

        $products = [
            [
                'category_id' => $audio->id,
                'barcode_number' => 'SKU001',
                'name' => 'Wireless Bluetooth Headphones',
                'price' => 79.99,
                'stock_qty' => 25,
            ],
            [
                'category_id' => $accessories->id,
                'barcode_number' => 'SKU002',
                'name' => 'USB-C Charging Cable',
                'price' => 12.99,
                'stock_qty' => 3, // Low stock
            ],
            [
                'category_id' => $mobile->id,
                'barcode_number' => 'SKU003',
                'name' => 'Smartphone Case',
                'price' => 24.99,
                'stock_qty' => 15,
            ],
            [
                'category_id' => $electronics->id,
                'barcode_number' => 'SKU004',
                'name' => 'Portable Power Bank',
                'price' => 45.99,
                'stock_qty' => 2, // Low stock
            ],
            [
                'category_id' => $computing->id,
                'barcode_number' => 'SKU005',
                'name' => 'Wireless Mouse',
                'price' => 29.99,
                'stock_qty' => 18,
            ],
            [
                'category_id' => $computing->id,
                'barcode_number' => 'SKU006',
                'name' => 'Mechanical Keyboard',
                'price' => 89.99,
                'stock_qty' => 8,
            ],
            [
                'category_id' => $electronics->id,
                'barcode_number' => 'SKU007',
                'name' => 'HD Webcam',
                'price' => 65.99,
                'stock_qty' => 1, // Low stock
            ],
            [
                'category_id' => $audio->id,
                'barcode_number' => 'SKU008',
                'name' => 'Bluetooth Speaker',
                'price' => 39.99,
                'stock_qty' => 12,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['barcode_number' => $product['barcode_number']],
                $product
            );
        }
    }
}