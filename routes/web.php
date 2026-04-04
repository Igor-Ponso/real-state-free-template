<?php

use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
        ->where('provider', 'google|github|facebook|apple')
        ->name('social.redirect');

    Route::get('auth/{provider}/callback', [SocialAuthController::class, 'callback'])
        ->where('provider', 'google|github|facebook|apple')
        ->name('social.callback');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
