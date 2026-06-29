<?php

use App\Exceptions\GlobalExceptionHandler;
use App\Http\Middleware\AuthenticateScraper;
use App\Http\Middleware\AuthenticateToken;
use App\Http\Middleware\CheckCreatorActive;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsCreator;
use App\Http\Middleware\SetLocaleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(SetLocaleMiddleware::class);
        $middleware->alias([
            'auth' => AuthenticateToken::class,
            'creator.active' => CheckCreatorActive::class,
            'admin' => EnsureUserIsAdmin::class,
            'creator' => EnsureUserIsCreator::class,
            'auth.scraper' => AuthenticateScraper::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        GlobalExceptionHandler::handle($exceptions);
    })->create();
