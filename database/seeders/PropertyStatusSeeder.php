<?php

namespace Database\Seeders;

use App\Models\PropertyStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PropertyStatusSeeder extends Seeder
{
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
