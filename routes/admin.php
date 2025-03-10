<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;

Volt::route('admin/email/verification', 'pages.admin.auth.email-verification')->middleware('guest')->name(name: 'admin.email.verification');

Route::middleware(['IsAdmin'])->group(function () {

    Route::view('admin/dashboard', 'livewire.pages.admin.dashboard')
        ->middleware(['auth', 'verified'])
        ->name('admin.dashboard');
});