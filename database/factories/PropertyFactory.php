<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\ListingType;
use App\Models\Property;
use App\Models\PropertyStatus;
use App\Models\PropertyType;
use App\Models\User;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    private const AMENITIES = [
        'pool', 'gym', 'doorman', 'elevator', 'balcony', 'fireplace',
        'garage', 'garden', 'security', 'laundry', 'storage', 'rooftop',
        'concierge', 'sauna', 'tennis', 'playground', 'bbq', 'wine_cellar',
    ];

    public function definition(): array
    {
        $title = fake()->randomElement([
            'Modern', 'Luxury', 'Elegant', 'Stunning', 'Spacious', 'Charming', 'Contemporary',
        ]).' '.fake()->randomElement([
            'Penthouse', 'Villa', 'Apartment', 'Condo', 'Townhouse', 'Loft', 'Estate',
        ]).' in '.fake()->randomElement([
            'Downtown', 'Waterfront', 'Midtown', 'West End', 'East Village', 'Old Port',
        ]);

        $isSale = fake()->boolean();

        return [
            'user_id' => User::factory(),
            'property_type_id' => PropertyType::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->paragraphs(3, true),
            'listing_type_id' => fn () => ListingType::where('slug', $isSale ? 'sale' : 'rental')->first()?->id ?? ListingType::first()->id,
            'property_status_id' => fn () => PropertyStatus::where('slug', 'active')->first()?->id ?? PropertyStatus::first()->id,
            'price' => Money::ofMinor(
                $isSale
                    ? fake()->numberBetween(30000000, 1000000000)
                    : fake()->numberBetween(150000, 1000000),
                'CAD',
            ),
            'price_min' => null,
            'price_max' => null,
            'address' => fake()->streetAddress(),
            'city_id' => City::factory(),
            'neighborhood' => fake()->randomElement([null, 'Yaletown', 'Kitsilano', 'Gastown', 'Coal Harbour', 'Yorkville', 'The Annex']),
            'state' => fake()->randomElement(['BC', 'ON', 'QC', 'AB']),
            'zip_code' => strtoupper(fake()->bothify('?#? #?#')),
            'latitude' => fake()->latitude(43.5, 50.5),
            'longitude' => fake()->longitude(-130, -60),
            'bedrooms' => fake()->numberBetween(1, 6),
            'bathrooms' => fake()->randomElement([1, 1.5, 2, 2.5, 3, 3.5, 4]),
            'area_sqft' => fake()->numberBetween(500, 6000),
            'lot_size_sqft' => $isSale ? fake()->numberBetween(2000, 20000) : null,
            'year_built' => fake()->numberBetween(1990, 2026),
            'parking_spaces' => fake()->numberBetween(0, 3),
            'floor' => fake()->optional(0.5)->numberBetween(1, 40),
            'total_floors' => fake()->optional(0.5)->numberBetween(1, 50),
            'amenities' => fake()->randomElements(self::AMENITIES, fake()->numberBetween(2, 8)),
            'features' => [
                'flooring' => fake()->randomElement(['hardwood', 'tile', 'carpet', 'marble']),
                'heating' => fake()->randomElement(['central', 'radiant', 'baseboard']),
                'cooling' => fake()->randomElement(['central_ac', 'split', 'none']),
                'view' => fake()->randomElement(['ocean', 'mountain', 'city', 'park', 'garden']),
            ],
            'deposit' => $isSale ? null : Money::ofMinor(fake()->numberBetween(100000, 500000), 'CAD'),
            'lease_length_months' => $isSale ? null : fake()->randomElement([6, 12, 24]),
            'available_from' => $isSale ? null : fake()->dateTimeBetween('now', '+3 months'),
            'pets_allowed' => fake()->boolean(60),
            'meta_title' => null,
            'meta_description' => null,
            'is_featured' => false,
            'is_published' => true,
            'published_at' => now(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'property_status_id' => fn () => PropertyStatus::where('slug', 'active')->first()?->id,
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn () => [
            'is_featured' => true,
            'property_status_id' => fn () => PropertyStatus::where('slug', 'active')->first()?->id,
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'property_status_id' => fn () => PropertyStatus::where('slug', 'draft')->first()?->id,
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    public function sold(): static
    {
        return $this->state(fn () => [
            'property_status_id' => fn () => PropertyStatus::where('slug', 'sold')->first()?->id,
            'listing_type_id' => fn () => ListingType::where('slug', 'sale')->first()?->id,
        ]);
    }

    public function rented(): static
    {
        return $this->state(fn () => [
            'property_status_id' => fn () => PropertyStatus::where('slug', 'rented')->first()?->id,
            'listing_type_id' => fn () => ListingType::where('slug', 'rental')->first()?->id,
        ]);
    }

    public function forSale(): static
    {
        return $this->state(fn () => [
            'listing_type_id' => fn () => ListingType::where('slug', 'sale')->first()?->id,
            'price' => Money::ofMinor(fake()->numberBetween(30000000, 1000000000), 'CAD'),
            'deposit' => null,
            'lease_length_months' => null,
            'available_from' => null,
        ]);
    }

    public function forRent(): static
    {
        return $this->state(fn () => [
            'listing_type_id' => fn () => ListingType::where('slug', 'rental')->first()?->id,
            'price' => Money::ofMinor(fake()->numberBetween(150000, 1000000), 'CAD'),
            'deposit' => Money::ofMinor(fake()->numberBetween(100000, 500000), 'CAD'),
            'lease_length_months' => fake()->randomElement([6, 12, 24]),
            'available_from' => fake()->dateTimeBetween('now', '+3 months'),
        ]);
    }
}
