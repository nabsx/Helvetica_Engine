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

        // Laravel's default auth middleware redirects guests to route('login'),
        // but this app's login route is named 'pos.login' — without this,
        // any expired-session or logged-out request to a protected route
        // crashes with RouteNotFoundException instead of redirecting nicely.
        $middleware->redirectGuestsTo(fn () => route('pos.login'));
    })
    ->withExceptions(function ($exceptions) {
        //
    })->create();
    