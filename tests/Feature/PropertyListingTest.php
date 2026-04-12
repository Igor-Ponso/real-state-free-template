<?php

use App\Models\City;
use App\Models\ListingType;
use App\Models\Property;
use App\Models\PropertyStatus;
use App\Models\PropertyType;
use App\Models\User;
use App\Scopes\PublishedScope;
use Brick\Money\Money;
use Illuminate\Support\Carbon;

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

test('published scope filters unpublished properties globally', function () {
    $agent = User::factory()->create();
    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    Property::factory()->active()->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
        'is_published' => true,
    ]);

    Property::factory()->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
        'is_published' => false,
        'property_status_id' => PropertyStatus::where('slug', 'draft')->first()->id,
    ]);

    // Public query (global scope active) — only published
    expect(Property::count())->toBe(1);

    // Admin query (bypass scope) — sees all
    expect(Property::withoutGlobalScope(PublishedScope::class)->count())->toBe(2);
});

test('properties are ordered by published_at descending', function () {
    $agent = User::factory()->create();
    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    $this->freezeTime();

    Property::factory()->active()->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
        'title' => 'Older Property',
        'published_at' => Carbon::now()->subDays(5),
    ]);

    Property::factory()->active()->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
        'title' => 'Newer Property',
        'published_at' => Carbon::now()->subDay(),
    ]);

    $this->get(route('properties.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('properties.data', 2)
            ->where('properties.data.0.title', 'Newer Property')
            ->where('properties.data.1.title', 'Older Property')
        );
});

test('sort options work correctly', function (string $sort, string $firstTitle) {
    $agent = User::factory()->create();
    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    Property::factory()->active()->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
        'title' => 'Cheap Old',
        'price' => Money::ofMinor(10000000, 'CAD'),
        'published_at' => Carbon::now()->subDays(10),
    ]);

    Property::factory()->active()->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
        'title' => 'Expensive New',
        'price' => Money::ofMinor(90000000, 'CAD'),
        'published_at' => Carbon::now(),
    ]);

    $this->get(route('properties.index', ['sort' => $sort]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('properties.data.0.title', $firstTitle)
        );
})->with([
    'newest first' => ['newest', 'Expensive New'],
    'oldest first' => ['oldest', 'Cheap Old'],
    'price ascending' => ['price_asc', 'Cheap Old'],
    'price descending' => ['price_desc', 'Expensive New'],
]);
