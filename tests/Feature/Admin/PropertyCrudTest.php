<?php

use App\Models\City;
use App\Models\ListingType;
use App\Models\Property;
use App\Models\PropertyStatus;
use App\Models\PropertyType;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    PropertyStatus::create(['name' => 'Active', 'slug' => 'active', 'sort_order' => 1, 'is_active' => true]);
    PropertyStatus::create(['name' => 'Draft', 'slug' => 'draft', 'sort_order' => 2, 'is_active' => true]);
    ListingType::create(['name' => 'For Sale', 'slug' => 'sale', 'sort_order' => 1, 'is_active' => true]);
    ListingType::create(['name' => 'For Rent', 'slug' => 'rental', 'sort_order' => 2, 'is_active' => true]);

    $manageProperties = Permission::create(['name' => 'manage-properties']);
    Permission::create(['name' => 'view-dashboard']);
    Permission::create(['name' => 'manage-inquiries']);

    Role::create(['name' => 'admin'])->givePermissionTo(Permission::all());
    Role::create(['name' => 'agent'])->givePermissionTo($manageProperties);
    Role::create(['name' => 'client']);
});

test('admin can list all properties', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $agent = User::factory()->create();
    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    Property::factory()->featured()->count(3)->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.properties.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Properties/Index')
            ->has('properties.data', 3)
        );
});

test('agent sees only own properties', function () {
    $agent1 = User::factory()->create();
    $agent1->assignRole('agent');
    $agent2 = User::factory()->create();
    $agent2->assignRole('agent');

    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    Property::factory()->featured()->count(2)->create([
        'user_id' => $agent1->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
    ]);

    Property::factory()->featured()->create([
        'user_id' => $agent2->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
    ]);

    $this->actingAs($agent1)
        ->get(route('admin.properties.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('properties.data', 2));
});

test('admin can create a property', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $city = City::factory()->create();
    $type = PropertyType::factory()->create();
    $listingType = ListingType::where('slug', 'sale')->first();
    $status = PropertyStatus::where('slug', 'active')->first();

    $this->actingAs($admin)
        ->post(route('admin.properties.store'), [
            'title' => 'Luxury Penthouse in Downtown',
            'description' => str_repeat('A beautiful luxury penthouse. ', 5),
            'property_type_id' => $type->id,
            'listing_type_id' => $listingType->id,
            'property_status_id' => $status->id,
            'price' => 2500000,
            'currency' => 'CAD',
            'address' => '123 Main St',
            'city_id' => $city->id,
            'state' => 'BC',
            'zip_code' => 'V6B 1A1',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'area_sqft' => 2000,
            'parking_spaces' => 2,
            'is_published' => true,
            'is_featured' => false,
            'pets_allowed' => false,
        ])
        ->assertRedirect();

    expect(Property::count())->toBe(1);
    expect(Property::first()->title)->toBe('Luxury Penthouse in Downtown');
});

test('property creation validates required fields', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->post(route('admin.properties.store'), [])
        ->assertSessionHasErrors(['title', 'description', 'price', 'address', 'bedrooms']);
});

test('admin can update any property', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $agent = User::factory()->create();
    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    $property = Property::factory()->featured()->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
        'title' => 'Original Title',
    ]);

    $this->actingAs($admin)
        ->put(route('admin.properties.update', $property), [
            'title' => 'Updated Title',
            'description' => str_repeat('Updated description. ', 5),
            'property_type_id' => $type->id,
            'listing_type_id' => $property->listing_type_id,
            'property_status_id' => $property->property_status_id,
            'price' => 3000000,
            'currency' => 'CAD',
            'address' => $property->address,
            'city_id' => $city->id,
            'state' => $property->state,
            'zip_code' => $property->zip_code,
            'bedrooms' => $property->bedrooms,
            'bathrooms' => $property->bathrooms,
            'area_sqft' => $property->area_sqft,
            'parking_spaces' => $property->parking_spaces,
            'is_published' => true,
            'is_featured' => false,
            'pets_allowed' => false,
        ])
        ->assertRedirect();

    expect($property->fresh()->title)->toBe('Updated Title');
});

test('agent cannot update another agents property', function () {
    $agent1 = User::factory()->create();
    $agent1->assignRole('agent');
    $agent2 = User::factory()->create();

    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    $property = Property::factory()->featured()->create([
        'user_id' => $agent2->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
    ]);

    $this->actingAs($agent1)
        ->put(route('admin.properties.update', $property), ['title' => 'Hack'])
        ->assertForbidden();
});

test('admin can soft delete a property', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    $property = Property::factory()->featured()->create([
        'user_id' => $admin->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.properties.destroy', $property))
        ->assertRedirect(route('admin.properties.index'));

    expect($property->fresh()->trashed())->toBeTrue();
});

test('client cannot access property management', function () {
    $client = User::factory()->create();
    $client->assignRole('client');

    $this->actingAs($client)
        ->get(route('admin.properties.index'))
        ->assertForbidden();
});

test('price above max is rejected by ValidMoney', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $city = City::factory()->create();
    $type = PropertyType::factory()->create();
    $listingType = ListingType::where('slug', 'sale')->first();
    $status = PropertyStatus::where('slug', 'active')->first();

    $this->actingAs($admin)
        ->post(route('admin.properties.store'), [
            'title' => 'Too Expensive',
            'description' => str_repeat('A luxury mansion. ', 5),
            'property_type_id' => $type->id,
            'listing_type_id' => $listingType->id,
            'property_status_id' => $status->id,
            'price' => 100_000_000, // $100M — above $50M max
            'currency' => 'CAD',
            'address' => '1 Billion Dollar Lane',
            'city_id' => $city->id,
            'state' => 'BC',
            'zip_code' => 'V6B 1A1',
            'bedrooms' => 10,
            'bathrooms' => 10,
            'area_sqft' => 20000,
            'parking_spaces' => 10,
            'is_published' => true,
        ])
        ->assertSessionHasErrors('price');
});

test('negative price is rejected', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $city = City::factory()->create();
    $type = PropertyType::factory()->create();
    $listingType = ListingType::where('slug', 'sale')->first();
    $status = PropertyStatus::where('slug', 'active')->first();

    $this->actingAs($admin)
        ->post(route('admin.properties.store'), [
            'title' => 'Negative Price',
            'description' => str_repeat('Invalid price. ', 5),
            'property_type_id' => $type->id,
            'listing_type_id' => $listingType->id,
            'property_status_id' => $status->id,
            'price' => -100,
            'currency' => 'CAD',
            'address' => '1 Main St',
            'city_id' => $city->id,
            'state' => 'BC',
            'zip_code' => 'V6B 1A1',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'area_sqft' => 1500,
            'parking_spaces' => 1,
            'is_published' => true,
        ])
        ->assertSessionHasErrors('price');
});

test('deposit above max is rejected', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $city = City::factory()->create();
    $type = PropertyType::factory()->create();
    $listingType = ListingType::where('slug', 'rental')->first();
    $status = PropertyStatus::where('slug', 'active')->first();

    $this->actingAs($admin)
        ->post(route('admin.properties.store'), [
            'title' => 'Rental with Huge Deposit',
            'description' => str_repeat('A nice rental. ', 5),
            'property_type_id' => $type->id,
            'listing_type_id' => $listingType->id,
            'property_status_id' => $status->id,
            'price' => 5000,
            'currency' => 'CAD',
            'deposit' => 2_000_000, // $2M — above $1M max
            'address' => '1 Main St',
            'city_id' => $city->id,
            'state' => 'BC',
            'zip_code' => 'V6B 1A1',
            'bedrooms' => 2,
            'bathrooms' => 1,
            'area_sqft' => 800,
            'parking_spaces' => 0,
            'is_published' => true,
        ])
        ->assertSessionHasErrors('deposit');
});
