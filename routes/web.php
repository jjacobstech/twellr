<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::fallback(function () {
    abort(404);
});

Route::middleware(['IsCreative', 'IsUser'])->group(function () {
    Route::view('dashboard', 'dashboard')
        ->middleware(['auth', 'verified',])
        ->name('dashboard');

    Route::view('profile', 'profile')
        ->middleware(['auth', 'verified'])
        ->name('profile');
});

require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/user.php';
