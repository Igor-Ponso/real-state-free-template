<?php

use App\Models\City;
use App\Models\ListingType;
use App\Models\Property;
use App\Models\PropertyStatus;
use App\Models\PropertyType;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Browser\visit;

beforeEach(function () {
    PropertyStatus::create(['name' => 'Active', 'slug' => 'active', 'sort_order' => 1, 'is_active' => true]);
    ListingType::create(['name' => 'For Sale', 'slug' => 'sale', 'sort_order' => 1, 'is_active' => true]);

    $type = PropertyType::factory()->create();
    $city = City::factory()->create();
    $agent = User::factory()->create();

    Property::factory()->active()->create([
        'user_id' => $agent->id,
        'property_type_id' => $type->id,
        'city_id' => $city->id,
    ]);

    $this->property = Property::first();
});

test('public pages load without JS errors', function () {
    visit([
        '/',
        '/properties',
        "/properties/{$this->property->slug}",
    ])->assertNoSmoke();
});

test('auth pages load without JS errors', function () {
    visit([
        '/login',
        '/register',
        '/forgot-password',
    ])->assertNoSmoke();
});

test('admin pages load without JS errors', function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin);

    visit([
        '/admin/dashboard',
        '/admin/properties',
        '/admin/properties/create',
        "/admin/properties/{$this->property->slug}/edit",
        '/admin/inquiries',
    ])->assertNoSmoke();
});

test('settings pages load without JS errors', function () {
    Role::create(['name' => 'client', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('client');
    $user->markEmailAsVerified();

    $this->actingAs($user);

    visit([
        '/dashboard',
        '/settings/profile',
        '/settings/appearance',
    ])->assertNoSmoke();
});
