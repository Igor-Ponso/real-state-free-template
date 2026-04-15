<?php

use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use App\Notifications\Security\PasswordChanged;
use App\Notifications\Security\TwoFactorEnabled;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Events\TwoFactorAuthenticationConfirmed;

test('password reset triggers confirmation notification', function () {
    Notification::fake();

    $user = User::factory()->create();

    app(ResetUserPassword::class)->reset($user, [
        'password' => 'NewStr0ng!Password2026',
        'password_confirmation' => 'NewStr0ng!Password2026',
    ]);

    Notification::assertSentTo($user, PasswordChanged::class);
});

test('in-app password update triggers confirmation notification', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->actingAs($user)->put(route('user-password.update'), [
        'current_password' => 'password',
        'password' => 'NewStr0ng!Password2026',
        'password_confirmation' => 'NewStr0ng!Password2026',
    ]);

    $response->assertSessionHasNoErrors();

    Notification::assertSentTo($user, PasswordChanged::class);
});

test('confirming 2FA dispatches security notification', function () {
    Notification::fake();

    $user = User::factory()->create();

    event(new TwoFactorAuthenticationConfirmed($user));

    Notification::assertSentTo($user, TwoFactorEnabled::class);
});
