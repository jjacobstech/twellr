<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::middleware(['auth', 'verified'])->group(
    function () {
        Volt::route('/payment/preference', 'pages.creative.payment-preference')->name('creative.payment.preference')->middleware('PaymentPreference');
        Volt::route('/upload', 'pages.creative.upload')->name(name: 'creative.upload');
        Route::post('/upload/print/stack', [ProductController::class, 'uploadPrintStack'])->name('upload.print.stack');
        Volt::route('/marketplace', 'pages.market-place')->name(name: 'market.place');
        Volt::route('/explore', 'pages.explore')->name(name: 'explore');
    }

);