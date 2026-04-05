<?php

use App\Models\InquiryStatus;
use App\Models\ListingType;
use App\Models\PropertyStatus;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    PropertyStatus::create(['name' => 'Active', 'slug' => 'active', 'sort_order' => 1, 'is_active' => true]);
    ListingType::create(['name' => 'For Sale', 'slug' => 'sale', 'sort_order' => 1, 'is_active' => true]);
    InquiryStatus::create(['name' => 'New', 'slug' => 'new', 'sort_order' => 0, 'is_active' => true]);

    $viewDashboard = Permission::create(['name' => 'view-dashboard']);
    $manageProperties = Permission::create(['name' => 'manage-properties']);
    $manageInquiries = Permission::create(['name' => 'manage-inquiries']);

    Role::create(['name' => 'admin'])->givePermissionTo([$viewDashboard, $manageProperties, $manageInquiries]);
    Role::create(['name' => 'agent'])->givePermissionTo([$viewDashboard, $manageProperties, $manageInquiries]);
    Role::create(['name' => 'client']);
});

test('admin can access dashboard', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->has('stats')
            ->where('stats.total_properties', 0)
        );
});

test('agent can access dashboard', function () {
    $agent = User::factory()->create();
    $agent->assignRole('agent');

    $this->actingAs($agent)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

test('client cannot access dashboard', function () {
    $client = User::factory()->create();
    $client->assignRole('client');

    $this->actingAs($client)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('guest is redirected to login', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect('/login');
});
