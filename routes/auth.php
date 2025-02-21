<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\UnverifiedController;
use App\Http\Controllers\Auth\VerifyEmailController;

Route::middleware('guest')->group(function () {
    Volt::route('register', 'pages.auth.register')
        ->name('register');

    Volt::route('login', 'pages.auth.login')
        ->name('login');

    Route::controller(GoogleAuthController::class)->group(function () {
        Route::get('auth/google', [GoogleAuthController::class, 'googleLogin'])->name('auth.google.login');

        Route::get('auth/google/signup', [GoogleAuthController::class, 'googleSignup'])->name('auth.google.signup');

        Route::get('auth/google/callback', [GoogleAuthController::class, 'googleAuthentication'])->name('auth.google.callback');
    });

    Volt::route('email/verification', 'pages.auth.email-verification')->middleware('guest')->name(name: 'email.verification');

    Volt::route('complete/registration', 'pages.auth.complete-registration')->middleware('guest')->name(name: 'complete.registration');


    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');
});