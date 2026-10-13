<?php

use App\Http\Middleware\DeteksiPeran;
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
    // ==========================================
    // PEMBAHASAN / BAGIAN: Pendaftaran Alias Middleware Sistem
    // ==========================================
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'peran' => DeteksiPeran::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
