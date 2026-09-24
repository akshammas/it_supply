<?php

use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'role' => EnsureRole::class,
        ]);

        // This project only has one login screen (the admin panel) — no
        // separate customer login — so any unauthenticated visitor
        // hitting a protected route should land on /admin/login. Without
        // this, Laravel's default guest-redirect looks for a route
        // literally named "login", which this app doesn't have, and
        // throws a RouteNotFoundException instead of redirecting.
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // An expired session mid-form-submit throws a CSRF token
        // mismatch, which by default shows a raw "419 Page Expired"
        // error page. Redirect to login with a friendly message instead.
        $exceptions->render(function (TokenMismatchException $e, $request) {
            return redirect()->route('admin.login')
                ->with('status', 'Your session expired. Please log in again.');
        });
    })->create();