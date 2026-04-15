<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::create(['name' => 'view-dashboard']);
    Permission::create(['name' => 'manage-properties']);

    Role::create(['name' => 'admin'])->givePermissionTo(Permission::all());
    Role::create(['name' => 'agent'])->givePermissionTo(Permission::all());
    Role::create(['name' => 'client']);
});

test('admin lands on admin dashboard after login', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->post(route('login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));
});

test('agent lands on admin dashboard after login', function () {
    $agent = User::factory()->create();
    $agent->assignRole('agent');

    $this->post(route('login.store'), [
        'email' => $agent->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));
});

test('client lands on personal dashboard after login', function () {
    $client = User::factory()->create();
    $client->assignRole('client');

    $this->post(route('login.store'), [
        'email' => $client->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));
});

test('admin visiting client dashboard is redirected to admin dashboard', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertRedirect(route('admin.dashboard'));
});

test('client visiting client dashboard sees their dashboard', function () {
    $client = User::factory()->create();
    $client->assignRole('client');

    $this->actingAs($client)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Dashboard'));
});
