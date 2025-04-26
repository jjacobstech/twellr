<?php

use Livewire\Volt\Volt;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', fn() => view('welcome'))->name('home');

Route::middleware('guest')->get('/r/{slug?}', [UserController::class, "referral"]);


Route::fallback(function () {
    abort(404);
});

Route::middleware(['IsCreative', 'IsUser'])->group(function () {
    Route::view('dashboard', 'dashboard')
        ->middleware(['auth', 'verified', 'referred'])
        ->name('dashboard');

    Route::view('profile', 'profile')
        ->middleware(['auth', 'verified'])
        ->name('profile');
});

require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/user.php';
