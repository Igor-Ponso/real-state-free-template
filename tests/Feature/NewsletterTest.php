<?php

use App\Models\City;
use App\Models\ListingType;
use App\Models\NewsletterSubscriber;
use App\Models\Property;
use App\Models\PropertyStatus;
use App\Models\PropertyType;
use App\Models\User;
use App\Notifications\Newsletter\NewsletterConfirm;
use App\Notifications\Newsletter\NewsletterWeeklyDigest;
use App\Notifications\Newsletter\NewsletterWelcome;
use Illuminate\Support\Facades\Notification;

test('visitor can subscribe with a valid email', function () {
    Notification::fake();

    $response = $this->post(route('newsletter.subscribe'), [
        'email' => 'buyer@example.com',
    ]);

    $response->assertRedirect();

    $subscriber = NewsletterSubscriber::whereBlind('email', 'newsletter_email_index', 'buyer@example.com')->first();

    expect($subscriber)->not->toBeNull();
    expect($subscriber->confirmed_at)->toBeNull();
    expect($subscriber->confirmation_token)->not->toBeEmpty();

    Notification::assertSentTo($subscriber, NewsletterConfirm::class);
});

test('honeypot rejects bot submissions', function () {
    $this->post(route('newsletter.subscribe'), [
        'email' => 'bot@example.com',
        'website' => 'http://spam.example',
    ])->assertSessionHasErrors('website');

    expect(NewsletterSubscriber::count())->toBe(0);
});

test('subscribing twice does not duplicate', function () {
    Notification::fake();

    $this->post(route('newsletter.subscribe'), ['email' => 'buyer@example.com']);
    $this->post(route('newsletter.subscribe'), ['email' => 'buyer@example.com']);

    expect(NewsletterSubscriber::count())->toBe(1);
});

test('confirm link activates subscriber and sends welcome', function () {
    Notification::fake();

    $this->post(route('newsletter.subscribe'), ['email' => 'buyer@example.com']);
    $subscriber = NewsletterSubscriber::first();

    $this->get(route('newsletter.confirm', ['token' => $subscriber->confirmation_token]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Newsletter/Status')
            ->where('status', 'confirmed')
        );

    expect($subscriber->fresh()->confirmed_at)->not->toBeNull();
    Notification::assertSentTo($subscriber, NewsletterWelcome::class);
});

test('invalid confirm token renders invalid status', function () {
    $this->get(route('newsletter.confirm', ['token' => 'bogus-token']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('status', 'invalid')
        );
});

test('unsubscribe link removes from active list', function () {
    Notification::fake();

    $this->post(route('newsletter.subscribe'), ['email' => 'buyer@example.com']);
    $subscriber = NewsletterSubscriber::first();
    $this->get(route('newsletter.confirm', ['token' => $subscriber->confirmation_token]));

    $this->get(route('newsletter.unsubscribe', ['token' => $subscriber->confirmation_token]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('status', 'unsubscribed'));

    expect($subscriber->fresh()->unsubscribed_at)->not->toBeNull();
    expect(NewsletterSubscriber::active()->count())->toBe(0);
});

test('prune command deletes unconfirmed subscribers older than 7 days', function () {
    $fresh = NewsletterSubscriber::create([
        'email' => 'fresh@example.com',
        'confirmation_token' => str_repeat('a', 64),
    ]);
    $fresh->forceFill(['created_at' => now()->subDays(3)])->save();

    $stale = NewsletterSubscriber::create([
        'email' => 'stale@example.com',
        'confirmation_token' => str_repeat('b', 64),
    ]);
    $stale->forceFill(['created_at' => now()->subDays(10)])->save();

    $confirmed = NewsletterSubscriber::create([
        'email' => 'confirmed@example.com',
        'confirmation_token' => str_repeat('c', 64),
        'confirmed_at' => now()->subDays(20),
    ]);
    $confirmed->forceFill(['created_at' => now()->subDays(20)])->save();

    $this->artisan('newsletter:prune')->assertSuccessful();

    expect(NewsletterSubscriber::count())->toBe(2);
    expect(NewsletterSubscriber::whereBlind('email', 'newsletter_email_index', 'stale@example.com')->count())->toBe(0);
});

test('weekly digest command dispatches to active subscribers only', function () {
    Notification::fake();

    PropertyStatus::create(['name' => 'Active', 'slug' => 'active', 'sort_order' => 1, 'is_active' => true]);
    ListingType::create(['name' => 'For Sale', 'slug' => 'sale', 'sort_order' => 1, 'is_active' => true]);

    $city = City::factory()->create();
    $type = PropertyType::factory()->create();
    $agent = User::factory()->create();

    Property::factory()->featured()->count(2)->create([
        'user_id' => $agent->id,
        'city_id' => $city->id,
        'property_type_id' => $type->id,
    ]);

    $confirmed = NewsletterSubscriber::create([
        'email' => 'active@example.com',
        'confirmation_token' => str_repeat('a', 64),
        'confirmed_at' => now(),
    ]);
    $unconfirmed = NewsletterSubscriber::create([
        'email' => 'pending@example.com',
        'confirmation_token' => str_repeat('b', 64),
    ]);

    $this->artisan('newsletter:weekly')->assertSuccessful();

    Notification::assertSentTo($confirmed, NewsletterWeeklyDigest::class);
    Notification::assertNotSentTo($unconfirmed, NewsletterWeeklyDigest::class);
});

test('weekly digest dry run sends nothing', function () {
    Notification::fake();

    NewsletterSubscriber::create([
        'email' => 'active@example.com',
        'confirmation_token' => str_repeat('a', 64),
        'confirmed_at' => now(),
    ]);

    $this->artisan('newsletter:weekly --dry')->assertSuccessful();

    Notification::assertNothingSent();
});
