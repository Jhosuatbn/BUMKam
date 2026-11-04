<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([__DIR__.'/../app/Console/Commands'])
    ->withMiddleware(function (Middleware $middleware): void {
    $middleware->validateCsrfTokens(except: [
        'pembayaran/midtrans/notification',
    ]);
    $middleware->alias([
        'pimpinan.access' => \App\Http\Middleware\PimpinanAccess::class,
        'audit.trail' => \App\Http\Middleware\RecordAuditTrail::class,
    ]);
})
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
