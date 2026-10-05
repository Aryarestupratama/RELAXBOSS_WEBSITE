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
        // Global (bukan grup web) agar halaman 404 ikut mendapat header keamanan.
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        $middleware->alias([
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        // Path tertulis (bukan route()) karena /masuk baru dibuat di TASK-005.
        $middleware->redirectGuestsTo('/masuk');
        $middleware->redirectUsersTo('/dashboard');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
