<?php

// bootstrap/app.php
// Add the highlighted alias registration inside the existing
// ->withMiddleware() callback. Don't replace the whole file —
// just merge this alias() call into what's already there.

use App\Http\Middleware\EnsureShiftIsOpen;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'shift.active' => EnsureShiftIsOpen::class,
            'admin' => EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function ($exceptions) {
        //
    })->create();
