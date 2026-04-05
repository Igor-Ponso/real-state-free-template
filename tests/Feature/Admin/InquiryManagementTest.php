<?php

use App\Models\City;
use App\Models\Inquiry;
use App\Models\InquiryStatus;
use App\Models\ListingType;
use App\Models\Property;
use App\Models\PropertyStatus;
use App\Models\PropertyType;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    PropertyStatus::create(['name' => 'Active', 'slug' => 'active', 'sort_order' => 1, 'is_active' => true]);
    ListingType::create(['name' => 'For Sale', 'slug' => 'sale', 'sort_order' => 1, 'is_active' => true]);
    InquiryStatus::create(['name' => 'New', 'slug' => 'new', 'sort_order' => 0, 'is_active' => true]);
    InquiryStatus::create(['name' => 'Read', 'slug' => 'read', 'sort_order' => 1, 'is_active' => true]);
    InquiryStatus::create(['name' => 'Replied', 'slug' => 'replied', 'sort_order' => 2, 'is_active' => true]);

    $manageInquiries = Permission::create(['name' => 'manage-inquiries']);
    Permission::create(['name' => 'manage-properties']);
    Permission::create(['name' => 'view-dashboard']);

    Role::create(['name' => 'admin'])->givePermissionTo(Permission::all());
    Role::create(['name' => 'agent'])->givePermissionTo($manageInquiries);
    Role::create(['name' => 'client']);
});

test('admin can list all inquiries', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $property = createTestProperty();
    Inquiry::factory()->count(3)->create(['property_id' => $property->id]);

    $this->actingAs($admin)
        ->get(route('admin.inquiries.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Inquiries/Index')
            ->has('inquiries.data', 3)
        );
});

test('agent sees only inquiries for own properties', function () {
    $agent = User::factory()->create();
    $agent->assignRole('agent');

    $ownProperty = createTestProperty($agent);
    $otherProperty = createTestProperty();

    Inquiry::factory()->count(2)->create(['property_id' => $ownProperty->id]);
    Inquiry::factory()->create(['property_id' => $otherProperty->id]);

    $this->actingAs($agent)
        ->get(route('admin.inquiries.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('inquiries.data', 2));
});

test('inquiry status can be updated', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $property = createTestProperty();
    $inquiry = Inquiry::factory()->create(['property_id' => $property->id]);
    $readStatus = InquiryStatus::where('slug', 'read')->first();

    $this->actingAs($admin)
        ->put(route('admin.inquiries.update', $inquiry), [
            'inquiry_status_id' => $readStatus->id,
        ])
        ->assertRedirect();

    expect($inquiry->fresh()->inquiryStatus->slug)->toBe('read');
});

test('client cannot access inquiry management', function () {
    $client = User::factory()->create();
    $client->assignRole('client');

    $this->actingAs($client)
        ->get(route('admin.inquiries.index'))
        ->assertForbidden();
});

/**
 * Create a published property with all required relationships.
 */
function createTestProperty(?User $agent = null): Property
{
    $agent ??= User::factory()->create();
    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    return Property::factory()->featured()->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
    ]);
}
