<?php

use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeController::class)->name('home')->middleware('throttle:120,1');
Route::get('properties', [PropertyController::class, 'index'])->name('properties.index')->middleware('throttle:120,1');
Route::get('properties/{property}', [PropertyController::class, 'show'])->name('properties.show')->middleware('throttle:120,1');
Route::post('inquiries', [InquiryController::class, 'store'])->name('inquiries.store')->middleware('throttle:5,1');

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
