<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertySearchController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeController::class)->name('home')->middleware('throttle:120,1');
Route::get('properties', [PropertyController::class, 'index'])->name('properties.index')->middleware('throttle:120,1');
Route::get('properties/search', PropertySearchController::class)->name('properties.search')->middleware('throttle:60,1');
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

Route::middleware(['auth', 'verified', 'role:admin|agent'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('properties', Admin\PropertyController::class);
    Route::post('properties/{property}/media', [Admin\MediaController::class, 'store'])->name('properties.media.store');
    Route::delete('media/{media}', [Admin\MediaController::class, 'destroy'])->name('media.destroy');
    Route::resource('inquiries', Admin\InquiryController::class)->only(['index', 'show', 'update']);
});

require __DIR__.'/settings.php';
