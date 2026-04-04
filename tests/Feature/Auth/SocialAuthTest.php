<?php

use App\Models\SocialAccount;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

test('social redirect routes to provider', function () {
    Socialite::fake('github');

    $response = $this->get(route('social.redirect', ['provider' => 'github']));

    $response->assertRedirect();
});

test('invalid provider returns 404', function () {
    $response = $this->get('/auth/twitter/redirect');

    $response->assertNotFound();
});

test('callback creates new user from social login', function () {
    Socialite::fake('google', (new SocialiteUser)->map([
        'id' => '99999',
        'name' => 'New User',
        'email' => 'new@example.com',
    ]));

    $response = $this->get(route('social.callback', ['provider' => 'google']));

    $this->assertAuthenticated();

    $user = User::whereBlind('email', 'email_index', 'new@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('New User');
    expect($user->email)->toBe('new@example.com');

    $this->assertDatabaseHas('social_accounts', [
        'provider' => 'google',
        'provider_id' => '99999',
    ]);
    $response->assertRedirect(config('fortify.home'));
});

test('callback creates user without password', function () {
    Socialite::fake('google', (new SocialiteUser)->map([
        'id' => '88888',
        'name' => 'No Pass User',
        'email' => 'nopass@example.com',
    ]));

    $this->get(route('social.callback', ['provider' => 'google']));

    $user = User::whereBlind('email', 'email_index', 'nopass@example.com')->first();
    expect($user->password)->toBeNull();
    expect($user->email_verified_at)->not->toBeNull();
});

test('callback links social account to existing user with matching email', function () {
    $user = User::factory()->create(['email' => 'existing@example.com']);

    Socialite::fake('github', (new SocialiteUser)->map([
        'id' => '77777',
        'name' => 'Existing User',
        'email' => 'existing@example.com',
    ]));

    $this->get(route('social.callback', ['provider' => 'github']));

    $this->assertAuthenticated();
    expect($user->socialAccounts()->count())->toBe(1);
    expect($user->socialAccounts->first()->provider)->toBe('github');
});

test('callback logs in existing social user', function () {
    $user = User::factory()->create();
    SocialAccount::factory()->create([
        'user_id' => $user->id,
        'provider' => 'google',
        'provider_id' => '12345',
    ]);

    Socialite::fake('google', (new SocialiteUser)->map([
        'id' => '12345',
        'name' => $user->name,
        'email' => $user->email,
    ]));

    $response = $this->get(route('social.callback', ['provider' => 'google']));

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(config('fortify.home'));
});

test('callback handles provider error gracefully', function () {
    Socialite::fake('google');

    // Override the fake to throw on user()
    Socialite::shouldReceive('driver->user')->andThrow(new Exception('Token invalid'));

    $response = $this->get(route('social.callback', ['provider' => 'google']));

    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHas('status');
});

test('user can link multiple social providers', function () {
    $user = User::factory()->create(['email' => 'multi@example.com']);
    SocialAccount::factory()->create([
        'user_id' => $user->id,
        'provider' => 'google',
        'provider_id' => '111',
    ]);

    Socialite::fake('github', (new SocialiteUser)->map([
        'id' => '222',
        'name' => $user->name,
        'email' => 'multi@example.com',
    ]));

    $this->get(route('social.callback', ['provider' => 'github']));

    expect($user->socialAccounts()->count())->toBe(2);
});

test('callback verifies unverified existing user', function () {
    $user = User::factory()->unverified()->create(['email' => 'unverified@example.com']);
    expect($user->email_verified_at)->toBeNull();

    Socialite::fake('google', (new SocialiteUser)->map([
        'id' => '333',
        'name' => 'Verified Now',
        'email' => 'unverified@example.com',
    ]));

    $this->get(route('social.callback', ['provider' => 'google']));

    $user->refresh();
    expect($user->email_verified_at)->not->toBeNull();
});

test('social-only user can set password without current password', function () {
    $user = User::factory()->socialOnly()->create();

    $this->actingAs($user)
        ->from(route('security.edit'))
        ->put(route('user-password.update'), [
            'password' => 'T3st@Secure!99',
            'password_confirmation' => 'T3st@Secure!99',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('security.edit'));

    $user->refresh();
    expect($user->password)->not->toBeNull();
});

test('authenticated users cannot access social redirect', function () {
    $user = User::factory()->create();

    Socialite::fake('google');

    $response = $this->actingAs($user)->get(route('social.redirect', ['provider' => 'google']));

    $response->assertRedirect(config('fortify.home'));
});
