<?php

namespace Database\Factories;

use App\Models\PropertyType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PropertyType>
 */
class PropertyTypeFactory extends Factory
{
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
