<?php

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

    PropertyStatus::create(['name' => 'Active', 'slug' => 'active', 'sort_order' => 1, 'is_active' => true]);
    ListingType::create(['name' => 'For Sale', 'slug' => 'sale', 'sort_order' => 1, 'is_active' => true]);

    $manage = Permission::create(['name' => 'manage-properties']);
    Permission::create(['name' => 'view-dashboard']);

    Role::create(['name' => 'admin'])->givePermissionTo(Permission::all());
    Role::create(['name' => 'agent'])->givePermissionTo($manage);
});

function setupProperty(User $owner): Property
{
    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    return Property::factory()->create([
        'user_id' => $owner->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
    ]);
}

test('admin can upload a valid image to own or any property', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $agent = User::factory()->create();
    $agent->assignRole('agent');

    $property = setupProperty($agent);

    $this->actingAs($admin)
        ->post("/admin/properties/{$property->slug}/media", [
            'files' => [UploadedFile::fake()->image('home.jpg', 1600, 1200)],
            'collection' => 'images',
        ])
        ->assertRedirect();

    expect($property->fresh()->getMedia('images'))->toHaveCount(1);
});

test('agent cannot upload to another agents property', function () {
    $owner = User::factory()->create();
    $owner->assignRole('agent');
    $intruder = User::factory()->create();
    $intruder->assignRole('agent');

    $property = setupProperty($owner);

    $this->actingAs($intruder)
        ->post("/admin/properties/{$property->slug}/media", [
            'files' => [UploadedFile::fake()->image('home.jpg', 1600, 1200)],
            'collection' => 'images',
        ])
        ->assertForbidden();
});

test('rejects image smaller than minimum dimensions', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $property = setupProperty($admin);

    $this->actingAs($admin)
        ->post("/admin/properties/{$property->slug}/media", [
            'files' => [UploadedFile::fake()->image('tiny.jpg', 400, 300)],
            'collection' => 'images',
        ])
        ->assertSessionHasErrors('files.0');

    expect($property->fresh()->getMedia('images'))->toBeEmpty();
});

test('rejects oversized image', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $property = setupProperty($admin);

    // 9 MB fake file — above the 8 MB limit
    $file = UploadedFile::fake()->create('big.jpg', 9 * 1024, 'image/jpeg');

    $this->actingAs($admin)
        ->post("/admin/properties/{$property->slug}/media", [
            'files' => [$file],
            'collection' => 'images',
        ])
        ->assertSessionHasErrors('files.0');
});

test('rejects svg for images collection', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $property = setupProperty($admin);

    $svg = UploadedFile::fake()->createWithContent('icon.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>');

    $this->actingAs($admin)
        ->post("/admin/properties/{$property->slug}/media", [
            'files' => [$svg],
            'collection' => 'images',
        ])
        ->assertSessionHasErrors('files.0');
});

test('rejects pdf for images collection', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $property = setupProperty($admin);

    $pdf = UploadedFile::fake()->create('doc.pdf', 200, 'application/pdf');

    $this->actingAs($admin)
        ->post("/admin/properties/{$property->slug}/media", [
            'files' => [$pdf],
            'collection' => 'images',
        ])
        ->assertSessionHasErrors('files.0');
});

test('accepts pdf for floor_plans collection', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $property = setupProperty($admin);

    // Real PDF header so mime sniffing resolves as application/pdf.
    $pdf = UploadedFile::fake()->createWithContent(
        'plan.pdf',
        "%PDF-1.4\n%\xe2\xe3\xcf\xd3\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF",
    );

    $this->actingAs($admin)
        ->post("/admin/properties/{$property->slug}/media", [
            'files' => [$pdf],
            'collection' => 'floor_plans',
        ])
        ->assertRedirect();

    expect($property->fresh()->getMedia('floor_plans'))->toHaveCount(1);
});

test('enforces max images per property', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $property = setupProperty($admin);

    // Seed 30 images at the limit (bypass controller — direct model call to skip validation)
    for ($i = 0; $i < 30; $i++) {
        $property->addMedia(UploadedFile::fake()->image("seed-{$i}.jpg", 1600, 1200))
            ->toMediaCollection('images');
    }

    // 31st upload should fail the 'files' max rule
    $this->actingAs($admin)
        ->post("/admin/properties/{$property->slug}/media", [
            'files' => [UploadedFile::fake()->image('one-too-many.jpg', 1600, 1200)],
            'collection' => 'images',
        ])
        ->assertSessionHasErrors('files');
});

test('can reorder media', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $property = setupProperty($admin);

    $media1 = $property->addMedia(UploadedFile::fake()->image('a.jpg', 1200, 900))->toMediaCollection('images');
    $media2 = $property->addMedia(UploadedFile::fake()->image('b.jpg', 1200, 900))->toMediaCollection('images');
    $media3 = $property->addMedia(UploadedFile::fake()->image('c.jpg', 1200, 900))->toMediaCollection('images');

    $this->actingAs($admin)
        ->post("/admin/properties/{$property->slug}/media/reorder", [
            'ids' => [$media3->id, $media1->id, $media2->id],
        ])
        ->assertOk();

    expect($media3->fresh()->order_column)->toBe(1);
    expect($media1->fresh()->order_column)->toBe(2);
    expect($media2->fresh()->order_column)->toBe(3);
});

test('can set primary image', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $property = setupProperty($admin);

    $media1 = $property->addMedia(UploadedFile::fake()->image('a.jpg', 1200, 900))->toMediaCollection('images');
    $media2 = $property->addMedia(UploadedFile::fake()->image('b.jpg', 1200, 900))->toMediaCollection('images');

    $this->actingAs($admin)
        ->post("/admin/media/{$media2->id}/set-primary")
        ->assertOk();

    expect($media2->fresh()->getCustomProperty('is_primary'))->toBeTrue();
    expect($media1->fresh()->getCustomProperty('is_primary', false))->toBeFalse();
});

test('can delete media', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $property = setupProperty($admin);

    $media = $property->addMedia(UploadedFile::fake()->image('x.jpg', 1200, 900))->toMediaCollection('images');

    $this->actingAs($admin)
        ->delete("/admin/media/{$media->id}")
        ->assertRedirect();

    expect($property->fresh()->getMedia('images'))->toBeEmpty();
});
