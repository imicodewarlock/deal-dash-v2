<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $storeTypes = [
        //     'bicycle_store', 'book_store', 'clothing_store', 'convenience_store', 
        //     'department_store', 'electronics_store', 'furniture_store', 'grocery_or_supermarket',
        //     'hardware_store', 'home_goods_store', 'jewelry_store', 'liquor_store', 
        //     'pet_store', 'pharmacy', 'shoe_store', 'shopping_mall', 'store', 'supermarket'
        // ];

        // Define the categories with corresponding images
        $categories = [
            ['name' => 'bicycle_store', 'image' => 'https://example.com/images/bicycle_store.jpg'],
            ['name' => 'book_store', 'image' => 'https://example.com/images/book_store.jpg'],
            ['name' => 'clothing_store', 'image' => 'https://example.com/images/clothing_store.jpg'],
            ['name' => 'convenience_store', 'image' => 'https://example.com/images/convenience_store.jpg'],
            ['name' => 'department_store', 'image' => 'https://example.com/images/department_store.jpg'],
            ['name' => 'electronics_store', 'image' => 'https://example.com/images/electronics_store.jpg'],
            ['name' => 'furniture_store', 'image' => 'https://example.com/images/furniture_store.jpg'],
            ['name' => 'grocery_or_supermarket', 'image' => 'https://example.com/images/grocery_store.jpg'],
            ['name' => 'hardware_store', 'image' => 'https://example.com/images/hardware_store.jpg'],
            ['name' => 'home_goods_store', 'image' => 'https://example.com/images/home_goods_store.jpg'],
            ['name' => 'jewelry_store', 'image' => 'https://example.com/images/jewelry_store.jpg'],
            ['name' => 'liquor_store', 'image' => 'https://example.com/images/liquor_store.jpg'],
            ['name' => 'pet_store', 'image' => 'https://example.com/images/pet_store.jpg'],
            ['name' => 'pharmacy', 'image' => 'https://example.com/images/pharmacy.jpg'],
            ['name' => 'shoe_store', 'image' => 'https://example.com/images/shoe_store.jpg'],
            ['name' => 'shopping_mall', 'image' => 'https://example.com/images/shopping_mall.jpg'],
            ['name' => 'supermarket', 'image' => 'https://example.com/images/supermarket.jpg'],
        ];

        // Insert categories into the database
        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
