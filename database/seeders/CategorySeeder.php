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
        // Manually define the place types from Google's supported types
        // $placeTypes = [
        //     'accounting', 'airport', 'amusement_park', 'aquarium', 'art_gallery', 
        //     'atm', 'bakery', 'bank', 'bar', 'beauty_salon', 'bicycle_store', 
        //     'book_store', 'bowling_alley', 'bus_station', 'cafe', 'campground', 
        //     'car_dealer', 'car_rental', 'car_repair', 'car_wash', 'casino', 
        //     'cemetery', 'church', 'city_hall', 'clothing_store', 'convenience_store',
        //     'courthouse', 'dentist', 'department_store', 'doctor', 'drugstore', 
        //     'electrician', 'electronics_store', 'embassy', 'fire_station', 'florist', 
        //     'funeral_home', 'furniture_store', 'gas_station', 'gym', 'hair_care', 
        //     'hardware_store', 'hindu_temple', 'home_goods_store', 'hospital', 'insurance_agency', 
        //     'jewelry_store', 'laundry', 'lawyer', 'library', 'light_rail_station', 
        //     'liquor_store', 'local_government_office', 'locksmith', 'lodging', 'meal_delivery', 
        //     'meal_takeaway', 'mosque', 'movie_rental', 'movie_theater', 'moving_company', 
        //     'museum', 'night_club', 'painter', 'park', 'parking', 
        //     'pet_store', 'pharmacy', 'physiotherapist', 'plumber', 'police', 
        //     'post_office', 'real_estate_agency', 'restaurant', 'roofing_contractor', 'rv_park', 
        //     'school', 'shoe_store', 'shopping_mall', 'spa', 'stadium', 
        //     'storage', 'store', 'subway_station', 'supermarket', 'synagogue', 
        //     'taxi_stand', 'train_station', 'transit_station', 'travel_agency', 'veterinary_care', 
        //     'zoo'
        // ];

        // $storeTypes = [
        //     'bicycle_store', 'book_store', 'clothing_store', 'convenience_store', 
        //     'department_store', 'electronics_store', 'furniture_store', 'grocery_or_supermarket',
        //     'hardware_store', 'home_goods_store', 'jewelry_store', 'liquor_store', 
        //     'pet_store', 'pharmacy', 'shoe_store', 'shopping_mall', 'store', 'supermarket'
        // ];

        // Define the categories with corresponding images
        $categories = [
            ['name' => 'bicycle_store', 'type' => 'bicycle_store', 'image' => 'https://example.com/images/bicycle_store.jpg'],
            ['name' => 'bicycle_store', 'type' => 'book_store', 'image' => 'https://example.com/images/book_store.jpg'],
            ['name' => 'bicycle_store', 'type' => 'clothing_store', 'image' => 'https://example.com/images/clothing_store.jpg'],
            ['name' => 'bicycle_store', 'type' => 'convenience_store', 'image' => 'https://example.com/images/convenience_store.jpg'],
            ['name' => 'bicycle_store', 'type' => 'department_store', 'image' => 'https://example.com/images/department_store.jpg'],
            ['name' => 'bicycle_store', 'type' => 'electronics_store', 'image' => 'https://example.com/images/electronics_store.jpg'],
            ['name' => 'bicycle_store', 'type' => 'furniture_store', 'image' => 'https://example.com/images/furniture_store.jpg'],
            ['name' => 'bicycle_store', 'type' => 'grocery_or_supermarket', 'image' => 'https://example.com/images/grocery_store.jpg'],
            ['name' => 'bicycle_store', 'type' => 'hardware_store', 'image' => 'https://example.com/images/hardware_store.jpg'],
            ['name' => 'bicycle_store', 'type' => 'home_goods_store', 'image' => 'https://example.com/images/home_goods_store.jpg'],
            ['name' => 'bicycle_store', 'type' => 'jewelry_store', 'image' => 'https://example.com/images/jewelry_store.jpg'],
            ['name' => 'bicycle_store', 'type' => 'liquor_store', 'image' => 'https://example.com/images/liquor_store.jpg'],
            ['name' => 'bicycle_store', 'type' => 'pet_store', 'image' => 'https://example.com/images/pet_store.jpg'],
            ['name' => 'bicycle_store', 'type' => 'pharmacy', 'image' => 'https://example.com/images/pharmacy.jpg'],
            ['name' => 'bicycle_store', 'type' => 'shoe_store', 'image' => 'https://example.com/images/shoe_store.jpg'],
            ['name' => 'bicycle_store', 'type' => 'shopping_mall', 'image' => 'https://example.com/images/shopping_mall.jpg'],
            ['name' => 'bicycle_store', 'type' => 'supermarket', 'image' => 'https://example.com/images/supermarket.jpg'],
            ['name' => 'bicycle_store', 'type' => 'store', 'image' => 'https://example.com/images/supermarket.jpg'],
        ];

        // Insert categories into the database
        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
