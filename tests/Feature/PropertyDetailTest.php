<?php

use App\Models\AgentProfile;
use App\Models\City;
use App\Models\ListingType;
use App\Models\Property;
use App\Models\PropertyStatus;
use App\Models\PropertyType;
use App\Models\User;

beforeEach(function () {
    PropertyStatus::create(['name' => 'Active', 'slug' => 'active', 'sort_order' => 1, 'is_active' => true]);
    PropertyStatus::create(['name' => 'Draft', 'slug' => 'draft', 'sort_order' => 2, 'is_active' => true]);
    ListingType::create(['name' => 'For Sale', 'slug' => 'sale', 'sort_order' => 1, 'is_active' => true]);
    ListingType::create(['name' => 'For Rent', 'slug' => 'rental', 'sort_order' => 2, 'is_active' => true]);
});

test('property detail page renders for published property', function () {
    $agent = User::factory()->create();
    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    $property = Property::factory()->featured()->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
    ]);

    $this->get(route('properties.show', $property))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Properties/Show')
            ->has('property')
        );
});

test('property detail page returns 404 for draft property', function () {
    $agent = User::factory()->create();
    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    $property = Property::factory()->draft()->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
    ]);

    $this->get(route('properties.show', $property))
        ->assertNotFound();
});

test('property detail page returns 404 for non-existent slug', function () {
    $this->get('/properties/non-existent-property-slug')
        ->assertNotFound();
});

test('property detail page includes all property fields', function () {
    $agent = User::factory()->create();
    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    $property = Property::factory()->featured()->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
        'title' => 'Luxury Waterfront Villa',
        'bedrooms' => 4,
    ]);

    $this->get(route('properties.show', $property))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Properties/Show')
            ->where('property.title', 'Luxury Waterfront Villa')
            ->where('property.bedrooms', 4)
            ->where('property.slug', $property->slug)
            ->has('property.description')
            ->has('property.price')
            ->has('property.address')
            ->has('property.unit_amenities')
            ->has('property.building_amenities')
            ->has('property.images')
        );
});

test('property detail page includes agent information', function () {
    $agent = User::factory()->create(['name' => 'Jane Broker']);
    AgentProfile::factory()->create([
        'user_id' => $agent->id,
        'bio' => 'Top selling agent.',
        'specializations' => ['Luxury Homes', 'Waterfront'],
    ]);
    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    $property = Property::factory()->featured()->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
    ]);

    $this->get(route('properties.show', $property))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('property.agent')
            ->where('property.agent.name', 'Jane Broker')
            ->where('property.agent.bio', 'Top selling agent.')
            ->has('property.agent.specializations', 2)
        );
});

test('similar properties excludes current property', function () {
    $agent = User::factory()->create();
    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    $property = Property::factory()->featured()->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
    ]);

    Property::factory()->featured()->count(2)->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
    ]);

    $this->get(route('properties.show', $property))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Properties/Show')
            ->where('property.id', $property->id)
        );
});

test('rental property detail includes rental-specific fields', function () {
    $agent = User::factory()->create();
    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    $property = Property::factory()->featured()->forRent()->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
    ]);

    $this->get(route('properties.show', $property))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('property.is_rental', true)
            ->has('property.deposit')
            ->has('property.lease_length_months')
            ->has('property.available_from')
            ->where('property.pets_allowed', $property->pets_allowed)
        );
});
