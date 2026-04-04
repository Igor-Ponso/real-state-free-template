<?php

namespace Database\Factories;

use App\Models\PropertyType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory for generating fake property type records.
 *
 * Creates property type lookup entries (House, Apartment, Penthouse, etc.)
 * with a unique name, auto-generated slug, no icon, and active by default.
 * Primarily used in tests; the PropertyTypeSeeder handles production data.
 *
 * @extends Factory<PropertyType>
 */
class PropertyTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Picks a unique property type name from the predefined list (House,
     * Apartment, Penthouse, Villa, Condo, Townhouse, Loft, Studio, Land),
     * generates a slug, sets no icon, sort_order 0, and is_active true.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'House', 'Apartment', 'Penthouse', 'Villa', 'Condo',
            'Townhouse', 'Loft', 'Studio', 'Land',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'icon' => null,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
