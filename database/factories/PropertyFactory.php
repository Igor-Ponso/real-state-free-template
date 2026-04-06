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

/**
 * Factory for generating fake property listings.
 *
 * Creates comprehensive real estate listings with randomized titles, prices,
 * locations, physical attributes, amenities, and features. The default state
 * produces an active, published, non-featured property that is randomly either
 * for sale (CAD $300K-$10M) or for rent (CAD $1,500-$10,000/mo).
 *
 * Provides state methods for all lifecycle stages: active, featured, draft,
 * sold, rented, forSale, and forRent.
 *
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Generates a full property listing with a composed title (e.g., "Luxury
     * Penthouse in Downtown"), a unique slug, 3-paragraph description, and
     * randomly assigned listing type (sale vs. rental). Sale properties get
     * prices in the $300K-$10M range with lot sizes; rental properties get
     * $1,500-$10,000/mo pricing with deposit, lease length, and availability
     * date. Includes 1-6 bedrooms, 1-4 bathrooms, 500-6,000 sqft, random
     * amenities, interior features (flooring, heating, cooling, view), and
     * Canadian postal codes. Defaults to active status, published, non-featured.
     *
     * @return array<string, mixed>
     */
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
            'unit_amenities' => fake()->randomElements(Property::UNIT_AMENITIES, fake()->numberBetween(2, 6)),
            'building_amenities' => fake()->randomElements(Property::BUILDING_AMENITIES, fake()->numberBetween(3, 8)),
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

    /**
     * Set the property to active status.
     *
     * Assigns the 'active' property status, marks as published, and sets
     * published_at to now. This is the default public-facing state.
     */
    public function active(): static
    {
        return $this->state(fn () => [
            'property_status_id' => fn () => PropertyStatus::where('slug', 'active')->first()?->id,
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    /**
     * Set the property as featured and active.
     *
     * Marks is_featured=true and ensures the property is active and published.
     * Featured properties appear prominently on the landing page hero section.
     */
    public function featured(): static
    {
        return $this->state(fn () => [
            'is_featured' => true,
            'property_status_id' => fn () => PropertyStatus::where('slug', 'active')->first()?->id,
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    /**
     * Set the property to draft status.
     *
     * Assigns the 'draft' property status, marks as unpublished, and clears
     * published_at. Draft properties are not visible to the public.
     */
    public function draft(): static
    {
        return $this->state(fn () => [
            'property_status_id' => fn () => PropertyStatus::where('slug', 'draft')->first()?->id,
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    /**
     * Set the property to sold status.
     *
     * Assigns the 'sold' property status and ensures the listing type is 'sale'.
     * Used for properties that have completed a sale transaction.
     */
    public function sold(): static
    {
        return $this->state(fn () => [
            'property_status_id' => fn () => PropertyStatus::where('slug', 'sold')->first()?->id,
            'listing_type_id' => fn () => ListingType::where('slug', 'sale')->first()?->id,
        ]);
    }

    /**
     * Set the property to rented status.
     *
     * Assigns the 'rented' property status and ensures the listing type is 'rental'.
     * Used for rental properties that have been leased to a tenant.
     */
    public function rented(): static
    {
        return $this->state(fn () => [
            'property_status_id' => fn () => PropertyStatus::where('slug', 'rented')->first()?->id,
            'listing_type_id' => fn () => ListingType::where('slug', 'rental')->first()?->id,
        ]);
    }

    /**
     * Set the property as a sale listing.
     *
     * Assigns the 'sale' listing type with a price range of CAD $300K-$10M.
     * Clears rental-specific fields (deposit, lease_length_months, available_from).
     */
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

    /**
     * Set the property as a rental listing.
     *
     * Assigns the 'rental' listing type with a monthly price of CAD $1,500-$10,000,
     * a deposit of CAD $1,000-$5,000, a random lease length (6, 12, or 24 months),
     * and an availability date within the next 3 months.
     */
    /**
     * Mark the property to skip placeholder images (tests the no-image fallback).
     *
     * Sets a `_no_images` key in features that the resource checks.
     */
    public function withoutImages(): static
    {
        return $this->state(fn () => [
            'features' => ['_no_images' => true],
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
