<?php

use App\Http\Controllers\Settings\AgentProfileController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/security', [SecurityController::class, 'edit'])->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('settings/appearance', 'settings/Appearance')->name('appearance.edit');

    // Agent profile — admins and agents manage their public /agents/{slug} page.
    Route::get('settings/agent-profile', [AgentProfileController::class, 'edit'])->name('agent-profile.edit');
    Route::patch('settings/agent-profile', [AgentProfileController::class, 'update'])->name('agent-profile.update');
    Route::post('settings/agent-profile/photo', [AgentProfileController::class, 'uploadPhoto'])->name('agent-profile.photo.upload');
    Route::delete('settings/agent-profile/photo', [AgentProfileController::class, 'destroyPhoto'])->name('agent-profile.photo.destroy');
});
