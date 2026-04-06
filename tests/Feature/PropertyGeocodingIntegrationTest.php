<?php

use App\Actions\Property\CreatePropertyAction;
use App\Actions\Property\UpdatePropertyAction;
use App\Models\City;
use App\Models\ListingType;
use App\Models\Property;
use App\Models\PropertyStatus;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Config::set('services.nominatim.enabled', true);
    Cache::flush();

    PropertyStatus::create(['name' => 'Active', 'slug' => 'active', 'sort_order' => 1, 'is_active' => true]);
    ListingType::create(['name' => 'For Sale', 'slug' => 'sale', 'sort_order' => 1, 'is_active' => true]);
});

test('CreatePropertyAction auto-fills lat/lng from Nominatim when not provided', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            ['lat' => '49.2827291', 'lon' => '-123.1207375'],
        ], 200),
    ]);

    $user = User::factory()->create();
    $city = City::factory()->create(['name' => 'Vancouver']);
    $type = PropertyType::factory()->create();
    $status = PropertyStatus::where('slug', 'active')->first();
    $listingType = ListingType::where('slug', 'sale')->first();

    $action = app(CreatePropertyAction::class);

    $property = $action->execute($user, [
        'title' => 'Modern Loft',
        'description' => 'Beautiful loft in downtown Vancouver.',
        'property_type_id' => $type->id,
        'listing_type_id' => $listingType->id,
        'property_status_id' => $status->id,
        'price' => 1500000,
        'currency' => 'CAD',
        'address' => '789 Granville Street',
        'city_id' => $city->id,
        'state' => 'BC',
        'zip_code' => 'V6Z 1K3',
        'bedrooms' => 2,
        'bathrooms' => 2,
        'area_sqft' => 1200,
        'parking_spaces' => 1,
    ]);

    expect((float) $property->latitude)->toBe(49.2827291);
    expect((float) $property->longitude)->toBe(-123.1207375);
    Http::assertSentCount(1);
});

test('CreatePropertyAction does not call geocoder when lat/lng provided', function () {
    Http::fake();

    $user = User::factory()->create();
    $city = City::factory()->create();
    $type = PropertyType::factory()->create();
    $status = PropertyStatus::where('slug', 'active')->first();
    $listingType = ListingType::where('slug', 'sale')->first();

    $action = app(CreatePropertyAction::class);

    $action->execute($user, [
        'title' => 'Known Coordinates',
        'description' => 'Property with manual lat/lng.',
        'property_type_id' => $type->id,
        'listing_type_id' => $listingType->id,
        'property_status_id' => $status->id,
        'price' => 1000000,
        'currency' => 'CAD',
        'address' => '123 Main St',
        'city_id' => $city->id,
        'state' => 'BC',
        'zip_code' => 'V6B 1A1',
        'latitude' => 50.0,
        'longitude' => -120.0,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area_sqft' => 1500,
        'parking_spaces' => 2,
    ]);

    Http::assertNothingSent();
});

test('CreatePropertyAction saves property even when geocoding fails', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response(null, 500),
    ]);

    $user = User::factory()->create();
    $city = City::factory()->create();
    $type = PropertyType::factory()->create();
    $status = PropertyStatus::where('slug', 'active')->first();
    $listingType = ListingType::where('slug', 'sale')->first();

    $action = app(CreatePropertyAction::class);

    $property = $action->execute($user, [
        'title' => 'Geocoding Failed',
        'description' => 'Property where geocoding service is down.',
        'property_type_id' => $type->id,
        'listing_type_id' => $listingType->id,
        'property_status_id' => $status->id,
        'price' => 800000,
        'currency' => 'CAD',
        'address' => '1 Broken API Blvd',
        'city_id' => $city->id,
        'state' => 'BC',
        'zip_code' => 'V6B 1A1',
        'bedrooms' => 2,
        'bathrooms' => 1,
        'area_sqft' => 900,
        'parking_spaces' => 0,
    ]);

    expect($property->exists)->toBeTrue();
    expect($property->latitude)->toBeNull();
});

test('UpdatePropertyAction re-geocodes when address changes', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            ['lat' => '49.28', 'lon' => '-123.12'],
        ], 200),
    ]);

    $user = User::factory()->create();
    $city = City::factory()->create(['name' => 'Vancouver']);
    $type = PropertyType::factory()->create();

    $property = Property::factory()->create([
        'user_id' => $user->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
        'address' => '100 Old Street',
        'latitude' => 50.0,
        'longitude' => -120.0,
    ]);

    $action = app(UpdatePropertyAction::class);

    $action->execute($property, [
        'address' => '200 New Avenue',
    ]);

    expect((float) $property->fresh()->latitude)->toBe(49.28);
    expect((float) $property->fresh()->longitude)->toBe(-123.12);
});

test('UpdatePropertyAction re-geocodes when city changes', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            ['lat' => '43.6532', 'lon' => '-79.3832'],
        ], 200),
    ]);

    $user = User::factory()->create();
    $vancouver = City::factory()->create(['name' => 'Vancouver']);
    $toronto = City::factory()->create(['name' => 'Toronto']);
    $type = PropertyType::factory()->create();

    $property = Property::factory()->create([
        'user_id' => $user->id,
        'city_id' => $vancouver->id,
        'property_type_id' => $type->id,
        'address' => '100 Main Street',
        'latitude' => 49.28,
        'longitude' => -123.12,
    ]);

    $action = app(UpdatePropertyAction::class);

    $action->execute($property, [
        'city_id' => $toronto->id,
    ]);

    expect((float) $property->fresh()->latitude)->toBe(43.6532);
    expect((float) $property->fresh()->longitude)->toBe(-79.3832);
});

test('UpdatePropertyAction does not re-geocode when address unchanged', function () {
    Http::fake();

    $user = User::factory()->create();
    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    $property = Property::factory()->create([
        'user_id' => $user->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
        'address' => '100 Same Street',
    ]);

    $action = app(UpdatePropertyAction::class);

    $action->execute($property, [
        'title' => 'Updated Title Only',
    ]);

    Http::assertNothingSent();
});

test('UpdatePropertyAction respects manual lat/lng over geocoder', function () {
    Http::fake();

    $user = User::factory()->create();
    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    $property = Property::factory()->create([
        'user_id' => $user->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
        'address' => '100 Old Street',
    ]);

    $action = app(UpdatePropertyAction::class);

    $action->execute($property, [
        'address' => '200 New Address',
        'latitude' => 45.123,
        'longitude' => -75.456,
    ]);

    expect((float) $property->fresh()->latitude)->toBe(45.123);
    expect((float) $property->fresh()->longitude)->toBe(-75.456);
    Http::assertNothingSent();
});
