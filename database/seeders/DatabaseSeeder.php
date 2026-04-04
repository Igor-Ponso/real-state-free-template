<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Master database seeder that orchestrates all child seeders.
 *
 * Runs seeders in dependency order: roles/permissions and lookup tables first
 * (statuses, types, cities), then the main PropertySeeder that creates users,
 * agents, properties, inquiries, favorites, and views.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Execution order (each seeder is idempotent via firstOrCreate):
     * 1. RoleAndPermissionSeeder — roles (admin, agent, client) and permissions
     * 2. PropertyStatusSeeder — property lifecycle statuses
     * 3. ListingTypeSeeder — sale and rental listing types
     * 4. InquiryStatusSeeder — inquiry workflow statuses
     * 5. PropertyTypeSeeder — property categories with icons
     * 6. CitySeeder — 8 Canadian cities with coordinates
     * 7. PropertySeeder — users, properties, inquiries, favorites, views
     */
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
