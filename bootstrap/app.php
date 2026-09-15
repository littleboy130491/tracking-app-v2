<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The customer portal owns the public login route; there is no "login"
        // route name, so guests must be sent to the portal sign-in screen.
        $middleware->redirectGuestsTo(fn (Request $request) => route('customer.login'));

        // Signed-in users who open the login screen go back to the landing
        // route, which sends staff to the admin panel and customers to the
        // portal. Pointing this at the login page itself would loop.
        $middleware->redirectUsersTo(fn (Request $request) => route('home'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
