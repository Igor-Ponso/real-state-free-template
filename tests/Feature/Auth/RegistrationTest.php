<?php

use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'T3st@Secure!99',
        'password_confirmation' => 'T3st@Secure!99',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('registration fails with weak password', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'weak@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('password');
});

test('registration fails with password missing symbols', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'nosymbol@example.com',
        'password' => 'NoSymbolPass123',
        'password_confirmation' => 'NoSymbolPass123',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('password');
});
