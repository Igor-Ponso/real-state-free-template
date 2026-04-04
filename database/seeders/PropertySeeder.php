<?php

namespace Database\Seeders;

use App\Models\AgentProfile;
use App\Models\City;
use App\Models\Favorite;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\PropertyView;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds the full property ecosystem: users, agents, properties, and interactions.
 *
 * Creates a complete demo dataset with an admin user, 8 agents with featured
 * profiles, 10 client users, 30 properties in various states (featured, active,
 * sold, draft), plus inquiries, favorites, and view analytics for published
 * properties. This is the main seeder that brings the application to life.
 */
class PropertySeeder extends Seeder
{
    /**
     * Seed users, properties, and all related interaction data.
     *
     * Hierarchy created:
     * 1. Admin user (admin@luxuryestate.com) with 'admin' role
     * 2. 8 agent users (agent1-8@luxuryestate.com) with 'agent' role and featured profiles
     * 3. 10 anonymous client users with 'client' role
     * 4. 30 properties distributed across agents, types, and cities:
     *    - Properties 0-3: featured (highlighted on landing page)
     *    - Properties 4-19: active (standard published listings)
     *    - Properties 20-24: sold (completed transactions)
     *    - Properties 25-29: draft (unpublished)
     * 5. 1-5 inquiries on up to 15 random published properties (70% from clients, 30% guest)
     * 6. 1-5 favorite properties per client
     * 7. 5-50 view records per published property (40% authenticated, 60% anonymous)
     */
    public function run(): void
    {
        $propertyTypes = PropertyType::all();
        $cities = City::all();

        // Create admin
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@luxuryestate.com',
            'password' => Hash::make('T3st@Secure!99'),
        ]);
        $admin->assignRole('admin');

        // Create agents with profiles
        $agents = collect();
        for ($i = 1; $i <= 8; $i++) {
            $agent = User::factory()->create([
                'email' => "agent{$i}@luxuryestate.com",
                'password' => Hash::make('T3st@Secure!99'),
            ]);
            $agent->assignRole('agent');
            AgentProfile::factory()->featured()->create([
                'user_id' => $agent->id,
            ]);
            $agents->push($agent);
        }

        // Create clients
        $clients = collect();
        for ($i = 1; $i <= 10; $i++) {
            $client = User::factory()->create();
            $client->assignRole('client');
            $clients->push($client);
        }

        // Create properties
        $properties = collect();
        for ($i = 0; $i < 30; $i++) {
            $property = Property::factory()
                ->when($i < 4, fn ($f) => $f->featured())
                ->when($i >= 25, fn ($f) => $f->draft())
                ->when($i >= 20 && $i < 25, fn ($f) => $f->sold())
                ->create([
                    'user_id' => $agents->random()->id,
                    'property_type_id' => $propertyTypes->random()->id,
                    'city_id' => $cities->random()->id,
                ]);
            $properties->push($property);
        }

        // Create inquiries
        $publishedProperties = $properties->where('is_published', true);
        foreach ($publishedProperties->random(min(15, $publishedProperties->count())) as $property) {
            Inquiry::factory()
                ->count(fake()->numberBetween(1, 5))
                ->create([
                    'property_id' => $property->id,
                    'user_id' => fake()->boolean(70) ? $clients->random()->id : null,
                ]);
        }

        // Create favorites
        foreach ($clients as $client) {
            $favoriteProperties = $publishedProperties->random(min(fake()->numberBetween(1, 5), $publishedProperties->count()));
            foreach ($favoriteProperties as $property) {
                Favorite::firstOrCreate([
                    'user_id' => $client->id,
                    'property_id' => $property->id,
                ]);
            }
        }

        // Create property views
        foreach ($publishedProperties as $property) {
            for ($i = 0; $i < fake()->numberBetween(5, 50); $i++) {
                PropertyView::create([
                    'property_id' => $property->id,
                    'user_id' => fake()->boolean(40) ? $clients->random()->id : null,
                    'ip_address' => fake()->ipv4(),
                    'user_agent' => fake()->userAgent(),
                    'viewed_at' => fake()->dateTimeBetween('-30 days', 'now'),
                ]);
            }
        }
    }
}
