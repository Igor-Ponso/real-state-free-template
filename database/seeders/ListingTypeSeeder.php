<?php

namespace Database\Seeders;

use App\Models\ListingType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the listing type lookup table.
 *
 * Creates the two listing types: "For Sale" (slug: sale) and "For Rent"
 * (slug: rental). These are referenced by PropertyFactory and throughout
 * the application to distinguish between sale and rental listings.
 */
class ListingTypeSeeder extends Seeder
{
    /**
     * Create listing type records using firstOrCreate for idempotency.
     *
     * Maps display names to canonical slugs: "For Sale" => "sale",
     * "For Rent" => "rental". Sort order follows array index (0, 1).
     */
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
