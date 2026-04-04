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

class PropertySeeder extends Seeder
{
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
        for ($i = 1; $i <= 5; $i++) {
            $agent = User::factory()->create([
                'email' => "agent{$i}@luxuryestate.com",
                'password' => Hash::make('T3st@Secure!99'),
            ]);
            $agent->assignRole('agent');
            AgentProfile::factory()->create([
                'user_id' => $agent->id,
                'is_featured' => $i <= 4,
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
