<?php

namespace Database\Factories;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
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

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }
}
