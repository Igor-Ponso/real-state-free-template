<?php

use App\Models\AgentProfile;
use App\Models\City;
use App\Models\ListingType;
use App\Models\Property;
use App\Models\PropertyStatus;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Storage::fake('public');

    Permission::create(['name' => 'manage-properties']);
    Permission::create(['name' => 'view-dashboard']);

    Role::create(['name' => 'admin'])->givePermissionTo(Permission::all());
    Role::create(['name' => 'agent'])->givePermissionTo(Permission::all());
    Role::create(['name' => 'client']);
});

// ─── Settings page ─────────────────────────────────────────────

test('agent can view their profile settings page', function () {
    $agent = User::factory()->create();
    $agent->assignRole('agent');

    $this->actingAs($agent)
        ->get(route('agent-profile.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('settings/AgentProfile'));
});

test('client cannot access agent profile settings', function () {
    $client = User::factory()->create();
    $client->assignRole('client');

    $this->actingAs($client)
        ->get(route('agent-profile.edit'))
        ->assertForbidden();
});

test('agent can update their bio, phone, and specializations', function () {
    $agent = User::factory()->create();
    $agent->assignRole('agent');

    $this->actingAs($agent)
        ->patch(route('agent-profile.update'), [
            'bio' => 'Specializing in waterfront luxury homes.',
            'phone' => '+1 (604) 555-0123',
            'license_number' => 'RE-9876-ZZ',
            'specializations' => ['Luxury waterfront', 'Investment properties'],
            'social_links' => [
                'linkedin' => 'https://linkedin.com/in/test-agent',
                'instagram' => 'https://instagram.com/testagent',
            ],
        ])
        ->assertRedirect();

    $profile = AgentProfile::where('user_id', $agent->id)->firstOrFail();
    expect($profile->bio)->toBe('Specializing in waterfront luxury homes.');
    expect($profile->phone)->toBe('+1 (604) 555-0123');
    expect($profile->specializations)->toEqualCanonicalizing(['Luxury waterfront', 'Investment properties']);
    expect($profile->social_links['linkedin'])->toBe('https://linkedin.com/in/test-agent');
});

test('invalid social link urls are rejected', function () {
    $agent = User::factory()->create();
    $agent->assignRole('agent');

    $this->actingAs($agent)
        ->patch(route('agent-profile.update'), [
            'social_links' => ['linkedin' => 'not-a-url'],
        ])
        ->assertSessionHasErrors('social_links.linkedin');
});

test('agent can upload a profile photo', function () {
    $agent = User::factory()->create();
    $agent->assignRole('agent');

    $this->actingAs($agent)
        ->post(route('agent-profile.photo.upload'), [
            'photo' => UploadedFile::fake()->image('photo.jpg', 800, 800),
        ])
        ->assertRedirect();

    $profile = AgentProfile::where('user_id', $agent->id)->firstOrFail();
    expect($profile->getFirstMedia('profile_photo'))->not->toBeNull();
});

test('oversized photo upload is rejected', function () {
    $agent = User::factory()->create();
    $agent->assignRole('agent');

    // 5MB — above the 4MB cap
    $big = UploadedFile::fake()->image('huge.jpg', 800, 800)->size(5 * 1024);

    $this->actingAs($agent)
        ->post(route('agent-profile.photo.upload'), ['photo' => $big])
        ->assertSessionHasErrors('photo');
});

test('agent can delete their profile photo', function () {
    $agent = User::factory()->create();
    $agent->assignRole('agent');
    $profile = AgentProfile::create(['user_id' => $agent->id]);
    $profile->addMedia(UploadedFile::fake()->image('old.jpg', 400, 400))
        ->toMediaCollection('profile_photo');

    expect($profile->fresh()->getFirstMedia('profile_photo'))->not->toBeNull();

    $this->actingAs($agent)
        ->delete(route('agent-profile.photo.destroy'))
        ->assertRedirect();

    expect($profile->fresh()->getFirstMedia('profile_photo'))->toBeNull();
});

// ─── Public agent page ─────────────────────────────────────────

test('valid agent slug renders the public profile page', function () {
    $agent = User::factory()->create();
    $agent->assignRole('agent');
    $profile = AgentProfile::create(['user_id' => $agent->id, 'bio' => 'Trusted advisor.']);

    $this->get(route('agents.show', ['agentProfile' => $profile->slug]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Agents/Show')
            ->where('agent.slug', $profile->slug)
            ->has('agent.bio')
            ->has('listings')
        );
});

test('unknown slug 404s', function () {
    $this->get('/agents/not-a-real-agent')->assertNotFound();
});

test('public page only surfaces published listings', function () {
    PropertyStatus::create(['name' => 'Active', 'slug' => 'active', 'sort_order' => 1, 'is_active' => true]);
    PropertyStatus::create(['name' => 'Draft', 'slug' => 'draft', 'sort_order' => 2, 'is_active' => true]);
    ListingType::create(['name' => 'For Sale', 'slug' => 'sale', 'sort_order' => 1, 'is_active' => true]);

    $agent = User::factory()->create();
    $agent->assignRole('agent');
    $profile = AgentProfile::create(['user_id' => $agent->id]);
    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    Property::factory()->count(2)->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
    ]); // published via factory default
    Property::factory()->draft()->count(3)->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
    ]);

    $this->get(route('agents.show', ['agentProfile' => $profile->slug]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('listings', 2));
});
