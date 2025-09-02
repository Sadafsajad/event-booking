<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Contracts\Debug\ExceptionHandler;
use App\Exceptions\Handler as AppExceptionHandler;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Route middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminOnly::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();

/* Important: ensure ExceptionHandler is bound */
$app->singleton(ExceptionHandler::class, AppExceptionHandler::class);

return $app;
