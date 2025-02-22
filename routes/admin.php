<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;

Route::middleware(['isAdmin'])->group(function () {
    Volt::route('admin/email/verification', 'pages.admin.auth.email-verification')->middleware('guest')->name(name: 'admin.email.verification');

    Route::view('admin/dashboard', 'livewire.pages.admin.dashboard')
        ->middleware(['auth', 'verified'])
        ->name('admin.dashboard');
});
