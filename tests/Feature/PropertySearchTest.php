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
});

test('search returns matching published properties', function () {
    $user = User::factory()->create();
    $city = City::factory()->create(['name' => 'Vancouver']);
    $type = PropertyType::factory()->create();

    Property::factory()->featured()->create([
        'user_id' => $user->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
        'title' => 'Luxury Penthouse in Downtown',
    ]);

    Property::factory()->featured()->create([
        'user_id' => $user->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
        'title' => 'Cozy Studio Apartment',
    ]);

    $this->getJson(route('properties.search', ['q' => 'Penthouse']))
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['title' => 'Luxury Penthouse in Downtown']);
});

test('search excludes unpublished properties', function () {
    $user = User::factory()->create();
    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    Property::factory()->draft()->create([
        'user_id' => $user->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
        'title' => 'Hidden Draft Property',
    ]);

    $this->getJson(route('properties.search', ['q' => 'Hidden']))
        ->assertOk()
        ->assertJsonCount(0);
});

test('search requires minimum 2 characters', function () {
    $this->getJson(route('properties.search', ['q' => 'a']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('q');
});

test('search requires query parameter', function () {
    $this->getJson(route('properties.search'))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('q');
});

test('search limits results to 5', function () {
    $user = User::factory()->create();
    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    Property::factory()->featured()->count(8)->create([
        'user_id' => $user->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
        'title' => 'Luxury Villa by the Sea',
    ]);

    $this->getJson(route('properties.search', ['q' => 'Luxury']))
        ->assertOk()
        ->assertJsonCount(5);
});

test('search returns expected json structure', function () {
    $user = User::factory()->create();
    $city = City::factory()->create(['name' => 'Vancouver']);
    $type = PropertyType::factory()->create();

    Property::factory()->featured()->create([
        'user_id' => $user->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
        'title' => 'Modern Loft Space',
    ]);

    $this->getJson(route('properties.search', ['q' => 'Loft']))
        ->assertOk()
        ->assertJsonStructure([
            '*' => ['id', 'title', 'slug', 'location', 'price', 'bedrooms', 'bathrooms', 'image'],
        ]);
});

test('search is case insensitive', function () {
    $user = User::factory()->create();
    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    Property::factory()->featured()->create([
        'user_id' => $user->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
        'title' => 'Oceanfront Estate',
    ]);

    $this->getJson(route('properties.search', ['q' => 'oceanfront']))
        ->assertOk()
        ->assertJsonCount(1);
});
