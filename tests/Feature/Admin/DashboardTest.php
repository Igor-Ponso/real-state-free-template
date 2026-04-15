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

test('admin dashboard exposes admin-scoped stats and chart', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->where('role', 'admin')
            ->has('stats.total_agents')
            ->has('stats.total_clients')
            ->has('chart.labels', 30)
            ->has('chart.data', 30)
        );
});

test('agent sees only own listings in dashboard stats', function () {
    $agent1 = User::factory()->create();
    $agent1->assignRole('agent');
    $agent2 = User::factory()->create();
    $agent2->assignRole('agent');

    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    // agent1 owns 3 properties, agent2 owns 2
    Property::factory()->count(3)->create([
        'user_id' => $agent1->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
    ]);
    Property::factory()->count(2)->create([
        'user_id' => $agent2->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
    ]);

    $this->actingAs($agent1)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('role', 'agent')
            ->where('stats.total_properties', 3)
            ->has('stats.response_rate')
            ->missing('stats.total_agents')
        );
});

test('agent response rate reflects only own inquiries', function () {
    $agent = User::factory()->create();
    $agent->assignRole('agent');

    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    $property = Property::factory()->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
    ]);

    $status = InquiryStatus::first();

    // 4 inquiries, 1 replied → 25%
    Inquiry::factory()->count(3)->create([
        'property_id' => $property->id,
        'inquiry_status_id' => $status->id,
    ]);
    Inquiry::factory()->create([
        'property_id' => $property->id,
        'inquiry_status_id' => $status->id,
        'replied_at' => now(),
    ]);

    $this->actingAs($agent)
        ->get(route('admin.dashboard'))
        ->assertInertia(fn ($page) => $page
            ->where('stats.response_rate', 25)
        );
});
