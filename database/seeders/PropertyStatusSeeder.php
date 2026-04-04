<?php

namespace Database\Seeders;

use App\Models\PropertyStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the property status lookup table.
 *
 * Creates the six lifecycle statuses a property can have:
 * Draft, Active, Under Contract, Sold, Rented, and Archived.
 * Each status gets an auto-generated slug, sequential sort_order, and is_active=true.
 */
class PropertyStatusSeeder extends Seeder
{
    /**
     * Create property status records using firstOrCreate for idempotency.
     *
     * Statuses are inserted in display order (sort_order = array index):
     * 0=Draft, 1=Active, 2=Under Contract, 3=Sold, 4=Rented, 5=Archived.
     * Slugs are generated via Str::slug (e.g., 'under-contract').
     */
    public function run(): void
    {
        $statuses = ['Draft', 'Active', 'Under Contract', 'Sold', 'Rented', 'Archived'];

        foreach ($statuses as $index => $status) {
            PropertyStatus::firstOrCreate(
                ['slug' => Str::slug($status)],
                ['name' => $status, 'sort_order' => $index, 'is_active' => true],
            );
        }
    }
}
