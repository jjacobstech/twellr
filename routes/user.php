<?php

use Livewire\Volt\Volt;
use App\Models\Transaction;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::middleware(['auth', 'verified'])->group(
    function () {
        Volt::route('/payment/preference', 'pages.creative.payment-preference')->name('creative.payment.preference')->middleware('PaymentPreference');
        Volt::route('/upload', 'pages.creative.upload')->name(name: 'creative.upload');
        Volt::route('/marketplace', 'pages.market-place')->name(name: 'market.place');
        Volt::route('/explore', 'pages.explore')->name(name: 'explore');
        Volt::route('/support', 'pages.support')->name('support');
        Volt::route('/wallet', 'pages.wallet')->name('wallet');
        Volt::route('/blog', 'pages.blog')->name('blog');
        Volt::route('settings', 'pages.settings')->name('settings');
    }



);
