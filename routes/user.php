<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::middleware(['auth', 'verified'])->group(
    function () {
        Volt::route('/upload', 'pages.creative.upload')->name('creative.upload');
        Route::post('/upload/print/stack', [ProductController::class, 'uploadPrintStack'])->name('upload.print.stack');
    }

);