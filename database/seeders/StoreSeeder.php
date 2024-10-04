<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Store;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define your Google API key
        $apiKey = env('GOOGLE_PLACES_API_KEY');

        // Location and radius for Nearby Search (e.g., New York City)
        $location = '31.037933,31.381523'; // New York City latitude, longitude
        $radius = 50000; // Search within 50km radius

        // Get the categories (store types) from your database
        $categories = Category::whereIn('name', [
            'bicycle_store', 'book_store', 'clothing_store', 'convenience_store', 
            'department_store', 'electronics_store', 'furniture_store', 'grocery_or_supermarket',
            'hardware_store', 'home_goods_store', 'jewelry_store', 'liquor_store', 
            'pet_store', 'pharmacy', 'shoe_store', 'shopping_mall', 'store', 'supermarket'
        ])->get();

        foreach ($categories as $category) {
            // Make a Nearby Search request for each store type (category)
            $response = Http::get('https://maps.googleapis.com/maps/api/place/nearbysearch/json', [
                'location' => $location,
                'radius' => $radius,
                'type' => $category->name,
                'key' => $apiKey,
            ]);

            // Handle the response from the API
            if ($response->successful()) {
                $places = $response->json()['results'];

                foreach ($places as $place) {
                    // Get the first photo reference if available
                    $photoUrl = null;
                    if (isset($place['photos']) && count($place['photos']) > 0) {
                        $photoReference = $place['photos'][0]['photo_reference'];
                        $photoUrl = $this->getPhotoUrl($photoReference, $apiKey);
                    }

                    // Insert each store into the database
                    Store::create([
                        'name' => $place['name'],
                        'address' => $place['vicinity'],
                        'latitude' => $place['geometry']['location']['lat'],
                        'longitude' => $place['geometry']['location']['lng'],
                        'place_id' => $place['place_id'], // Add Google Place ID
                        'image' => $photoUrl, // Add photo URL
                        'phone' => $place['formatted_phone_number'] ?? null,
                        'category_id' => $category->id,
                    ]);
                }
            } else {
                // Log or handle errors if the request fails
                echo "Failed to fetch stores for category: " . $category->name . "\n";
            }
        }
    }

    /**
     * Get the full photo URL from the photo reference.
     *
     * @param string $photoReference
     * @param string $apiKey
     * @return string
     */
    private function getPhotoUrl($photoReference, $apiKey)
    {
        $maxWidth = 400; // Set maximum width
        return "https://maps.googleapis.com/maps/api/place/photo?maxwidth={$maxWidth}&photoreference={$photoReference}&key={$apiKey}";
    }
}
