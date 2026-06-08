<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        using: function (): void {
            require __DIR__.'/../routes/install.php';

            Route::middleware('web')->group(__DIR__.'/../routes/web.php');

            Route::get('/up', function (Request $request) {
                return $request->expectsJson()
                    ? response()->json(['status' => 'up'])
                    : response('OK');
            });
        },
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prependToGroup('web', \App\Http\Middleware\EnsureApplicationInstalled::class);

        $middleware->alias([
            'check.maintenance' => \App\Http\Middleware\CheckMaintenanceMode::class,
            'installed' => \App\Http\Middleware\EnsureApplicationInstalled::class,
            'guest.installed' => \App\Http\Middleware\RedirectIfInstalled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
