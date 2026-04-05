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

        // Create properties (general, across all cities)
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

        // Vancouver map test data — 30 properties spread across real neighborhoods
        $vancouver = City::where('slug', 'vancouver')->first();
        if ($vancouver) {
            $vancouverCoords = [
                ['lat' => 49.2827, 'lng' => -123.1207, 'hood' => 'Downtown'],
                ['lat' => 49.2868, 'lng' => -123.1323, 'hood' => 'Coal Harbour'],
                ['lat' => 49.2744, 'lng' => -123.1215, 'hood' => 'Yaletown'],
                ['lat' => 49.2725, 'lng' => -123.1002, 'hood' => 'Mount Pleasant'],
                ['lat' => 49.2685, 'lng' => -123.1537, 'hood' => 'Kitsilano'],
                ['lat' => 49.2632, 'lng' => -123.1363, 'hood' => 'Fairview'],
                ['lat' => 49.2591, 'lng' => -123.1656, 'hood' => 'Point Grey'],
                ['lat' => 49.2488, 'lng' => -123.2297, 'hood' => 'UBC'],
                ['lat' => 49.2838, 'lng' => -123.1088, 'hood' => 'Gastown'],
                ['lat' => 49.2811, 'lng' => -123.0986, 'hood' => 'Strathcona'],
                ['lat' => 49.2940, 'lng' => -123.0796, 'hood' => 'Hastings-Sunrise'],
                ['lat' => 49.2632, 'lng' => -123.0784, 'hood' => 'Main Street'],
                ['lat' => 49.2536, 'lng' => -123.0647, 'hood' => 'South Vancouver'],
                ['lat' => 49.2478, 'lng' => -123.1196, 'hood' => 'Oakridge'],
                ['lat' => 49.2386, 'lng' => -123.0651, 'hood' => 'Killarney'],
                ['lat' => 49.2511, 'lng' => -123.0445, 'hood' => 'Champlain Heights'],
                ['lat' => 49.2266, 'lng' => -123.0057, 'hood' => 'River District'],
                ['lat' => 49.2660, 'lng' => -123.1839, 'hood' => 'West Point Grey'],
                ['lat' => 49.2540, 'lng' => -123.1448, 'hood' => 'Shaughnessy'],
                ['lat' => 49.2481, 'lng' => -123.1623, 'hood' => 'Kerrisdale'],
                ['lat' => 49.2399, 'lng' => -123.1516, 'hood' => 'Marpole'],
                ['lat' => 49.2762, 'lng' => -123.0691, 'hood' => 'Grandview-Woodland'],
                ['lat' => 49.2558, 'lng' => -123.1001, 'hood' => 'Riley Park'],
                ['lat' => 49.2438, 'lng' => -123.0848, 'hood' => 'Sunset'],
                ['lat' => 49.2690, 'lng' => -123.0415, 'hood' => 'Renfrew-Collingwood'],
                ['lat' => 49.2305, 'lng' => -123.0264, 'hood' => 'Victoria-Fraserview'],
                ['lat' => 49.2885, 'lng' => -123.0452, 'hood' => 'Hastings Park'],
                ['lat' => 49.2763, 'lng' => -123.1392, 'hood' => 'West End'],
                ['lat' => 49.2710, 'lng' => -123.1423, 'hood' => 'South Granville'],
                ['lat' => 49.2577, 'lng' => -123.1223, 'hood' => 'Cambie Village'],
            ];

            foreach ($vancouverCoords as $i => $coord) {
                $property = Property::factory()
                    ->when($i < 2, fn ($f) => $f->featured())
                    ->when($i >= 27, fn ($f) => $f->forRent())
                    ->when($i < 27, fn ($f) => $f->forSale())
                    ->create([
                        'user_id' => $agents->random()->id,
                        'property_type_id' => $propertyTypes->random()->id,
                        'city_id' => $vancouver->id,
                        'neighborhood' => $coord['hood'],
                        'latitude' => $coord['lat'],
                        'longitude' => $coord['lng'],
                        'state' => 'BC',
                    ]);
                $properties->push($property);
            }
        }

        // One property without images — tests the "No photos" fallback
        $properties->push(Property::factory()->withoutImages()->create([
            'user_id' => $agents->random()->id,
            'title' => 'New Development — Photos Coming Soon',
            'property_type_id' => $propertyTypes->random()->id,
            'city_id' => $cities->random()->id,
        ]));

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
