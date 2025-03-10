<?php

use App\Http\Middleware\User;
use App\Http\Middleware\Admin;
use App\Http\Middleware\Creative;
use App\Http\Middleware\AdminExists;
use App\Http\Middleware\PaymentPreference;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'IsAdmin' => Admin::class,
            'IsUser' => User::class,
            'AdminExists' => AdminExists::class,
            'IsCreative' => Creative::class,
            'PaymentPreference' => PaymentPreference::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();