<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            PropertyStatusSeeder::class,
            ListingTypeSeeder::class,
            InquiryStatusSeeder::class,
            PropertyTypeSeeder::class,
            CitySeeder::class,
            PropertySeeder::class,
        ]);
    }
}
