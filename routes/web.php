<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::fallback(function () {
    abort(404);
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', 'isUser'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('feed', 'feed')
    ->middleware(['auth', 'verified'])
    ->name('feed');

require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/user.php';