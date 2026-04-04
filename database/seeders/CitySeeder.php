<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds 8 Canadian cities with real geographic coordinates.
 *
 * Creates major Canadian cities across 4 provinces (BC, ON, QC, AB, NS).
 * The first 5 cities (Vancouver, Toronto, Montreal, Calgary, Whistler) are
 * marked as featured with hero images for the landing page city showcase.
 * The remaining 3 (Ottawa, Edmonton, Halifax) are non-featured with no images.
 */
class CitySeeder extends Seeder
{
    /**
     * Create city records using firstOrCreate for idempotency.
     *
     * Each city includes real latitude/longitude coordinates for Leaflet map
     * rendering, province codes, country 'CA', optional image paths, featured
     * flags, and sequential sort_order. Slugs are generated from city names.
     */
    public function run(): void
    {
        $cities = [
            ['name' => 'Vancouver', 'state' => 'BC', 'lat' => 49.2827, 'lng' => -123.1207, 'featured' => true, 'image' => '/images/cities/vancouver.jpg'],
            ['name' => 'Toronto', 'state' => 'ON', 'lat' => 43.6532, 'lng' => -79.3832, 'featured' => true, 'image' => '/images/cities/toronto.jpg'],
            ['name' => 'Montreal', 'state' => 'QC', 'lat' => 45.5017, 'lng' => -73.5673, 'featured' => true, 'image' => '/images/cities/montreal.jpg'],
            ['name' => 'Calgary', 'state' => 'AB', 'lat' => 51.0447, 'lng' => -114.0719, 'featured' => true, 'image' => '/images/cities/calgary.jpg'],
            ['name' => 'Whistler', 'state' => 'BC', 'lat' => 50.1163, 'lng' => -122.9574, 'featured' => true, 'image' => '/images/cities/whistler.jpg'],
            ['name' => 'Ottawa', 'state' => 'ON', 'lat' => 45.4215, 'lng' => -75.6972, 'featured' => false, 'image' => null],
            ['name' => 'Edmonton', 'state' => 'AB', 'lat' => 53.5461, 'lng' => -113.4938, 'featured' => false, 'image' => null],
            ['name' => 'Halifax', 'state' => 'NS', 'lat' => 44.6488, 'lng' => -63.5752, 'featured' => false, 'image' => null],
        ];

        foreach ($cities as $index => $city) {
            City::firstOrCreate(
                ['slug' => Str::slug($city['name'])],
                [
                    'name' => $city['name'],
                    'state' => $city['state'],
                    'country' => 'CA',
                    'latitude' => $city['lat'],
                    'longitude' => $city['lng'],
                    'image_path' => $city['image'],
                    'is_featured' => $city['featured'],
                    'sort_order' => $index,
                ],
            );
        }
    }
}
