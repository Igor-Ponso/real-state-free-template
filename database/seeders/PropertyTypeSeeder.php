<?php

namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PropertyTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'House', 'icon' => 'house'],
            ['name' => 'Apartment', 'icon' => 'building-2'],
            ['name' => 'Penthouse', 'icon' => 'crown'],
            ['name' => 'Villa', 'icon' => 'castle'],
            ['name' => 'Condo', 'icon' => 'building'],
            ['name' => 'Townhouse', 'icon' => 'home'],
            ['name' => 'Loft', 'icon' => 'warehouse'],
            ['name' => 'Studio', 'icon' => 'square'],
            ['name' => 'Land', 'icon' => 'trees'],
        ];

        foreach ($types as $index => $type) {
            PropertyType::firstOrCreate(
                ['slug' => Str::slug($type['name'])],
                [
                    'name' => $type['name'],
                    'icon' => $type['icon'],
                    'sort_order' => $index,
                    'is_active' => true,
                ],
            );
        }
    }
}
