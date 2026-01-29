<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Electronic devices and accessories',
                'color' => '#007bff',
                'icon' => 'fas fa-laptop',
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'Accessories',
                'slug' => 'accessories',
                'description' => 'Phone and computer accessories',
                'color' => '#28a745',
                'icon' => 'fas fa-plug',
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'Audio & Video',
                'slug' => 'audio-video',
                'description' => 'Headphones, speakers, and audio equipment',
                'color' => '#dc3545',
                'icon' => 'fas fa-headphones',
                'is_active' => true,
                'sort_order' => 3
            ],
            [
                'name' => 'Computing',
                'slug' => 'computing',
                'description' => 'Computers, keyboards, mice, and peripherals',
                'color' => '#ffc107',
                'icon' => 'fas fa-desktop',
                'is_active' => true,
                'sort_order' => 4
            ],
            [
                'name' => 'Mobile Devices',
                'slug' => 'mobile-devices',
                'description' => 'Smartphones, tablets, and mobile accessories',
                'color' => '#17a2b8',
                'icon' => 'fas fa-mobile-alt',
                'is_active' => true,
                'sort_order' => 5
            ],
            [
                'name' => 'General',
                'slug' => 'general',
                'description' => 'General products and miscellaneous items',
                'color' => '#6c757d',
                'icon' => 'fas fa-box',
                'is_active' => true,
                'sort_order' => 99
            ]
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}