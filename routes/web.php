<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertySearchController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeController::class)->name('home')->middleware('throttle:public');
Route::get('properties', [PropertyController::class, 'index'])->name('properties.index')->middleware('throttle:public');
Route::get('properties/search', PropertySearchController::class)->name('properties.search')->middleware('throttle:search');
Route::get('properties/{property}', [PropertyController::class, 'show'])->name('properties.show')->middleware('throttle:public');
Route::get('agents/{agentProfile}', [AgentController::class, 'show'])->name('agents.show')->middleware('throttle:public');
Route::post('inquiries', [InquiryController::class, 'store'])->name('inquiries.store')->middleware('throttle:inquiry');

// Newsletter — public signup with double opt-in confirmation.
Route::post('newsletter/subscribe', [NewsletterController::class, 'store'])->name('newsletter.subscribe')->middleware('throttle:inquiry');
Route::get('newsletter/confirm/{token}', [NewsletterController::class, 'confirm'])->name('newsletter.confirm')->middleware('throttle:public');
Route::get('newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe')->middleware('throttle:public');

Route::middleware('guest')->group(function () {
    Route::get('auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
        ->where('provider', 'google|github|facebook|apple')
        ->name('social.redirect');

    Route::get('auth/{provider}/callback', [SocialAuthController::class, 'callback'])
        ->where('provider', 'google|github|facebook|apple')
        ->name('social.callback');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});

Route::middleware(['auth', 'verified', 'role:admin|agent', 'throttle:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('properties', Admin\PropertyController::class);
    Route::post('geocode', Admin\GeocodingController::class)->name('geocode')->middleware('throttle:10,1');
    Route::post('properties/{property}/media', [Admin\MediaController::class, 'store'])->name('properties.media.store');
    Route::post('properties/{property}/media/reorder', [Admin\MediaController::class, 'reorder'])->name('properties.media.reorder');
    Route::post('media/{media}/set-primary', [Admin\MediaController::class, 'setPrimary'])->name('media.setPrimary');
    Route::delete('media/{media}', [Admin\MediaController::class, 'destroy'])->name('media.destroy');
    Route::resource('inquiries', Admin\InquiryController::class)->only(['index', 'show', 'update']);
    Route::post('inquiries/{inquiry}/reply', [Admin\InquiryController::class, 'reply'])->name('inquiries.reply');
});

require __DIR__.'/settings.php';
