<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;

Volt::route('admin/email/verification', 'pages.admin.auth.email-verification')->middleware('guest')->name(name: 'admin.email.verification');

Route::middleware(['auth', 'verified','IsAdmin'])->group(function () {


    Volt::route('admin/dashboard', 'pages.admin.dashboard')->name('admin.dashboard');

    Volt::route('admin/profile', 'pages.admin.profile')->name('admin.profile');

    Volt::route('admin/settings', 'pages.admin.settings')->name(name: 'admin.settings');

    Volt::route('admin/system/preferences', 'pages.admin.preferences')->name('admin.preferences');

    Volt::route('admin/uploaded/designs', 'pages.admin.designs')->name(name: 'admin.designs');

    Volt::route('admin/orders', 'pages.admin.orders')->name(name: 'admin.orders');




});
