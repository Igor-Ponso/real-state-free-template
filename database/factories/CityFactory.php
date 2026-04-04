<?php

namespace Database\Factories;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory for generating fake city records.
 *
 * Creates Canadian cities with random province codes, a unique slug,
 * geographic coordinates within Canada's bounds, and no image.
 * Defaults to non-featured with sort_order 0.
 *
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Generates a city with a Faker-generated name, a random Canadian province
     * code (BC, ON, QC, AB, NS), country 'CA', latitude between 43-51,
     * longitude between -130 and -60, null image, non-featured, sort_order 0.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->city();

        return [
            'name' => $name,
            'state' => fake()->randomElement(['BC', 'ON', 'QC', 'AB', 'NS']),
            'country' => 'CA',
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'latitude' => fake()->latitude(43, 51),
            'longitude' => fake()->longitude(-130, -60),
            'image_path' => null,
            'is_featured' => false,
            'sort_order' => 0,
        ];
    }

    /**
     * Mark the city as featured.
     *
     * Featured cities are displayed on the landing page's city showcase section.
     */
    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }
}
