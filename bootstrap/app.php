<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
       $exceptions->render(function (AuthenticationException $e, $request) {
            \Log::info('Custom AuthenticationException handler triggered');
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'Authentication required.',
            ], 401);
        });
    })->create();
