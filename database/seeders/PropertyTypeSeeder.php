<?php

namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the property type lookup table with categories and Lucide icons.
 *
 * Creates nine property types: House, Apartment, Penthouse, Villa, Condo,
 * Townhouse, Loft, Studio, and Land. Each type includes a Lucide icon name
 * used by the frontend for visual identification in filters and cards.
 */
class PropertyTypeSeeder extends Seeder
{
    /**
     * Create property type records using firstOrCreate for idempotency.
     *
     * Each type gets a name, slug, Lucide icon identifier (e.g., 'house',
     * 'building-2', 'crown'), sequential sort_order, and is_active=true.
     * Icons map to the Lucide icon set used by shadcn-vue components.
     */
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
