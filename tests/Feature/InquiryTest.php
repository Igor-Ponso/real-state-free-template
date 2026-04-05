<?php

use App\Models\City;
use App\Models\Inquiry;
use App\Models\InquiryStatus;
use App\Models\ListingType;
use App\Models\Property;
use App\Models\PropertyStatus;
use App\Models\PropertyType;
use App\Models\User;

beforeEach(function () {
    PropertyStatus::create(['name' => 'Active', 'slug' => 'active', 'sort_order' => 1, 'is_active' => true]);
    ListingType::create(['name' => 'For Sale', 'slug' => 'sale', 'sort_order' => 1, 'is_active' => true]);
    InquiryStatus::create(['name' => 'New', 'slug' => 'new', 'sort_order' => 0, 'is_active' => true]);
});

test('guest can submit inquiry for a property', function () {
    $property = createProperty();

    $this->postJson(route('inquiries.store'), [
        'property_id' => $property->id,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '+1 (555) 123-4567',
        'message' => 'I am interested in this property. Please contact me.',
    ])->assertCreated()
        ->assertJson(['message' => 'Thank you for your inquiry. We will get back to you shortly.']);

    $inquiry = Inquiry::latest()->first();
    expect($inquiry)->not->toBeNull();
    expect($inquiry->name)->toBe('John Doe');
    expect($inquiry->email)->toBe('john@example.com');
    expect($inquiry->message)->toBe('I am interested in this property. Please contact me.');
    expect($inquiry->property_id)->toBe($property->id);
    expect($inquiry->user_id)->toBeNull();
});

test('authenticated user inquiry is linked to their account', function () {
    $user = User::factory()->create();
    $property = createProperty();

    $this->actingAs($user)
        ->postJson(route('inquiries.store'), [
            'property_id' => $property->id,
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'message' => 'Interested in scheduling a viewing of this property.',
        ])->assertCreated();

    $inquiry = Inquiry::latest()->first();
    expect($inquiry->user_id)->toBe($user->id);
});

test('inquiry validation rejects missing required fields', function () {
    $this->postJson(route('inquiries.store'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['property_id', 'name', 'email', 'message']);
});

test('inquiry validation rejects invalid email', function () {
    $property = createProperty();

    $this->postJson(route('inquiries.store'), [
        'property_id' => $property->id,
        'name' => 'Test User',
        'email' => 'not-an-email',
        'message' => 'I would like more information about this property.',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('inquiry validation rejects message shorter than 10 characters', function () {
    $property = createProperty();

    $this->postJson(route('inquiries.store'), [
        'property_id' => $property->id,
        'name' => 'Test User',
        'email' => 'test@example.com',
        'message' => 'Short',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['message']);
});

test('inquiry validation rejects non-existent property_id', function () {
    $this->postJson(route('inquiries.store'), [
        'property_id' => 99999,
        'name' => 'Test User',
        'email' => 'test@example.com',
        'message' => 'I would like more information about this property.',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['property_id']);
});

test('honeypot field silently discards spam submissions', function () {
    $property = createProperty();

    $this->postJson(route('inquiries.store'), [
        'property_id' => $property->id,
        'name' => 'Spam Bot',
        'email' => 'bot@spam.com',
        'message' => 'Buy our amazing products now with a discount!',
        'honeypot' => 'I am a bot',
    ])->assertCreated();

    expect(Inquiry::count())->toBe(0);
});

test('inquiry is created with new status', function () {
    $property = createProperty();

    $this->postJson(route('inquiries.store'), [
        'property_id' => $property->id,
        'name' => 'Status Check',
        'email' => 'status@example.com',
        'message' => 'Testing that the inquiry gets the new status assigned.',
    ])->assertCreated();

    $inquiry = Inquiry::latest()->first();
    expect($inquiry->inquiryStatus->slug)->toBe('new');
});

/**
 * Create a published property with all required relationships.
 */
function createProperty(): Property
{
    $agent = User::factory()->create();
    $city = City::factory()->create();
    $type = PropertyType::factory()->create();

    return Property::factory()->featured()->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
    ]);
}
