<?php

use App\Models\City;
use App\Models\ListingType;
use App\Models\Property;
use App\Models\PropertyStatus;
use App\Models\PropertyType;
use App\Models\User;
use App\Services\PropertyQueryService;
use Brick\Money\Money;

beforeEach(function () {
    PropertyStatus::create(['name' => 'Active', 'slug' => 'active', 'sort_order' => 1, 'is_active' => true]);
    PropertyStatus::create(['name' => 'Draft', 'slug' => 'draft', 'sort_order' => 2, 'is_active' => true]);
    PropertyStatus::create(['name' => 'Sold', 'slug' => 'sold', 'sort_order' => 3, 'is_active' => true]);
    PropertyStatus::create(['name' => 'Rented', 'slug' => 'rented', 'sort_order' => 4, 'is_active' => true]);
    ListingType::create(['name' => 'For Sale', 'slug' => 'sale', 'sort_order' => 1, 'is_active' => true]);
    ListingType::create(['name' => 'For Rent', 'slug' => 'rental', 'sort_order' => 2, 'is_active' => true]);

    $this->service = app(PropertyQueryService::class);
    $this->agent = User::factory()->create();
    $this->city = City::factory()->create(['name' => 'Vancouver', 'slug' => 'vancouver']);
    $this->type = PropertyType::factory()->create(['name' => 'House', 'slug' => 'house']);
});

it('returns paginated published properties with no filters', function () {
    Property::factory()->active()->count(3)->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    Property::factory()->draft()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    $result = $this->service->filteredListing([]);

    expect($result)->toHaveCount(3)
        ->and($result->total())->toBe(3);
});

it('filters by property type slug', function () {
    $condo = PropertyType::factory()->create(['name' => 'Condo', 'slug' => 'condo']);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $condo->id,
    ]);

    $result = $this->service->filteredListing(['type' => 'house']);

    expect($result)->toHaveCount(1)
        ->and($result->first()->property_type_id)->toBe($this->type->id);
});

it('filters by multiple property type slugs', function () {
    $condo = PropertyType::factory()->create(['name' => 'Condo', 'slug' => 'condo']);
    $villa = PropertyType::factory()->create(['name' => 'Villa', 'slug' => 'villa']);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $condo->id,
    ]);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $villa->id,
    ]);

    $result = $this->service->filteredListing(['type' => ['house', 'condo']]);

    expect($result)->toHaveCount(2);
});

it('filters by city slug', function () {
    $toronto = City::factory()->create(['name' => 'Toronto', 'slug' => 'toronto']);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $toronto->id,
        'property_type_id' => $this->type->id,
    ]);

    $result = $this->service->filteredListing(['city' => 'vancouver']);

    expect($result)->toHaveCount(1)
        ->and($result->first()->city_id)->toBe($this->city->id);
});

it('filters by listing type slug', function () {
    Property::factory()->active()->forSale()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    Property::factory()->active()->forRent()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    $result = $this->service->filteredListing(['listing' => 'sale']);

    expect($result)->toHaveCount(1);
});

it('filters by minimum bedrooms', function () {
    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'bedrooms' => 2,
    ]);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'bedrooms' => 4,
    ]);

    $result = $this->service->filteredListing(['bedrooms' => '3']);

    expect($result)->toHaveCount(1)
        ->and($result->first()->bedrooms)->toBe(4);
});

it('filters by exact bedrooms when bedrooms_exact is set', function () {
    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'bedrooms' => 2,
    ]);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'bedrooms' => 3,
    ]);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'bedrooms' => 4,
    ]);

    $result = $this->service->filteredListing([
        'bedrooms' => ['2', '4'],
        'bedrooms_exact' => true,
    ]);

    expect($result)->toHaveCount(2)
        ->and($result->pluck('bedrooms')->sort()->values()->all())->toBe([2, 4]);
});

it('filters by minimum bathrooms', function () {
    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'bathrooms' => 1.5,
    ]);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'bathrooms' => 3.0,
    ]);

    $result = $this->service->filteredListing(['bathrooms' => '2']);

    expect($result)->toHaveCount(1)
        ->and((float) $result->first()->bathrooms)->toBe(3.0);
});

it('filters by exact bathrooms when bathrooms_exact is set', function () {
    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'bathrooms' => 1.5,
    ]);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'bathrooms' => 2.0,
    ]);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'bathrooms' => 3.5,
    ]);

    $result = $this->service->filteredListing([
        'bathrooms' => ['1.5', '3.5'],
        'bathrooms_exact' => true,
    ]);

    expect($result)->toHaveCount(2);
});

it('filters by price range', function () {
    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'price' => Money::ofMinor(10000000, 'CAD'),
    ]);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'price' => Money::ofMinor(50000000, 'CAD'),
    ]);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'price' => Money::ofMinor(90000000, 'CAD'),
    ]);

    $result = $this->service->filteredListing([
        'min_price' => 20000000,
        'max_price' => 60000000,
    ]);

    expect($result)->toHaveCount(1)
        ->and($result->first()->price->getMinorAmount()->toInt())->toBe(50000000);
});

it('filters by min_price only', function () {
    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'price' => Money::ofMinor(10000000, 'CAD'),
    ]);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'price' => Money::ofMinor(50000000, 'CAD'),
    ]);

    $result = $this->service->filteredListing(['min_price' => 30000000]);

    expect($result)->toHaveCount(1);
});

it('filters by unit amenities', function () {
    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'unit_amenities' => ['balcony', 'den', 'fireplace'],
    ]);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'unit_amenities' => ['den', 'fireplace'],
    ]);

    $result = $this->service->filteredListing(['unit_amenities' => ['balcony']]);

    expect($result)->toHaveCount(1)
        ->and($result->first()->unit_amenities)->toContain('balcony');
});

it('filters by multiple unit amenities requiring all', function () {
    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'unit_amenities' => ['balcony', 'den', 'fireplace'],
    ]);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'unit_amenities' => ['balcony', 'fireplace'],
    ]);

    $result = $this->service->filteredListing(['unit_amenities' => ['balcony', 'den']]);

    expect($result)->toHaveCount(1);
});

it('filters by building amenities', function () {
    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'building_amenities' => ['pool', 'gym', 'concierge'],
    ]);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'building_amenities' => ['gym', 'elevator'],
    ]);

    $result = $this->service->filteredListing(['building_amenities' => ['pool']]);

    expect($result)->toHaveCount(1)
        ->and($result->first()->building_amenities)->toContain('pool');
});

it('sorts by price ascending', function () {
    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'price' => Money::ofMinor(50000000, 'CAD'),
    ]);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'price' => Money::ofMinor(10000000, 'CAD'),
    ]);

    $result = $this->service->filteredListing(['sort' => 'price_asc']);

    expect($result->first()->price->getMinorAmount()->toInt())->toBe(10000000)
        ->and($result->last()->price->getMinorAmount()->toInt())->toBe(50000000);
});

it('sorts by price descending', function () {
    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'price' => Money::ofMinor(10000000, 'CAD'),
    ]);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'price' => Money::ofMinor(50000000, 'CAD'),
    ]);

    $result = $this->service->filteredListing(['sort' => 'price_desc']);

    expect($result->first()->price->getMinorAmount()->toInt())->toBe(50000000)
        ->and($result->last()->price->getMinorAmount()->toInt())->toBe(10000000);
});

it('sorts by oldest published_at', function () {
    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'published_at' => now()->subDays(10),
        'title' => 'Older Property',
    ]);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'published_at' => now(),
        'title' => 'Newer Property',
    ]);

    $result = $this->service->filteredListing(['sort' => 'oldest']);

    expect($result->first()->title)->toBe('Older Property');
});

it('defaults to latest published_at when no sort specified', function () {
    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'published_at' => now()->subDays(10),
        'title' => 'Older Property',
    ]);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'published_at' => now(),
        'title' => 'Newer Property',
    ]);

    $result = $this->service->filteredListing([]);

    expect($result->first()->title)->toBe('Newer Property');
});

it('respects perPage parameter', function () {
    Property::factory()->active()->count(5)->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    $result = $this->service->filteredListing([], 2);

    expect($result)->toHaveCount(2)
        ->and($result->total())->toBe(5)
        ->and($result->lastPage())->toBe(3);
});

it('clamps perPage to minimum of 1', function () {
    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    $result = $this->service->filteredListing([], 0);

    expect($result)->toHaveCount(1);
});

it('clamps perPage to maximum of 48', function () {
    Property::factory()->active()->count(50)->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    $result = $this->service->filteredListing([], 100);

    expect($result)->toHaveCount(48);
});

it('combines multiple filters together', function () {
    $toronto = City::factory()->create(['name' => 'Toronto', 'slug' => 'toronto']);
    $condo = PropertyType::factory()->create(['name' => 'Condo', 'slug' => 'condo']);

    // Matches all filters: house, vancouver, for sale, 3+ bedrooms, price in range
    Property::factory()->active()->forSale()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'bedrooms' => 4,
        'price' => Money::ofMinor(50000000, 'CAD'),
    ]);

    // Wrong city
    Property::factory()->active()->forSale()->create([
        'user_id' => $this->agent->id,
        'city_id' => $toronto->id,
        'property_type_id' => $this->type->id,
        'bedrooms' => 4,
        'price' => Money::ofMinor(50000000, 'CAD'),
    ]);

    // Wrong type
    Property::factory()->active()->forSale()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $condo->id,
        'bedrooms' => 4,
        'price' => Money::ofMinor(50000000, 'CAD'),
    ]);

    // Too few bedrooms
    Property::factory()->active()->forSale()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'bedrooms' => 1,
        'price' => Money::ofMinor(50000000, 'CAD'),
    ]);

    $result = $this->service->filteredListing([
        'type' => 'house',
        'city' => 'vancouver',
        'listing' => 'sale',
        'bedrooms' => '3',
        'min_price' => 40000000,
        'max_price' => 60000000,
    ]);

    expect($result)->toHaveCount(1);
});

it('excludes draft and non-published properties', function () {
    Property::factory()->draft()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
        'is_published' => false,
    ]);

    $result = $this->service->filteredListing([]);

    expect($result)->toHaveCount(0);
});

it('eager loads city, listingType, propertyType, and media relationships', function () {
    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    $result = $this->service->filteredListing([]);

    $property = $result->first();

    expect($property->relationLoaded('city'))->toBeTrue()
        ->and($property->relationLoaded('listingType'))->toBeTrue()
        ->and($property->relationLoaded('propertyType'))->toBeTrue()
        ->and($property->relationLoaded('media'))->toBeTrue();
});

// --- similarProperties ---

it('returns properties in the same city excluding the given property', function () {
    $property = Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    $similar1 = Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    $similar2 = Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    $result = $this->service->similarProperties($property);

    expect($result)->toHaveCount(2)
        ->and($result->pluck('id')->toArray())->not->toContain($property->id)
        ->and($result->pluck('id')->toArray())->toContain($similar1->id, $similar2->id);
});

it('does not return properties from a different city', function () {
    $toronto = City::factory()->create(['name' => 'Toronto', 'slug' => 'toronto']);

    $property = Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $toronto->id,
        'property_type_id' => $this->type->id,
    ]);

    $result = $this->service->similarProperties($property);

    expect($result)->toHaveCount(0);
});

it('respects the limit parameter for similar properties', function () {
    $property = Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    Property::factory()->active()->count(6)->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    $result = $this->service->similarProperties($property, 2);

    expect($result)->toHaveCount(2);
});

it('eager loads relationships on similar properties', function () {
    $property = Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    $result = $this->service->similarProperties($property);

    $similar = $result->first();

    expect($similar->relationLoaded('city'))->toBeTrue()
        ->and($similar->relationLoaded('listingType'))->toBeTrue()
        ->and($similar->relationLoaded('propertyType'))->toBeTrue()
        ->and($similar->relationLoaded('media'))->toBeTrue();
});

it('only returns published similar properties', function () {
    $property = Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    Property::factory()->draft()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    $result = $this->service->similarProperties($property);

    expect($result)->toHaveCount(1);
});

// --- resolveMapCenter ---

it('returns city coordinates when city filter is applied', function () {
    $result = $this->service->resolveMapCenter(['vancouver']);

    expect($result)->toBeArray()
        ->and($result[0])->toBe((float) $this->city->latitude)
        ->and($result[1])->toBe((float) $this->city->longitude);
});

it('returns coordinates of the first city slug when multiple provided', function () {
    $toronto = City::factory()->create(['name' => 'Toronto', 'slug' => 'toronto']);

    $result = $this->service->resolveMapCenter(['vancouver', 'toronto']);

    expect($result[0])->toBe((float) $this->city->latitude)
        ->and($result[1])->toBe((float) $this->city->longitude);
});

it('returns null when city slugs array is empty', function () {
    $result = $this->service->resolveMapCenter([]);

    expect($result)->toBeNull();
});

it('returns null when city slug does not exist', function () {
    $result = $this->service->resolveMapCenter(['nonexistent-city']);

    expect($result)->toBeNull();
});
