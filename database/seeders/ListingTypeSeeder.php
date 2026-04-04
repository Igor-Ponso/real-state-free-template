<?php

namespace Database\Seeders;

use App\Models\ListingType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ListingTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['For Sale', 'For Rent'];

        foreach ($types as $index => $type) {
            ListingType::firstOrCreate(
                ['slug' => Str::slug($type === 'For Sale' ? 'sale' : 'rental')],
                ['name' => $type, 'sort_order' => $index, 'is_active' => true],
            );
        }
    }
}
