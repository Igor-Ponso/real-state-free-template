<?php

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

test('property listing page can be rendered', function () {
    $this->get(route('properties.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Properties/Index')
            ->has('properties.data')
            ->has('appliedFilters')
        );
});

test('property listing shows only published properties', function () {
    $agent = User::factory()->create();
    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    Property::factory()->featured()->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
        'title' => 'Published Property',
    ]);

    Property::factory()->draft()->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
        'title' => 'Draft Property',
    ]);

    $this->get(route('properties.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('properties.data', 1)
        );
});

test('property listing can be filtered by city', function () {
    $agent = User::factory()->create();
    $type = PropertyType::factory()->create();
    $vancouver = City::factory()->create(['name' => 'Vancouver', 'slug' => 'vancouver']);
    $toronto = City::factory()->create(['name' => 'Toronto', 'slug' => 'toronto']);

    Property::factory()->featured()->create([
        'user_id' => $agent->id,
        'city_id' => $vancouver->id,
        'property_type_id' => $type->id,
    ]);

    Property::factory()->featured()->create([
        'user_id' => $agent->id,
        'city_id' => $toronto->id,
        'property_type_id' => $type->id,
    ]);

    $this->get(route('properties.index', ['city' => 'vancouver']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('properties.data', 1)
        );
});

test('property listing can be filtered by property type', function () {
    $agent = User::factory()->create();
    $city = City::factory()->create();
    $house = PropertyType::factory()->create(['name' => 'House', 'slug' => 'house']);
    $condo = PropertyType::factory()->create(['name' => 'Condo', 'slug' => 'condo']);

    Property::factory()->featured()->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $house->id,
    ]);

    Property::factory()->featured()->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $condo->id,
    ]);

    $this->get(route('properties.index', ['type' => 'house']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('properties.data', 1)
        );
});

test('property listing paginates results', function () {
    $agent = User::factory()->create();
    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    Property::factory()->featured()->count(15)->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
    ]);

    $this->get(route('properties.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('properties.data', 12)
            ->where('properties.meta.total', 15)
            ->where('properties.meta.last_page', 2)
        );
});
